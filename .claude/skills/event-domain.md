# Event Domain Knowledge

This project is not a marketplace.

It is a professional conference and summit management platform.

Every Event owns:

- Ticket Types
- Workshops
- Awards
- Sponsors
- Speakers
- Landing Pages
- Discount Coupons
- Reports

Tickets are approval-based.

Users cannot purchase directly.

Flow:

Request (public form, no login)
↓

Admin Review

↓

Approve — or — Reject (terminal)

↓ (approved only)

Payment link emailed to the user

↓

User pays online

↓

Generate Ticket (Paid → Ticket Issued)

↓

Generate QR + Workshop Booking Key, emailed to the user

↓

Workshop Booking (optional, if the Ticket Type includes slots)

↓

Check-In (registration team scans the QR at the event)

There is no user login.

The Ticket is the attendee identity.

Each ticket contains:

- Ticket ID
- QR Code
- Workshop Booking Key

Workshop booking uses:

Ticket ID
+
Workshop Booking Key

instead of authentication.

## Admin & Operations

The Admin Dashboard is where staff manage all of the above: create/edit Events, Ticket Types, Workshops, Discount Coupons, Landing Page content (CMS), multi-language content, review Reports, and approve/reject Ticket Requests.

A separate QR registration team portal is used on event day for check-in staff to scan tickets. It is distinct from the admin dashboard.

## Open questions (not yet designed)

- Awards & voting mechanics: who can vote, one vote per category/person, voting window, how nominees are entered, results visibility.
- Discount coupon rules: percentage vs fixed amount, scope (event-wide vs per Ticket Type), usage limits.
- Multi-language: which locales, translation storage model, fallback behavior.
- QR team portal access model: staff login vs shared access key.