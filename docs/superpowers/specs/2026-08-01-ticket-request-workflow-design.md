# Ticket Request Workflow — Design

## Context

Phase 03 (Admin Panel) status doc flags "Ticket Request approvals/rejections" and "Reports" as
remaining work, but both actually depend on Phase 05 (Ticket Request) having a real `Ticket`
model and submission flow — which doesn't exist yet (the public request page today is a static
form with no `action`). This spec covers building that core: the `Ticket` model, a
**dynamic, admin-configurable request form**, submission + validation, and the admin
review/approve/reject queue. Discount Coupons and Reports remain separate follow-up work; see
`.claude/plans/00-status.md`.

Domain rules this design must satisfy (from `.claude/skills/event-domain.md` and
`.claude/CLAUDE.md`):

- No user authentication. The Ticket is the attendee's identity.
- Flow: Request (public, no login) → Admin Review → Approve / Reject (terminal) → \[Payment →
  Ticket Issued → Check-In, out of scope here\].
- Never hardcode event-specific values; every feature must be reusable across events.

## Goals

- Replace the static request form with a real submission flow that creates a `Ticket` in
  `Pending` status.
- Collect Name, Email, Phone on every request (fixed, always required), plus whichever extra
  fields (Instagram, Portfolio, CV) the admin has configured for that event.
- Give the admin a queue to review pending requests and Approve or Reject them.
- Validate every field server-side and client-side, with input whitelisted by type to guard
  against XSS/SQL injection.
- Establish the full `tickets` table shape now (including Payment/QR/Workshop-booking columns),
  so later phases add logic, not migrations — see Data model.

## Out of scope

- Payment (Phase 06) — Approve stops at `Payment Pending`. The `is_paid` / `payment_method`
  columns exist (see Data model) but nothing in this feature sets them; no payment link is
  generated or emailed yet.
- QR generation / check-in (Phase 09) — `ticket_id`, `workshop_booking_key`, `checked_in_at`
  columns exist but stay null; nothing in this feature generates or sets them.
- Discount Coupons, Reports (remaining Phase 03 items, independent of this work).
- Rejection *reason* capture (admin just clicks Reject; the email is a generic notice).

## Data model

### `tickets` (new table, `Ticket` model)

| Column | Type | Notes | Populated by |
|---|---|---|---|
| `id` | bigint | | — |
| `event_id` | FK → events, cascade delete | | this phase |
| `ticket_type_id` | FK → ticket_types, cascade delete | | this phase |
| `name` | string(255) | attendee name, required | this phase |
| `email` | string(255) | attendee email, required | this phase |
| `phone` | string(32) | attendee phone (E.164), required | this phase |
| `ticket_number` | string(32), unique | human-readable reference, e.g. `CCS2026-000042` — event slug (uppercased, no dashes) + zero-padded `id` | this phase, at submission |
| `status` | string, backed by `TicketStatus` enum | default `Pending` | this phase drives Pending/Approved/Rejected/PaymentPending; later phases drive Paid/TicketIssued/CheckedIn/Cancelled |
| `ticket_id` | string(40), unique, nullable | hard-to-guess identifier (random token), used with `workshop_booking_key` for no-login workshop booking | **later** — Payment/QR phase, at issuance |
| `workshop_booking_key` | string(40), nullable | random token; only generated if the ticket type includes workshop slots (`workshop_slot_count` is null or > 0) | **later** — Payment/QR phase, at issuance |
| `is_paid` | boolean | default `false` | **later** — Payment phase |
| `payment_method` | string(50), nullable | | **later** — Payment phase |
| `checked_in_at` | timestamp, nullable | null = not checked in | **later** — QR/Check-in phase |
| `timestamps` | | | — |

No separate `event_name` / `event_date` columns — accessible via the `event_id` relation
(`$ticket->event->name_en`, `->start_date`, `->end_date`), consistent with how the rest of the
app avoids denormalized copies.

`TicketStatus` enum (`App\Enums\TicketStatus`): `Pending`, `Approved`, `Rejected`,
`PaymentPending`, plus `Paid`, `TicketIssued`, `CheckedIn`, `Cancelled` declared for
forward-compatibility — this feature only ever sets the first four.

### `ticket_request_fields` (new table, `TicketRequestField` model)

Admin-configured extra fields, per event. Phone is NOT here — it's a fixed core `tickets`
column, always required (see above), not admin-configurable.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | |
| `event_id` | FK → events, cascade delete | |
| `type` | string, backed by `TicketRequestFieldType` enum | `Instagram`, `Portfolio`, `Cv` |
| `label_ar` | string(255) | |
| `label_en` | string(255) | |
| `is_required` | boolean | default `false` |
| `sort_order` | unsigned int | default `0` |
| `timestamps` | | |

### `ticket_request_answers` (new table, `TicketRequestAnswer` model)

The attendee's submitted value per configured field, per ticket.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | |
| `ticket_id` | FK → tickets, cascade delete | |
| `ticket_request_field_id` | FK → ticket_request_fields, cascade delete | |
| `value` | string(2048), nullable | instagram handle or portfolio URL |
| `file_path` | string(255), nullable | CV upload, or portfolio PDF upload |
| `timestamps` | | |

Exactly one of `value` / `file_path` is set per answer, depending on field type and — for
Portfolio — which mode the attendee picked at submission time.

## Public request flow

`GET /events/{event}/request` (existing route, `TicketRequestController@create`) renders:

