# Ticket Request

Scope: Public request form (no login) that creates a Ticket in Pending status. Admin reviews and either Approves (triggers a payment-link email) or Rejects (terminal).

Status: 🔴 Barely started — form UI only, no submission or workflow.

- [x] Public request form UI (`GET /events/{event}/request`), pre-selects a ticket type
- [ ] `Ticket` model + migration
- [ ] Form submission endpoint (`POST`) that creates a Ticket in Pending status
- [ ] Admin review screen with Approve / Reject actions
- [ ] Approval email with payment link; rejection is terminal
