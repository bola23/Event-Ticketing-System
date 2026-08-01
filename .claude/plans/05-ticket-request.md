# Ticket Request

Scope: Public request form (no login) that creates a Ticket in Pending status. Admin reviews and either Approves (triggers a payment-link email) or Rejects (terminal).

Status: 🟡 Partial — request, review, approve/reject all built; approval email with a real payment link waits on Payment (Phase 06).

- [x] Public request form UI (`GET /events/{event}/request`), pre-selects a ticket type
- [x] Dynamic, admin-configurable extra fields (Instagram, Portfolio [URL or PDF], CV upload) alongside fixed Name/Email/Phone
- [x] `Ticket` model + migration (plus `TicketRequestField`, `TicketRequestAnswer`)
- [x] Form submission endpoint (`POST`) that creates a Ticket in Pending status, with server-side validation (regex/phone/file-type) and private file storage
- [x] Admin review screen with Approve / Reject actions
- [x] Reject sends a rejection email; Approve moves the ticket to Payment Pending — no email yet, since there's no real payment link until Payment (Phase 06) is built
