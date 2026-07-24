# Landing Page Visual Redesign & New Sections — Design Spec

Date: 2026-07-23
Status: Approved by user, pending file review

## Context

This spec covers importing a visual design produced in Claude Design (`CCS Landing Page.dc.html`, project `c669adee-a89a-4608-9b7d-8d18093db51b`) and implementing it as the real public landing page. The current landing page (`docs/superpowers/specs/2026-07-18-landing-page-design.md`, all 27 tasks complete) has the correct 10-section structure and real bilingual CMS-backed data, but its visual polish was never finished — sections use bare Tailwind utility classes with no cohesive dark-theme design, despite `.claude/skills/ui-system.md` calling for "premium / minimal / corporate / dark theme / large typography."

The imported design is a fully-realized dark near-black/burgundy/red/teal/gold visual system with motion (fade-ups, floating shapes, live countdown, animated FAQ/nav), plus 5 sections that don't exist in the current page (Stats bar, Gallery, Testimonials, Contact form, Newsletter signup).

This is **not a pure re-skin**: the 10 existing sections get restyled in place using real data as today, but the 5 new sections require new tables, new routes, and new admin CRUD screens. Two of the existing sections (Workshops, Agenda) also gain real structural detail (not just new colors) because the underlying data already supports it.

## Scope

**In scope:**
- Visual redesign of all 10 existing landing-page sections (Hero, About, Speakers, Workshops, Agenda, Tickets, Awards, Partners, FAQ, Location), preserving their existing routes, data sources, and the element-ID hooks `tests/Feature/LandingPageTest.php` asserts on
- 5 new sections: Stats bar, Gallery, Testimonials, Contact form, Newsletter signup
- New Eloquent models/migrations: `Testimonial`, `GalleryPhoto`, `ContactMessage`, `NewsletterSubscriber`
- Two new manual content fields on the existing `landing_page_content` mechanism (`Stats` section: `attendees_count`, `countries_count`)
- Two new public POST routes (contact submit, newsletter submit)
- Admin CRUD for Gallery Photos and Testimonials (full CRUD, matching the existing 8-entity pattern); admin index-only screens for Contact Messages and Newsletter Subscribers

**Explicitly out of scope:**
- Real file upload for images anywhere (Gallery, Speakers, Sponsors) — `image_path`/`logo_path`/`photo_path` stay manually-entered path/URL text fields, matching the already-deferred gap from the prior landing-page review. Not resolved here.
- Workshop seat-fill / capacity-remaining display — no booking table exists yet (07-workshops is still a future spec). Workshop cards show static capacity, not a fill percentage.
- Award categories, nominee counts, or voting — Awards stays a CMS-editable teaser blurb, unchanged in kind from today (08-awards remains its own future spec).
- Email sending for Contact/Newsletter — both are store-only with an admin list view; no Mailable, no notification.
- Unsubscribe flow for Newsletter — out of scope for this pass.
- Any change to the admin panel's own visual system (already redesigned per `docs/superpowers/specs/2026-07-20-admin-panel-ui-redesign.md`) beyond adding nav entries and screens for the 2 new CRUD resources.

## Visual system

**Palette:** no new colors. The mockup's roles map onto the existing `resources/css/app.css` `@theme` tokens:

| Mockup role | Mockup hex | Maps to existing token |
|---|---|---|
| `--black` | `#0b0708` | `--color-ccs-black` (`#171f22`) |
| `--burgundy` / `--burgundy2` | `#241014` / `#341620` | `--color-ccs-maroon` (`#430d14`) |
| `--red` / `--red2` | `#7c1d2b` / `#a32334` | `--color-ccs-red` (`#d33333`) |
| `--coral` | `#e2664a` | `--color-ccs-coral` (`#ff7e71`) |
| `--teal` | `#3ec6b8` | `--color-ccs-teal` / `--color-ccs-teal-light` |
| `--gold` | `#d4a24c` | `--color-ccs-gold` / `--color-ccs-gold-dark` |
| `--white` / `--muted` / `--muted2` | `#f6f2ef` / `#b9a8a7` / `#8d7c7b` | Tailwind neutrals (`white`, `gray-400`, `gray-500`) — no new tokens needed |

