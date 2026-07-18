# ADR-001: Ticket Is the Attendee Identity

Ticket is the attendee identity. There is no user login or registration anywhere in the system.

Consequence: every downstream feature that needs to identify a person (workshop booking, check-in) must authenticate against the Ticket ID (plus a scoped secondary key, e.g. the Workshop Booking Key) instead of a user session.
