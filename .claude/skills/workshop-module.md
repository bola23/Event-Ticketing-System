# Workshop Rules

A Workshop belongs to one Event.

A Ticket belongs to one Event.

A Ticket never belongs directly to Workshops.

Instead:

Ticket Type

↓

Workshop Slots

Example

General
0

VIP
1

Premium
2

Platinum
Unlimited

The attendee chooses any available workshops.

Selections create WorkshopBooking records.

Do not store workshop ids on Tickets.

Capacity must always be respected.

## Booking key

A random Workshop Booking Key is generated when the ticket is issued, alongside the Ticket ID and QR code (see event-domain skill).

The attendee uses Ticket ID + Workshop Booking Key (no login) to open their workshop picker.

The picker offers only that Event's available Workshops. Selections are never forced — the attendee freely picks up to their Ticket Type's slot count, and different tickets on the same Ticket Type may end up with different Workshop combinations.

A Ticket Type with 0 slots does not get a workshop picker at all.