**Typography:** unchanged — Manrope (display), Inter (body), Cairo (Arabic both roles), already self-hosted via `@fontsource`. The mockup's Google Fonts `<link>` is dropped entirely.

**Motion:** translate the mockup's CSS `@keyframes` (`fadeUp`, `floatSlow`, `pulseGlow`) into `resources/css/app.css` as reusable utility classes, applied via Alpine `x-intersect`-style reveal-on-scroll where the mockup uses load-time animation, and respecting `prefers-reduced-motion: reduce` (mockup has no such guard — this project adds one, per `frontend-design` skill's quality floor). Interactive behavior (FAQ accordion, agenda day tabs, mobile nav toggle, live countdown) is implemented with Alpine.js `x-data`, following the same pattern already used in `hero.blade.php` and `faq.blade.php` — not the mockup's bespoke `DCLogic` state class, which has no equivalent in this stack.

**Bilingual/RTL:** every string in the mockup (English-only) gets an Arabic counterpart pulled from the same paired-column / `landing_page_content` mechanism already in place. Flex/grid direction, spacing, and icon mirroring follow the existing `dir="rtl"` convention — no hardcoded LTR-only layout.

## Section-by-section plan

1. **Hero** — restyle only. Same data (`event->name_*`, `start_date`, `venue_name_*`), same live countdown (already Alpine-driven), new gradient/typography/floating decorative shapes treatment. Same `#hero` id and CTA behavior.
2. **About** — restyle only. Same `contentFor(About, 'body')` source, same `#about` id (must still be absent when no content row exists, per `test_about_section_omitted_when_no_content`).
3. **Stats** *(new)* — 4-tile bar. Speakers count = `$event->speakers->count()`, Workshops count = `$event->workshops->count()` (live). Attendees/Countries = `contentFor(Stats, 'attendees_count')` / `contentFor(Stats, 'countries_count')` (manual, admin-edited display strings like `"4,200+"`). The section renders whenever at least one of the four stats has data (a positive speaker/workshop count, or a non-empty manual value); it's omitted only when all four are empty — matching the existing "no underlying data, no section" rule.
4. **Speakers** — restyle to card grid (photo box, name, title), keep existing click-to-reveal bio interaction and `#speakers` id.
5. **Workshops** — restyle to real cards (name, description snippet, capacity as static text, e.g. "40 seats"), each linking to `workshops.show`; "See All Workshops" CTA retained. No seat-fill bar (no booking data exists). Same `#workshops` id.
6. **Agenda** — upgrade from bare link to a real day-tabbed preview: group `$event->agendaItems` by `day_date`, Alpine tabs switch the visible day, each row shows `start_time`, `title_*`, `type` (as a track pill), and `speaker->name_*` when present. "View Full Agenda" CTA retained, same `#agenda-teaser` id (test asserts the link to `agenda.show` exists — must still be present).
7. **Tickets** — restyle to pricing cards: name, price+currency, `workshop_slot_count` rendered as a label (0 → omit or "No workshops included", null → "Unlimited workshops", N → "N workshop(s) included"), `description_*` as body text, CTA to `ticket-requests.create?type={id}`. Same `#tickets` id and query-string contract (`test_tickets_section_links_to_request_page`).
8. **Awards** — restyle only (teaser blurb + CTA to `awards.show`), same `#awards` id.
9. **Gallery** *(new)* — uniform grid of `GalleryPhoto` rows (`image_path`, optional `caption_*` as `alt` text), ordered by `sort_order`. Section omitted when the event has no photos.
10. **Testimonials** *(new)* — grid of quote cards from `Testimonial` rows (`quote_*`, `name_*`, `title_*`), ordered by `sort_order`. Omitted when none exist.
11. **Partners** — restyle only, same grouped-by-tier rendering, same `#partners` id (must stay absent when no sponsors, per `test_partners_section_omitted_when_no_sponsors`).
12. **FAQ** — restyle only, same `#faq` id and accordion behavior (Alpine, unchanged mechanism).
13. **Location** — restyle only, same `#location` id and map embed.
14. **Contact** *(new)* — form (name, email, message) posting to `POST /events/{event}/contact`. On success: store a `ContactMessage` row, redirect back with a flash success message (no page reload of form fields' previous values needed beyond standard Laravel old-input-on-error behavior).
15. **Newsletter** *(new)* — single-field email form posting to `POST /events/{event}/newsletter`. Validates unique `(event_id, email)`; duplicate submissions still redirect with a friendly success message rather than an error (idempotent from the visitor's perspective).

Section order on the page: Hero, About, Stats, Speakers, Workshops, Agenda, Tickets, Awards, Gallery, Testimonials, Partners, FAQ, Location, Contact, Newsletter — footer-adjacent sections (Contact, Newsletter) last, matching the mockup's own ordering.

## Data model

New tables (paired bilingual columns where the content is CMS-authored; plain columns where it's visitor-submitted):

- **testimonials** — `event_id`, `quote_ar/en`, `name_ar/en`, `title_ar/en`, `sort_order`
- **gallery_photos** — `event_id`, `image_path`, `caption_ar/en` (nullable), `sort_order`
- **contact_messages** — `event_id`, `name`, `email`, `message`, timestamps (visitor-submitted, single language — no `_ar`/`_en` split)
- **newsletter_subscribers** — `event_id`, `email`, timestamps, unique on `(event_id, email)`

Extended enum: `App\Enums\LandingPageSection` gains a `Stats = 'stats'` case, used with `field_key` values `attendees_count` and `countries_count` via the existing `landing_page_content` table — no new table for this part.

## Routes

```
POST /events/{event}/contact    -> ContactMessageController@store   name: contact.store
POST /events/{event}/newsletter -> NewsletterController@store       name: newsletter.store
```

Both inside the existing `Route::prefix('events/{event}')->middleware(EnsureEventIsPublished::class)` group.

Admin additions inside the existing authenticated admin group:

```
Route::resource('events.gallery-photos', GalleryPhotoController::class)->except('show');
Route::resource('events.testimonials', TestimonialController::class)->except('show');
Route::get('events/{event}/contact-messages', [ContactMessageController::class, 'index'])->name('events.contact-messages.index');
Route::get('events/{event}/newsletter-subscribers', [NewsletterSubscriberController::class, 'index'])->name('events.newsletter-subscribers.index');
```

## Admin scope

- **Gallery Photos**, **Testimonials**: full CRUD (index + form), built with the existing shared component set (`x-admin.field`, `x-admin.bilingual-field`, `x-admin.button`, `x-admin.table`, `x-admin.page-header`, `x-admin.empty-state`) inside `layouts.admin`, added to the sidebar nav alongside the other 8 entities.
- **Contact Messages**, **Newsletter Subscribers**: index-only screens (read the submissions; no create/edit/delete — they're records of visitor activity, not editable content).
- **Landing Page Content editor**: gains two new inputs (Attendees, Countries) under a new "Stats" fieldset, same pattern as the existing Hero/About/Location/Awards-teaser fieldsets.

## Constraints

- Every existing `id="..."` hook and route-linking assertion in `tests/Feature/LandingPageTest.php` must keep passing unchanged — this is a re-skin of those 8 sections, not a rewrite of what they assert.
- No fabricated data: Workshop seat-fill and Award nominee counts are explicitly not built because no real data source exists for them yet.
- No new `@theme` colors — every visual choice maps onto the 8 existing CCS tokens.
- Bilingual parity: every new user-facing string (Stats labels, Gallery/Testimonial content, Contact/Newsletter form labels and flash messages) ships in both Arabic and English from the start.

## Testing

- `LandingPageTest`: existing tests must stay green as-is. New tests: Stats section shows live counts and manual values, omitted when both absent; Gallery/Testimonials sections render and omit-when-empty; Agenda day-tab preview shows real session data; Workshops cards show capacity, not a fabricated fill percentage.
- New feature tests for `POST /events/{event}/contact` (stores a row, validates required fields, redirects with success) and `POST /events/{event}/newsletter` (stores a row, validates email format, handles duplicate submissions gracefully).
- New admin CRUD tests for Gallery Photos and Testimonials, following the existing 7-entity pattern (index empty/populated, create, edit, update, delete, validation).
- New admin tests for the Contact Messages and Newsletter Subscribers index screens (renders submitted rows).
- Landing Page Content editor test extended to cover the 2 new Stats fields.
