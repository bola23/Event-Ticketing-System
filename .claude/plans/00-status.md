# Project Status

Consolidated checklist across all 10 build phases (see the individual phase files in this
directory for scope). Reflects actual code in the repo, not intent — re-verify against routes/
models before trusting this after major work lands, since it goes stale the moment new code
ships without an update here.

Last verified: 2026-07-25 (routes, models, controllers, migrations inspected directly).

## At a glance

| # | Phase | Status |
|---|-------|--------|
| 01 | Project Setup | ✅ Done |
| 02 | Database Design | 🟡 Partial — core content schema done, ticket workflow schema missing |
| 03 | Admin Panel | 🟡 Partial — CMS/content CRUD done, approvals/coupons/reports missing |
| 04 | Landing Page | ✅ Done |
| 05 | Ticket Request | 🔴 Barely started — form UI only, no submission or workflow |
| 06 | Payment | ⬜ Not started |
| 07 | Workshops | 🟡 Partial — browsing built, booking flow missing |
| 08 | Awards | 🟡 Partial — teaser/page shell built, voting missing |
| 09 | QR System | ⬜ Not started |
| 10 | Reports | ⬜ Not started |

## 01 — Project Setup

- [x] Laravel 12 + PHP 8.3
- [x] MySQL connection
- [x] Laravel Boost
- [x] Base Blade layout with RTL/LTR (Arabic + English) support
- [x] Bilingual UI string catalog (`lang/ar.json`, `lang/en.json`) covering the whole app

Note: the phase doc originally scoped "Bootstrap 5 + SCSS" — the project actually uses
Tailwind CSS + AlpineJS instead (see `.claude/CLAUDE.md`). Doc updated to match reality.

## 02 — Database Design

- [x] Events, Speakers, Sponsors, TicketTypes (+ TicketTypeFeatures), Workshops, AgendaItems,
      Faqs, LandingPageContent, GalleryPhotos, Testimonials, ContactMessages,
      NewsletterSubscribers
- [ ] Ticket (attendee ticket + workflow state: Pending → Approved/Rejected → Payment Pending
      → Paid → Ticket Issued → Checked In → Cancelled)
- [ ] WorkshopBooking (slot-based booking keyed by Ticket ID + Workshop Booking Key)
- [ ] DiscountCoupon
- [ ] Award / AwardVote

## 03 — Admin Panel

- [x] Admin auth (login/logout)
- [x] Events CRUD
- [x] Ticket Types CRUD (with bilingual feature bullets)
- [x] Workshops CRUD
- [x] Speakers CRUD
- [x] Sponsors CRUD
- [x] Agenda Items CRUD
- [x] FAQs CRUD
- [x] Gallery Photos CRUD
- [x] Testimonials CRUD
- [x] Landing Page Content CMS, including per-section show/hide toggles
- [x] Contact Messages (read-only index)
- [x] Newsletter Subscribers (read-only index)
- [ ] Discount Coupons admin
- [ ] Ticket Request review/approve/reject queue
- [ ] Per-event Reports screen

## 04 — Landing Page

- [x] Full CCS-branded redesign (hero, about, speakers, workshops teaser, tickets, awards
      teaser, gallery, testimonials, partners, FAQ, location, contact, newsletter/footer)
- [x] Bilingual (Arabic RTL / English LTR), all copy CMS-editable or catalog-translated
- [x] Admin-controlled section visibility (show/hide any of the 13 toggleable sections)
- [x] Standalone Agenda page (moved off the landing page into its own route/page)
- [x] Scroll-reveal animations, smooth-scroll nav, language switcher

Functionally complete for the current scope. Future landing-page work is additive (new
sections/content), not foundational.

## 05 — Ticket Request

- [x] Public request form UI (`GET /events/{event}/request`), pre-selects a ticket type
- [ ] `Ticket` model + migration
- [ ] Form submission endpoint (`POST`) that creates a Ticket in Pending status
- [ ] Admin review screen with Approve / Reject actions
- [ ] Approval email with payment link; rejection is terminal

## 06 — Payment

- [ ] Payment gateway selection
- [ ] Payment link flow (Payment Pending → Paid)
- [ ] QR code + Workshop Booking Key generation on payment success
- [ ] Issued-ticket email

Not started.

## 07 — Workshops

- [x] Admin CRUD for workshops
- [x] Public workshop browsing (`workshops.index`, `workshops.show`)
- [x] Landing page workshops teaser (capacity shown, no fabricated fill %)
- [ ] `WorkshopBooking` model
- [ ] Ticket ID + Workshop Booking Key redemption flow (no login)
- [ ] Enforce each ticket type's `workshop_slot_count` against booked slots

## 08 — Awards

- [x] Admin-editable Awards teaser blurb (Landing Page Content CMS)
- [x] Public awards teaser (landing page) + `/events/{event}/awards` page shell
- [ ] Voting mechanics decided (who can vote, one vote per category/person, voting window)
- [ ] Nominee entry (admin)
- [ ] `Award` / `AwardVote` models
- [ ] Vote submission flow + results display

## 09 — QR System

- [ ] QR code generation on ticket issuance
- [ ] Registration/check-in team portal
- [ ] Portal access model (staff login vs. shared key) decided
- [ ] Check-in marks Ticket as Checked In

Not started.

## 10 — Reports

- [ ] Metrics defined (ticket counts by status, revenue, workshop attendance, check-in rates)
- [ ] Per-event admin report screens

Not started.
