# Workshops

Scope: Workshop booking flow. Attendee uses Ticket ID + Workshop Booking Key (no login) to pick up to their Ticket Type's workshop-slot count from that Event's available Workshops. See workshop-module skill for slot/capacity rules.

Status: 🟡 Partial — browsing built, booking flow missing.

- [x] Admin CRUD for workshops
- [x] Public workshop browsing (`workshops.index`, `workshops.show`)
- [x] Landing page workshops teaser (real capacity shown, no fabricated fill %)
- [ ] `WorkshopBooking` model
- [ ] Ticket ID + Workshop Booking Key redemption flow (no login)
- [ ] Enforce each ticket type's `workshop_slot_count` against booked slots
