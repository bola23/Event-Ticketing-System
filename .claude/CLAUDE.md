# Event & Ticketing Platform

This project is a reusable event management platform. First reference deployment: CCS (Content Creators Summit — قمة صناع المحتوى).

Technology

- Laravel 12
- PHP 8.3
- MySQL
- Laravel Boost
- Tailwind CSS
- AlpineJS where needed

Architecture

MVC

Service Layer

Repository Pattern

Domain Rules

No user authentication.

Tickets represent attendees.

Ticket workflow

Request

Pending

Approved

Rejected

Payment Pending

Paid

Ticket Issued

Checked In

Cancelled

Approved and Rejected are the two outcomes of admin review; Rejected is terminal. Cancelled is a separate terminal state reachable later in the flow (e.g. after payment or issuance).

System flow (see event-domain skill for full detail):

1. User requests a ticket (no login).
2. Admin reviews the request.
3. Admin approves or rejects it.
4. On approval, the user is emailed a payment link; on payment, the user is emailed the issued ticket (QR code + Workshop Booking Key).
5. At the event, the registration team scans the QR code to check the attendee in.

Modules

- Ticketing & Approval workflow
- Online Payment
- Workshops (slot-based, keyed booking — see workshop-module skill)
- Awards with attendee/public voting
- Admin Dashboard: events, ticket types, discount coupons, landing page CMS, multi-language content, per-event reports, ticket approvals/rejections
- QR registration team portal (event-day check-in)

Every feature should be reusable across multiple events.

Never hardcode event-specific values.

All frontend content should be editable through the CMS.

The admin dashboard is part of the product.

The platform is bilingual (Arabic RTL + English LTR) — see ui-system skill.

See `.claude/skills/` for domain-specific rules and `.claude/plans/` for the per-subsystem build roadmap.
