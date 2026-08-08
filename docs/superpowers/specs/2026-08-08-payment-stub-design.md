# Payment Stub — Design

## Context

Phase 06 (Payment) is entirely unbuilt. Every ticket that gets Approved today dead-ends at
`Payment Pending` — there is no way to move it forward, no `ticket_id`/`workshop_booking_key`/QR
ever gets generated, and no issued-ticket email ever gets sent. This blocks Workshop Booking
(Phase 07), which needs a real Workshop Booking Key to redeem against, and leaves the ticket
workflow without a working terminal "issued" state.

The organization intends to integrate a real payment gateway — [Kashier](https://kashier.io/) —
but their merchant account is still pending approval. This spec is **not** that integration. It
builds the smallest thing that unblocks Workshop Booking now: a manual admin "mark as paid"
action that issues the ticket. The ticket-issuance logic is written as a reusable unit so that
wiring up Kashier later is additive (a payment link + a webhook that calls the same issuance
logic), not a rewrite.

Domain rules this design must satisfy (from `.claude/skills/event-domain.md` and
`.claude/CLAUDE.md`):

- No user authentication for attendees — the Ticket is still the attendee's identity.
- Workflow: ... → Approved → Payment Pending → Paid → Ticket Issued → Checked In → Cancelled.
- On payment, the user is emailed the issued ticket (QR code + Workshop Booking Key).
- Never hardcode event-specific values; every feature must be reusable across events.

## Goals

- Give admins a way to move a `Payment Pending` ticket forward without a real payment gateway.
- Generate the `ticket_id` and a unique `workshop_booking_key` at issuance time, matching the
  columns already established in the `tickets` table.
- Generate a real QR code image and email it to the attendee, inline, alongside the booking key.
- Structure the issuance logic (generate IDs, flip status, send email) as a single reusable
  method — not duplicated — so a future Kashier webhook can call the same code path instead of
  admins doing it by hand.
- Keep `payment_method` flexible enough to record a future `kashier` value without a schema
  change.

## Out of scope

- Real Kashier integration: no payment link generation, no checkout redirect, no webhook
  handler, no API credentials. This spec only prepares the ground (reusable issuance logic,
  `payment_method` values list) for that to be additive later.
- Workshop Booking itself (Phase 07, separate spec) — this only makes a real
  `workshop_booking_key` exist for it to redeem against.
- Discount Coupons, Reports, Awards voting — separate specs.
- Refunds, partial payments, payment amount/reference tracking — a stub records *that* an admin
  confirmed payment happened offline, not transaction details.
- A public "view my ticket" page — the issued-ticket email is the only place the QR/booking key
  appear, for now.

## Data model

### `tickets` — one migration, additive only

Add a unique index to the existing `workshop_booking_key` column (currently nullable and
unconstrained since the Ticket Request Workflow spec only reserved the column):

```php
Schema::table('tickets', function (Blueprint $table) {
    $table->unique('workshop_booking_key');
});
```

No other schema changes. `ticket_id`, `is_paid`, `payment_method`, `status` already exist from
the `tickets` migration.

`payment_method` stays a free-form `string(50)`, but the admin UI and validation constrain it to
a known list: `cash`, `bank_transfer`, `other` today, with `kashier` reserved as a future value
once the gateway is live — no migration needed to add it later.

## Ticket issuance logic

New method, `Ticket::issue(string $paymentMethod): void` (model-level, since it's a pure state
transition over the ticket's own columns — consistent with how the rest of the app keeps
transitions close to the model rather than in a separate service layer for something this small):

1. Guard: only callable when `status === TicketStatus::PaymentPending` (throws `\RuntimeException`
   if not — callers check first via the controller's 404 and this is the belt-and-suspenders
   backstop, not the primary guard).
2. Sets `ticket_id` = the ticket's own `ticket_number` (already unique from the request stage —
   reused rather than minting a second identifier).
3. Generates `workshop_booking_key`: an 8-character random uppercase alphanumeric code prefixed
   `WBK-` (e.g. `WBK-7F3K9A2Q`). Checked against the database in a retry loop (regenerate and
   recheck on collision, up to a small max attempts) before assigning — this is new logic; unlike
   `ticket_number` (deterministic from the event slug + row ID, never collides), a random code
   needs its own uniqueness check.
4. Sets `is_paid = true`, `payment_method = $paymentMethod`, `status = TicketStatus::TicketIssued`.
5. Saves the ticket, then sends `App\Mail\TicketIssued` to `$ticket->email`.

This is the one place that does all of the above — both today's admin action and a future
Kashier webhook call `$ticket->issue($paymentMethod)`; neither reimplements the ID generation or
email dispatch.

## Admin: mark as paid

`TicketRequestQueueController::markPaid(Event $event, Ticket $ticket, Request $request)`:

- Route: `PATCH admin/events/{event}/ticket-requests/{ticket}/mark-paid`, named
  `admin.events.ticket-requests.mark-paid`.
- `assertBelongsToEvent()` (existing helper, reused).
- 404s (via the existing `NotFoundHttpException` convention in this controller) if
  `$ticket->status !== TicketStatus::PaymentPending`.
- Validates `payment_method` inline: `required|in:cash,bank_transfer,other`.
- Calls `$ticket->issue($request->string('payment_method'))`.
- Redirects to the queue index, same pattern as approve/reject.

**UI** — `admin/ticket-requests/index.blade.php`: tickets with status `Payment Pending` get a new
inline row action, matching the existing Approve/Reject form pattern — a `<select>` for payment
method (Cash / Bank Transfer / Other) plus a "Mark as Paid" submit button in the same form.

## Email

New `App\Mail\TicketIssued` Mailable + `emails.tickets.issued` view, following the
`TicketRequestRejected` pattern already in the codebase:

- Subject: "Your ticket is ready!" (translated).
- Body: event name, ticket number, the QR code image embedded inline via `$message->embedData()`,
  and the Workshop Booking Key as plain text with a one-line explanation that it's needed to book
  workshops.
- QR payload: the ticket's `ticket_id` (= `ticket_number`) — the same value a future check-in
  scanner (Phase 09) would look up.

## New dependency

- Composer: `endroid/qr-code` — its `PngWriter` uses GD (confirmed available in this
  environment; Imagick is not) to produce a real embeddable PNG, unlike `simple-qrcode`'s PNG
  path which requires Imagick.

## Testing

Feature tests (PHPUnit):

- Marking a `Payment Pending` ticket as paid transitions it to `Ticket Issued`, sets `is_paid`,
  `payment_method`, `ticket_id` (equal to `ticket_number`), and a `workshop_booking_key`.
- `workshop_booking_key` is unique across multiple issued tickets (create several, assert no
  collisions / assert the DB unique constraint holds).
- Marking as paid requires a valid `payment_method` (missing or invalid value fails validation,
  ticket stays `Payment Pending`).
- Marking as paid 404s when the ticket is not currently `Payment Pending` (e.g. already
  `Pending`, or already `Ticket Issued`).
- Marking as paid sends `TicketIssued` mail to the ticket's email (`Mail::fake()` + assert), and
  the ticket stays untouched if the ticket doesn't belong to the given event (matches the
  existing `assertBelongsToEvent` 404 convention).
- `Ticket::issue()` throws/refuses when called on a ticket not in `PaymentPending` (unit-level
  guard test, independent of the controller).