1. Ticket Type select (existing, pre-selected via `?type=` query param).
2. Name, Email, Phone — always present, always required, fixed core fields.
3. The event's `TicketRequestField` rows, in `sort_order`, each rendered per its type:

| Type | Widget | Client-side check | Server-side rule |
|---|---|---|---|
| Instagram | text | `pattern="^@?[\w.]{1,30}$"` | `regex:/^@?[A-Za-z0-9_.]{1,30}$/` |
| Portfolio | radio (URL / PDF) + matching input | URL: `type=url`; PDF: drop-zone | URL mode: `url\|max:2048`. PDF mode: `file\|mimes:pdf\|max:5120` |
| CV | drop-zone (file) | `accept=".pdf,.doc,.docx"` | `file\|mimes:pdf,doc,docx\|max:5120` |

Fixed-field rules:
- `name` → `required|string|max:255|regex:/^[\pL\pM\s\'\-\.]+$/u` (unicode letters/marks/space/'-.
  only — blocks tag/script content by construction).
- `email` → `required|email:rfc,dns|max:255`.
- `phone` → `required`, `intl-tel-input` client-side (live validation + E.164 formatting), and
  `propaganistas/laravel-phone`'s `phone()` rule server-side.

Each dynamic field's rule gets `required` or `nullable` prepended based on its `is_required`
flag. Rules are built at request time in `TicketRequestStoreRequest::rules()` by loading the
event's `TicketRequestField`s — this has to be dynamic since the field set varies per event.

`POST /events/{event}/request` (new route) validates, then in a DB transaction creates the
`Ticket` (status `Pending`, `ticket_number` generated from the event slug + the new row's `id`)
and one `TicketRequestAnswer` per submitted dynamic field (storing uploaded files to the private
disk first, saving the returned path). Redirects back with a "request received" flash message
that includes the `ticket_number`, matching the existing Contact/Newsletter pattern.

**XSS/SQLi posture:** all persistence goes through Eloquent (no raw SQL), every input is
whitelisted by type-specific regex/rule before reaching the DB, and every place an answer is
later displayed (the admin queue) uses Blade's `{{ }}` escaping — never `{!! !!}`.

## File uploads

A reusable Blade+Alpine drop-zone component, `<x-file-dropzone>`, used for CV (always) and
Portfolio (when the attendee picks the PDF radio option): drag-and-drop or click-to-browse,
shows the selected filename, checks extension/size client-side before submit.

**Storage:** the `private` local disk (new disk config, not the default `public` one) — no
`storage:link`, no guessable public URL. Rationale: CVs and portfolios can contain personal
information and shouldn't be reachable by anyone who has the URL. Admins retrieve files via an
authenticated streaming route, `admin.events.ticket-requests.download-file`, which checks the
requested file belongs to a ticket under that event before streaming it.

## Admin: request form builder

`admin/events/{event}/request-form-fields` — new CRUD screen, following the existing
Faqs/Speakers pattern (index + create/edit forms, no drag-reorder — a numeric Sort Order field
like the rest of the admin panel). Admin picks a `type` from the three supported types
(Instagram, Portfolio, CV), sets bilingual label, required toggle, sort order. Name/Email/Phone
are not listed here — they're fixed into the public form and not admin-editable.

## Admin: ticket request queue

`admin/events/{event}/ticket-requests` — lists `Ticket`s for the event, default-filtered to
`Pending`, with a status filter dropdown. Each row: Name, Email, Ticket Type, submitted date,
expandable dynamic answers (CV/Portfolio render as download links to the streaming route).
Two actions on pending rows:

- **Approve** — `PATCH admin/events/{event}/ticket-requests/{ticket}/approve`. Sets status to
  `PaymentPending`. No email (Payment phase isn't built yet — nothing real to link to).
- **Reject** — `PATCH admin/events/{event}/ticket-requests/{ticket}/reject`. Sets status to
  `Rejected` (terminal) and sends a rejection notice via a new `Mailable`
  (`App\Mail\TicketRequestRejected`). First real transactional email in this app —
  `MAIL_MAILER=log` in `.env`, so it writes to the log in dev, same mechanism that would apply
  in production once a real mailer is configured.

## New dependencies

Both explicitly requested:

- Composer: `propaganistas/laravel-phone` — server-side phone validation.
- npm: `intl-tel-input` — client-side phone input widget with country codes.

## Testing

Feature tests (PHPUnit), following existing repo conventions:

**Public**
- Valid submission creates a `Ticket` (status `Pending`, unique `ticket_number` assigned) + one
  `TicketRequestAnswer` per submitted dynamic field.
- `ticket_id`, `workshop_booking_key`, `is_paid`, `payment_method`, `checked_in_at` are all still
  null/false immediately after submission — this feature never sets them.
- Missing required dynamic fields fail validation.
- Invalid name / email / phone / instagram-handle / portfolio-URL formats are rejected.
- CV upload persists to the private disk and is not reachable via a public URL.
- Draft events still 404 on both `GET` and `POST` (matches `DraftEventVisibilityTest`).

**Admin**
- Request-form-field CRUD (create/edit/delete/reorder).
- Approve transitions status to `PaymentPending` and sends no mail.
- Reject transitions status to `Rejected` and sends the rejection mail (`Mail::fake()` + assert).
- The file-download route 404s/redirects for guests (not admin-authenticated) and for a file
  belonging to a different event's ticket.
- Download route streams the correct file for the correct ticket.
