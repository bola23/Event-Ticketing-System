# Admin Panel UI/UX Redesign & Root Route Fix — Design Spec

Date: 2026-07-20
Status: Approved by user, pending file review

## Context

The Landing Page & Public Pages implementation (see `docs/superpowers/specs/2026-07-18-landing-page-design.md`, plan `docs/superpowers/plans/2026-07-18-landing-page-implementation.md`, all 27 tasks complete and merged) deliberately kept every admin screen minimal — bare HTML, no CSS framework classes — since visual polish was explicitly out of that spec's scope. It also never wired the site root (`/`) to anything.

This spec covers the follow-up: give the admin panel a real, designed UI/UX using the CCS brand identity (`docs/brand-identity.pdf`, `docs/logo.jpeg`, `.claude/skills/ui-system.md`), and fix `/` so it shows the real landing page instead of Laravel's stock placeholder.

This is a **presentation-layer pass, not a behavior change**: no new routes (beyond the `/` redirect), no controller logic, no validation rules, no migrations. Every existing route name, form field name, and controller method stays exactly as built.

## Scope

**In scope:**
- `/` redirects to the published reference event's landing page
- A designed visual system (palette already exists; typography and a signature motif are new) applied consistently across the admin panel
- A shared admin layout (sidebar + event switcher) replacing the bare `layouts.app` wrapper for admin pages
- A small Blade component library (Approach C — see Rationale) used to restyle all 17 existing admin view files
- Redesigned: login screen, dashboard, and all 8 CRUD screens (Events, Speakers, Sponsors, Ticket Types, Workshops, Agenda Items, FAQs, Landing Page Content)

**Explicitly out of scope:**
- The public landing page's own content/layout (Hero, About, Speakers, etc.) — already brainstormed, built, and reviewed in the prior spec. It inherits the new typography (see below) but its section structure, copy, and layout are untouched.
- Any new admin functionality (roles/permissions, bulk actions, search/filtering, pagination) — this is styling only.
- The previously-deferred findings from the final whole-branch review (hardcoded Hero eyebrow/locale bug, orphaned `hero_headline` field, image upload for Speakers/Sponsors, per-event workshop slug uniqueness, i18n `__()` inconsistency, throttle keyed by IP-only) remain deferred — not addressed here.

## Root route

`GET /` returns a redirect (302) to `route('landing.show', ['event' => 'ccs-2026'])` — the one currently-published event. No new controller; a closure or trivial route-level redirect is sufficient. When a second event needs simultaneous public visibility, an events-index page becomes its own future spec — not built speculatively now.

## Visual system

**Palette:** no new colors. Reuses the existing CCS `@theme` tokens already defined in `resources/css/app.css` (`--color-ccs-coral/red/maroon/black/teal/teal-light/gold/gold-dark`).

**Typography (new — currently the entire app uses browser default fonts):**
- English display/headings: **Manrope** (weights 700–800)
- English body/UI text: **Inter** (weights 400–500)
- Arabic (headings and body both): **Cairo** (weights 400 and 700) — a modern geometric Arabic face chosen to echo Manrope/Inter's character rather than falling back to system Tahoma/Arial
- Font selection switches via `:lang(ar)` / `:lang(en)` CSS rules, keyed off the same `app()->getLocale()` value already driving `dir="rtl"`/`dir="ltr"` on `<html>`
- Fonts self-hosted via `@fontsource` npm packages (bundled through Vite, not a Google Fonts CDN call) to keep the app's asset pipeline self-contained and avoid an external network dependency at render time

**Signature element:** the CCS logo is a flag/parallelogram shape (angled top edge, coral sliver over a red/maroon body). A small CSS `clip-path`-drawn version of this shape becomes the admin panel's one recurring motif, used in exactly two places, deliberately not more:
1. The sidebar's active-nav-item indicator (replacing a plain highlight bar)
2. A `x-admin.page-header` accent beside each screen's `<h1>`

**Login screen** is where the CCS brand gets its one full-strength moment: the same diagonal coral→red→maroon→near-black gradient background used on the public Hero, with the real logo image (`docs/logo.jpeg`, copied into `resources/images/` and referenced via Vite) centered above a card containing the login form. Every other admin screen stays deliberately quiet — near-black background, minimal color, the flag motif as the only recurring accent — per "spend the boldness in one place."

## Component architecture (Approach C: shell + a small set of true atoms)

Rejected alternatives: a full component library built ahead of need (over-engineering for 8 fairly similar CRUD screens), and hand-styling all 17 files independently with no shared components (guarantees drift the first time any shared element needs to change).

**New shared layout:** `resources/views/layouts/admin.blade.php` — sidebar + event-switcher shell, used by every admin page in place of the bare `layouts.app` extend.

**New Blade components** (`resources/views/components/admin/`):
- `field.blade.php` — label + input/select/textarea/checkbox + validation error, as one unit (covers every form field across all 8 entities' forms)
- `button.blade.php` — primary/secondary/danger variants
- `table.blade.php` — table wrapper with consistent header/row styling
- `page-header.blade.php` — `<h1>` + the flag-motif accent
- `empty-state.blade.php` — "no records yet" placeholder for empty index screens

**Rewritten** (styling only, no logic changes): `resources/views/admin/auth/login.blade.php`, `resources/views/admin/dashboard.blade.php`, and both `index.blade.php` + `form.blade.php` for each of the 8 entities (Events, Speakers, Sponsors, Ticket Types, Workshops, Agenda Items, FAQs) plus `landing-page-content/edit.blade.php`.

## Dashboard

`admin.dashboard` is a top-level route with no `{event}` parameter, and the app has no session-based "currently selected event" concept — so the dashboard cannot show per-event entity counts. It replaces the current bare "Admin Dashboard + Logout button" with a simple events summary instead: total event count, a published/draft breakdown, and a shortcut to the Events list. Entity-specific screens (Speakers, Sponsors, etc.) remain reachable only via the sidebar once an admin has navigated into a specific event's context, exactly as routed today.

## Constraints

Existing feature tests assert on specific markup produced by the current bare views (e.g. `assertSee('id="partners"', false)`, the ticket-type `<option selected>` check in `TicketRequestPageTest`, various `assertSee()` calls against admin form/index content). The redesign must preserve every such hook exactly — same element IDs, same attribute presence, same visible text — unless a specific test is deliberately updated alongside an intentional, called-out change. This is a re-skin, not a rewrite of what each page contains or asserts.

## Testing

The full existing suite (89 tests as of the last merge) must stay green throughout — no route, controller, or validation logic changes anywhere in this spec, so no existing test's *behavioral* assertions should need to change. New tests to add:
- `GET /` redirects (302) to the CCS event's landing page
- Login screen renders the logo image
- Spot-check that a couple of the new Blade components (e.g. `x-admin.field`) render their label/error states correctly in isolation
