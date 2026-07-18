# Landing Page & Public Pages — Design Spec

Date: 2026-07-18
Status: Approved by user, pending file review

## Context

This is the first implementation sub-project of the Event & Ticketing Platform (see `.claude/CLAUDE.md`, `.claude/skills/event-domain.md`, `.claude/skills/workshop-module.md`, `.claude/skills/ui-system.md`). It covers the public-facing Landing Page and its supporting public pages, the database schema for the content behind them, and basic admin CRUD forms to enter that content — for a single reference event, CCS (Content Creators Summit).

The rest of the platform (ticket request/approval/payment, workshop booking, awards voting, QR check-in, reports, coupons, the full admin panel) is deliberately out of scope and remains to be spec'd separately — see `.claude/plans/`.

## Scope

**In scope:**
- Public Landing Page (one per Event, fixed section order)
- Public Workshops index + per-workshop detail pages
- Public Agenda page
- Public Ticket Request page (form only — submission handling/approval workflow is a separate future spec)
- Public Awards placeholder page (teaser target only — no voting logic)
- Database schema for all entities the above pages render
- Basic admin CRUD forms to enter/edit that content

**Explicitly out of scope** (each is its own future spec per `.claude/plans/`):
- Ticket request processing: approval/rejection, payment link, ticket/QR issuance (05-ticket-request, 06-payment)
- Workshop *booking* — attendee picking workshops via Ticket ID + Workshop Booking Key (07-workshops)
- Awards voting mechanics (08-awards — still needs its own brainstorming pass)
- QR registration team portal / check-in (09-qr-system)
- Per-event reports (10-reports)
- Discount coupons
- The rest of the admin panel: approvals queue, reports, coupon management, QR portal, and any multi-language *management* UI beyond the paired-column fields described below

## Multi-tenancy & routes

One deployment serves multiple Events; public routes are scoped by event slug.

- `GET /events/{event}` — Landing Page
- `GET /events/{event}/workshops` — Workshops index
- `GET /events/{event}/workshops/{workshop}` — Workshop detail
- `GET /events/{event}/agenda` — Agenda page
- `GET /events/{event}/request?type={ticketType}` — Ticket Request form
- `GET /events/{event}/awards` — Awards placeholder page ("coming soon" / informational only)

## Data model

Approach: structured tables per entity (not a generic JSON content store), so entities other subsystems need to reference by ID (Workshops, Speakers, Ticket Types) are real relations from day one. Bilingual fields use paired columns on the same row (`name_ar` / `name_en`), not a separate translations table or locale-per-row pattern.

- **events** — `slug`, `name_ar/en`, `tagline_ar/en`, `start_date`, `end_date`, `venue_name_ar/en`, `venue_address_ar/en`, `map_embed_url`, `status`
- **speakers** — `event_id`, `name_ar/en`, `title_ar/en`, `bio_ar/en`, `photo_path`, `sort_order`
- **sponsors** — `event_id`, `name_ar/en`, `logo_path`, `tier`, `website_url`, `sort_order`
- **ticket_types** — `event_id`, `name_ar/en`, `description_ar/en`, `price`, `currency`, `workshop_slot_count` (0 = none, null = unlimited), `sort_order`, `is_active`
- **workshops** — `event_id`, `slug`, `name_ar/en`, `description_ar/en`, `speaker_id` (nullable), `capacity`, `sort_order`
- **agenda_items** — `event_id`, `day_date`, `start_time`, `end_time`, `title_ar/en`, `type` (keynote/session/workshop/break/panel), `speaker_id` (nullable), `workshop_id` (nullable), `sort_order`
- **faqs** — `event_id`, `question_ar/en`, `answer_ar/en`, `sort_order`
- **landing_page_content** — `event_id`, `section` (hero/about/location/awards_teaser), `field_key`, `value_ar`, `value_en`

`landing_page_content` is the one deliberate exception to "structured table per entity": these are a handful of fixed, relationship-free text fields (Hero headline, About body, Location intro, Awards teaser blurb), so one small keyed table beats five near-empty single-purpose tables.

**Rendering rule:** a section with no underlying rows (e.g. an event with zero Sponsors) simply does not render. No admin toggle is needed for this — it falls out of the data naturally.

## Landing Page sections (fixed order, same for every event)

1. **Hero** — logo/tagline, title, date + live countdown, location badge, CTA button → Tickets section. Background: diagonal gradient, coral → maroon → near-black (`#ff7e71` → `#430d14` → `#171f22`), echoing the CCS logo's flag shape. Content and background chosen and confirmed via visual mockup review.
2. **About Event** — from `landing_page_content` (section = `about`)
3. **Speakers** — cards (photo, name, title); short bio shown on click/hover; no dedicated speaker page
4. **Workshops** — teaser cards linking to the Workshops index → individual workshop detail pages
5. **Agenda** — teaser linking to the full Agenda page
6. **Tickets** — one card per Ticket Type (name, price, description, workshop slot count if any); CTA → dedicated Ticket Request page
7. **Awards** — teaser linking to the Awards placeholder page
8. **Partners** — sponsor logos grouped by tier; no dedicated partners page
9. **FAQ** — accordion sourced from `faqs`
10. **Location** — venue text + map embed, from `landing_page_content` (section = `location`)

## Admin scope (this spec only)

Basic CRUD screens, each with paired Arabic/English inputs where the entity has bilingual fields:

- Events
- Speakers
- Workshops
- Ticket Types
- Sponsors
- FAQs
- Agenda Items
- Landing Page free-text content (Hero / About / Location / Awards-teaser fields)

No approval workflow, no coupon management, no reports — those stay with their own future specs (see `.claude/plans/03-admin-panel.md`).

## Visual / brand direction

CCS palette: coral `#ff7e71`, red `#d33333`, dark maroon `#430d14`, near-black `#171f22`, teal `#2a7675`, light teal `#7ccbcf`, gold `#fad48b` / `#a48755`. Dark theme, premium/minimal/corporate style, large typography, generous whitespace, subtle animations only (see `.claude/skills/ui-system.md`). Bootstrap 5 + SCSS, no Tailwind. Bilingual Arabic (RTL) / English (LTR) with a language switcher; layouts must not hardcode LTR-only markup.

## Testing strategy

- Feature tests per public route: renders successfully, shows only the requesting event's data, and gracefully omits sections with no underlying rows.
- Form validation/persistence tests for each admin CRUD screen: required fields enforced, bilingual pairs handled consistently (both required or both optional, not mixed).

## Open questions carried forward (not part of this spec)

From `.claude/skills/event-domain.md`:
- Awards & voting mechanics
- Discount coupon rules
- Multi-language: locales beyond Arabic/English, if any
- QR team portal access model
