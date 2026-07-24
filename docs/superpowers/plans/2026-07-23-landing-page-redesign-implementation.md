# Landing Page Visual Redesign & New Sections Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Apply the imported Claude Design visual system to the 10 existing landing-page sections and add 5 new sections (Stats, Gallery, Testimonials, Contact, Newsletter), per `docs/superpowers/specs/2026-07-23-landing-page-redesign.md`.

**Architecture:** Same single-route landing page (`landing.show`). Existing sections keep their real bilingual data sources and are restyled in place. New sections get new tables (bilingual paired columns for CMS-authored content, plain columns for visitor-submitted content), new routes, and — where applicable — new admin CRUD screens using the existing shared admin component set.

**Tech Stack:** Laravel 12, PHP 8.3, MySQL, Tailwind CSS v4, Alpine.js, PHPUnit.

## Global Constraints

- Every existing `id="..."` element and route-linking assertion in `tests/Feature/LandingPageTest.php` must keep passing unchanged.
- No new `@theme` colors in `resources/css/app.css` — every visual choice reuses the 8 existing CCS tokens (`--color-ccs-coral/red/maroon/black/teal/teal-light/gold/gold-dark`).
- No fabricated data: Workshop cards show static `capacity`, never a fill percentage (no booking table exists). Awards stays a CMS teaser blurb, never nominee counts.
- Every new user-facing string ships with real bilingual support: paired `_ar`/`_en` columns for CMS-authored content, `app()->getLocale()` ternaries in views for display, matching the existing pattern exactly. Static UI chrome uses `__()` calls following the existing convention (no `lang/*.json` catalog exists yet in this app — that gap is pre-existing and out of scope here, same as every current partial).
- `image_path` on the new `gallery_photos` table is a manually-entered path/URL text field, not a file upload — matching the Speaker/Sponsor precedent.
- All new PHP files start with `declare(strict_types=1);` and use explicit return types / param types, matching every existing file in `app/`.
- Run `vendor/bin/pint --dirty --format agent` before each commit that touches PHP.
- Starting test count: 121 (must stay green throughout; grows as tasks add tests).
- Final section order in `resources/views/landing/show.blade.php`: nav, hero, about, stats, speakers, workshops, agenda-teaser, tickets, awards, gallery, testimonials, partners, faq, location, contact, newsletter, footer.

---

## Task 1: Design system CSS foundation

**Files:**
- Modify: `resources/css/app.css`

**Interfaces:**
- Produces: CSS classes `.ccs-section`, `.ccs-eyebrow`, `.ccs-fade-up`, `.ccs-float-slow`, `.ccs-pulse-glow`, and a global `[x-cloak]` rule — every later task's Blade views use these.

- [ ] **Step 1: Add the new CSS to `resources/css/app.css`**

Append after the existing `.ccs-flag-accent` rule (end of file):

```css

[x-cloak] {
  display: none !important;
}

.ccs-section {
  padding-inline: clamp(20px, 6vw, 80px);
  padding-bottom: 100px;
  max-width: 1440px;
  margin-inline: auto;
}

.ccs-eyebrow {
  font-size: 0.875rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  margin-bottom: 1rem;
}

@keyframes ccs-fade-up {
  from { opacity: 0; transform: translateY(28px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes ccs-float-slow {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-18px) rotate(3deg); }
}

@keyframes ccs-pulse-glow {
  0%, 100% { opacity: 0.35; }
  50% { opacity: 0.6; }
}

.ccs-fade-up {
  animation: ccs-fade-up 0.9s ease both;
}

.ccs-float-slow {
  animation: ccs-float-slow 14s ease-in-out infinite;
}

.ccs-pulse-glow {
  animation: ccs-pulse-glow 6s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
  .ccs-fade-up, .ccs-float-slow, .ccs-pulse-glow {
    animation: none;
  }
}
```

- [ ] **Step 2: Build and verify**

Run: `npm run build`
Expected: build succeeds with no errors.

Run (bash): `grep -c "ccs-fade-up" public/build/assets/app-*.css`
Expected: a non-zero count — confirms the new rules survived into the compiled stylesheet (Tailwind v4 does not purge hand-written CSS rules outside `@theme`, only its own generated utilities, so this is a sanity check, not a real risk).

- [ ] **Step 3: Run the full suite to confirm nothing broke**

Run: `php artisan test --compact`
Expected: 121 passed (no test covers CSS content, this just guards against a syntax error breaking the Vite build in a way that affects rendering).

- [ ] **Step 4: Commit**

```bash
git add resources/css/app.css
git commit -m "feat: add motion and section-layout CSS foundation for landing page redesign"
```

---

## Task 2: Nav + Footer chrome

**Files:**
- Create: `resources/views/landing/partials/nav.blade.php`
- Create: `resources/views/landing/partials/footer.blade.php`
- Modify: `resources/views/landing/show.blade.php`
- Test: Create `tests/Feature/LandingPageNavigationTest.php`

**Interfaces:**
- Consumes: `.ccs-section` (footer padding), `[x-cloak]` (mobile menu) from Task 1.
- Produces: `show.blade.php` now wraps the existing 10 `@include`s with a nav header and footer. Every later task that inserts a new section include does so between these two.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/LandingPageNavigationTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_links_to_the_ticket_and_about_sections(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('href="#about"', false);
        $response->assertSee('href="#tickets"', false);
        $response->assertSee('Request Ticket');
    }

    public function test_footer_renders_with_event_name(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published, 'name_en' => 'Content Creators Summit']);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('Content Creators Summit');
        $response->assertSee('All rights reserved.');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=LandingPageNavigationTest`
Expected: FAIL (`nav.blade.php` view not found).

- [ ] **Step 3: Create the nav partial**

Create `resources/views/landing/partials/nav.blade.php`:

```blade
{{-- resources/views/landing/partials/nav.blade.php --}}
<header x-data="{ open: false }" class="fixed top-0 inset-x-0 z-50 flex items-center justify-between gap-4 px-5 md:px-16 py-5 bg-ccs-black/80 backdrop-blur border-b border-white/10">
    <a href="#hero" class="font-display font-extrabold text-xl shrink-0">CCS <span class="text-ccs-coral">{{ $event->start_date->format('Y') }}</span></a>

    <nav class="hidden lg:flex items-center gap-6 text-sm font-semibold text-gray-300">
        <a href="#about" class="hover:text-white">{{ __('About') }}</a>
        <a href="#agenda-teaser" class="hover:text-white">{{ __('Agenda') }}</a>
        <a href="#speakers" class="hover:text-white">{{ __('Speakers') }}</a>
        <a href="#workshops" class="hover:text-white">{{ __('Workshops') }}</a>
        <a href="#awards" class="hover:text-white">{{ __('Awards') }}</a>
        <a href="#tickets" class="hover:text-white">{{ __('Tickets') }}</a>
        <a href="#partners" class="hover:text-white">{{ __('Sponsors') }}</a>
        <a href="#faq" class="hover:text-white">{{ __('FAQ') }}</a>
    </nav>

    <div class="flex items-center gap-3 shrink-0">
        <a href="#tickets" class="px-5 py-2.5 rounded-md bg-gradient-to-br from-ccs-red to-ccs-maroon text-sm font-bold whitespace-nowrap">{{ __('Request Ticket') }}</a>
        <button type="button" aria-label="{{ __('Menu') }}" class="lg:hidden w-11 h-11 rounded-md border border-white/20" @click="open = !open">&#9776;</button>
    </div>

    <div x-show="open" x-cloak x-transition class="absolute top-full inset-x-0 bg-ccs-black border-b border-white/10 flex flex-col px-5 pb-6 lg:hidden">
        <a href="#about" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('About') }}</a>
        <a href="#agenda-teaser" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Agenda') }}</a>
        <a href="#speakers" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Speakers') }}</a>
        <a href="#workshops" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Workshops') }}</a>
        <a href="#awards" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Awards') }}</a>
        <a href="#tickets" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Tickets') }}</a>
        <a href="#partners" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Sponsors') }}</a>
        <a href="#faq" class="py-3.5 font-semibold" @click="open = false">{{ __('FAQ') }}</a>
    </div>
</header>
```

- [ ] **Step 4: Create the footer partial**

Create `resources/views/landing/partials/footer.blade.php`:

```blade
{{-- resources/views/landing/partials/footer.blade.php --}}
<footer class="ccs-section pt-24">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-16">
        <div>
            <div class="font-display font-extrabold text-xl mb-4">CCS <span class="text-ccs-coral">{{ $event->start_date->format('Y') }}</span></div>
            <p class="text-sm text-gray-500 max-w-[220px]">{{ app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en }}</p>
        </div>
        <div class="flex flex-col gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">{{ __('Event') }}</span>
            <a href="#about" class="text-sm text-gray-400 hover:text-white">{{ __('About') }}</a>
            <a href="#agenda-teaser" class="text-sm text-gray-400 hover:text-white">{{ __('Agenda') }}</a>
            <a href="#speakers" class="text-sm text-gray-400 hover:text-white">{{ __('Speakers') }}</a>
            <a href="#workshops" class="text-sm text-gray-400 hover:text-white">{{ __('Workshops') }}</a>
        </div>
        <div class="flex flex-col gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">{{ __('Program') }}</span>
            <a href="#awards" class="text-sm text-gray-400 hover:text-white">{{ __('Awards') }}</a>
            <a href="#partners" class="text-sm text-gray-400 hover:text-white">{{ __('Sponsors') }}</a>
            <a href="#tickets" class="text-sm text-gray-400 hover:text-white">{{ __('Tickets') }}</a>
            <a href="#faq" class="text-sm text-gray-400 hover:text-white">{{ __('FAQs') }}</a>
        </div>
        <div class="flex flex-col gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">{{ __('Connect') }}</span>
            <a href="#" class="text-sm text-gray-400 hover:text-white">Instagram</a>
            <a href="#" class="text-sm text-gray-400 hover:text-white">LinkedIn</a>
            <a href="#" class="text-sm text-gray-400 hover:text-white">YouTube</a>
        </div>
    </div>
    <div class="flex flex-wrap justify-between items-center gap-4 pt-8 border-t border-white/10 text-xs text-gray-500">
        <span>&copy; {{ $event->start_date->format('Y') }} {{ app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en }}. {{ __('All rights reserved.') }}</span>
    </div>
</footer>
```

- [ ] **Step 5: Wrap `show.blade.php` with nav and footer**

Replace the full contents of `resources/views/landing/show.blade.php` with:

```blade
@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en)

@section('content')
    @include('landing.partials.nav', ['event' => $event])
    @include('landing.partials.hero', ['event' => $event])
    @include('landing.partials.about', ['event' => $event])
    @include('landing.partials.speakers', ['event' => $event])
    @include('landing.partials.workshops-teaser', ['event' => $event])
    @include('landing.partials.agenda-teaser', ['event' => $event])
    @include('landing.partials.tickets', ['event' => $event])
    @include('landing.partials.awards-teaser', ['event' => $event])
    @include('landing.partials.partners', ['event' => $event])
    @include('landing.partials.faq', ['event' => $event])
    @include('landing.partials.location', ['event' => $event])
    @include('landing.partials.footer', ['event' => $event])
@endsection
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --compact --filter=LandingPageNavigationTest`
Expected: PASS (2/2)

- [ ] **Step 7: Run the full suite**

Run: `php artisan test --compact`
Expected: 123 passed

- [ ] **Step 8: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/landing/partials/nav.blade.php resources/views/landing/partials/footer.blade.php resources/views/landing/show.blade.php tests/Feature/LandingPageNavigationTest.php
git commit -m "feat: add landing page nav header and footer chrome"
```

---

## Task 3: Hero redesign

**Files:**
- Modify: `resources/views/landing/partials/hero.blade.php`
- Test: Modify `tests/Feature/LandingPageTest.php`

**Interfaces:**
- Consumes: `.ccs-fade-up`, `.ccs-float-slow`, `.ccs-pulse-glow` from Task 1.
- Fixes two pre-existing bugs while rewriting this exact file: the eyebrow line hardcoded `$event->name_ar` regardless of locale (now shows the locale-correct date/venue), and the CMS `hero_headline` field was set via the admin editor but never rendered (now used as the H1, falling back to the event name when unset — this is why `test_landing_page_renders_for_an_event`, which never sets `hero_headline`, keeps passing unchanged).

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/LandingPageTest.php` (inside the class, needs `use App\Enums\LandingPageSection;` and `use App\Models\LandingPageContent;`, both already imported):

```php
    public function test_hero_headline_overrides_event_name_when_set(): void
    {
        $event = Event::factory()->create(['name_en' => 'Content Creators Summit']);
        LandingPageContent::factory()->for($event)->create([
            'section' => LandingPageSection::Hero,
            'field_key' => 'headline',
            'value_en' => 'The Future of Content',
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('The Future of Content');
        $response->assertDontSee('Content Creators Summit');
    }

    public function test_hero_eyebrow_shows_locale_correct_venue(): void
    {
        $event = Event::factory()->create([
            'venue_name_ar' => 'قاعة المؤتمرات', 'venue_name_en' => 'Convention Hall',
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('Convention Hall');
        $response->assertDontSee('قاعة المؤتمرات');
    }
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: FAIL on the 2 new tests (current hero shows event name always, and shows `name_ar` in the eyebrow regardless of locale).

- [ ] **Step 3: Rewrite the Hero partial**

Replace the full contents of `resources/views/landing/partials/hero.blade.php`:

```blade
{{-- resources/views/landing/partials/hero.blade.php --}}
@php
    $headline = $event->contentFor(\App\Enums\LandingPageSection::Hero, 'headline');
    $headlineText = app()->getLocale() === 'ar'
        ? ($headline?->value_ar ?: $event->name_ar)
        : ($headline?->value_en ?: $event->name_en);
    $venueName = app()->getLocale() === 'ar' ? $event->venue_name_ar : $event->venue_name_en;
    $tagline = app()->getLocale() === 'ar' ? $event->tagline_ar : $event->tagline_en;
@endphp
<section id="hero" class="ccs-hero relative min-h-screen flex flex-col justify-center overflow-hidden px-5 md:px-16 pt-36 pb-24">
    <div class="absolute w-[520px] h-[520px] border border-white/10 rounded-full -top-40 -right-28 ccs-float-slow" aria-hidden="true"></div>
    <div class="absolute w-[180px] h-[180px] rounded-full bg-ccs-teal-light/30 blur-3xl top-1/4 left-1/2 ccs-pulse-glow" aria-hidden="true"></div>

    <div class="relative max-w-5xl ccs-fade-up">
        <p class="text-sm font-bold tracking-[0.14em] uppercase text-ccs-coral mb-5">
            {{ $event->start_date->format('M j') }}&ndash;{{ $event->end_date->format('j, Y') }}
            @if($venueName) &middot; {{ $venueName }} @endif
        </p>
        <h1 class="font-display text-[clamp(2.75rem,9vw,7rem)] font-extrabold leading-[0.98] tracking-tight mb-7">{{ $headlineText }}</h1>
        @if($tagline)
            <p class="text-lg md:text-2xl text-gray-300 max-w-xl leading-relaxed mb-11">{{ $tagline }}</p>
        @endif
        <div class="flex flex-wrap gap-4 mb-16">
            <a href="#tickets" class="px-8 py-4 rounded-lg bg-gradient-to-br from-ccs-red to-ccs-maroon text-base font-bold">{{ __('Request Your Ticket') }}</a>
            <a href="#about" class="px-8 py-4 rounded-lg border border-white/35 text-base font-bold">{{ __('Explore Event') }}</a>
        </div>

        <div class="flex flex-wrap gap-3 md:gap-7" x-data="{
                now: Date.now(),
                target: new Date('{{ $event->start_date->toDateString() }}').getTime(),
                get diff() { return Math.max(0, this.target - this.now); },
                get d() { return String(Math.floor(this.diff / 86400000)).padStart(2, '0'); },
                get h() { return String(Math.floor(this.diff / 3600000) % 24).padStart(2, '0'); },
                get m() { return String(Math.floor(this.diff / 60000) % 60).padStart(2, '0'); },
                get s() { return String(Math.floor(this.diff / 1000) % 60).padStart(2, '0'); },
            }" x-init="setInterval(() => now = Date.now(), 1000)">
            <div class="flex flex-col items-center px-6 py-4 bg-white/5 border border-white/10 rounded-xl min-w-[88px]">
                <span class="text-3xl md:text-4xl font-extrabold tabular-nums" x-text="d"></span>
                <span class="text-xs uppercase tracking-wide text-gray-400 mt-1.5">{{ __('Days') }}</span>
            </div>
            <div class="flex flex-col items-center px-6 py-4 bg-white/5 border border-white/10 rounded-xl min-w-[88px]">
                <span class="text-3xl md:text-4xl font-extrabold tabular-nums" x-text="h"></span>
                <span class="text-xs uppercase tracking-wide text-gray-400 mt-1.5">{{ __('Hours') }}</span>
            </div>
            <div class="flex flex-col items-center px-6 py-4 bg-white/5 border border-white/10 rounded-xl min-w-[88px]">
                <span class="text-3xl md:text-4xl font-extrabold tabular-nums" x-text="m"></span>
                <span class="text-xs uppercase tracking-wide text-gray-400 mt-1.5">{{ __('Min') }}</span>
            </div>
            <div class="flex flex-col items-center px-6 py-4 bg-white/5 border border-white/10 rounded-xl min-w-[88px]">
                <span class="text-3xl md:text-4xl font-extrabold tabular-nums" x-text="s"></span>
                <span class="text-xs uppercase tracking-wide text-gray-400 mt-1.5">{{ __('Sec') }}</span>
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS (all LandingPageTest tests, including the 2 new ones)

- [ ] **Step 5: Run the full suite**

Run: `php artisan test --compact`
Expected: 125 passed

- [ ] **Step 6: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/landing/partials/hero.blade.php tests/Feature/LandingPageTest.php
git commit -m "feat: redesign hero section, fix locale-bugged eyebrow and wire up orphaned hero_headline field"
```

---

## Task 4: Stats section (new)

**Files:**
- Modify: `app/Enums/LandingPageSection.php`
- Modify: `app/Http/Requests/Admin/LandingPageContentRequest.php`
- Modify: `app/Http/Controllers/Admin/LandingPageContentController.php`
- Modify: `resources/views/admin/landing-page-content/edit.blade.php`
- Create: `resources/views/landing/partials/stats.blade.php`
- Modify: `resources/views/landing/show.blade.php`
- Test: Modify `tests/Feature/Admin/LandingPageContentCrudTest.php`
- Test: Create `tests/Feature/LandingPageStatsTest.php`

**Interfaces:**
- Consumes: `.ccs-section` from Task 1; `LandingPageSection` enum, `Event::contentFor()` (both already exist).
- Produces: `LandingPageSection::Stats` case, field keys `attendees_count` / `countries_count` — no other task depends on this.

- [ ] **Step 1: Add the `Stats` enum case**

In `app/Enums/LandingPageSection.php`, add a new case:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum LandingPageSection: string
{
    case Hero = 'hero';
    case About = 'about';
    case Stats = 'stats';
    case Location = 'location';
    case AwardsTeaser = 'awards_teaser';
}
```

- [ ] **Step 2: Write the failing admin test**

In `tests/Feature/Admin/LandingPageContentCrudTest.php`, update the `payload()` method to include the 2 new fields, and update both count assertions from 4 to 6:

```php
    private function payload(): array
    {
        return [
            'hero_headline_ar' => 'العنوان', 'hero_headline_en' => 'Headline',
            'about_body_ar' => 'نبذة', 'about_body_en' => 'About body',
            'location_intro_ar' => 'الموقع', 'location_intro_en' => 'Location intro',
            'awards_teaser_blurb_ar' => 'الجوائز', 'awards_teaser_blurb_en' => 'Awards blurb',
            'stats_attendees_count_ar' => '٢٠٠+', 'stats_attendees_count_en' => '200+',
            'stats_countries_count_ar' => '١٥', 'stats_countries_count_en' => '15',
        ];
    }
```

Change `$this->assertSame(4, $event->landingPageContent()->count());` to `$this->assertSame(6, ...)` in both `test_admin_can_set_landing_page_content` and `test_resubmitting_content_updates_instead_of_duplicating`.

- [ ] **Step 3: Run test to verify it fails**

Run: `php artisan test --compact --filter=LandingPageContentCrudTest`
Expected: FAIL (only 4 rows created — the request doesn't accept or persist the 2 new fields yet).

- [ ] **Step 4: Extend the FormRequest**

In `app/Http/Requests/Admin/LandingPageContentRequest.php`, add to the `rules()` array:

```php
            'stats_attendees_count_ar' => ['nullable', 'string', 'max:50'],
            'stats_attendees_count_en' => ['nullable', 'string', 'max:50'],
            'stats_countries_count_ar' => ['nullable', 'string', 'max:50'],
            'stats_countries_count_en' => ['nullable', 'string', 'max:50'],
```

- [ ] **Step 5: Extend the controller's FIELDS map**

In `app/Http/Controllers/Admin/LandingPageContentController.php`, add to the `FIELDS` const array:

```php
        'stats_attendees_count' => ['section' => LandingPageSection::Stats, 'field_key' => 'attendees_count'],
        'stats_countries_count' => ['section' => LandingPageSection::Stats, 'field_key' => 'countries_count'],
```

- [ ] **Step 6: Add the Stats fieldset to the edit view**

In `resources/views/admin/landing-page-content/edit.blade.php`, add before the closing `<x-admin.button>`:

```blade
        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('Stats') }}</h2>
        <x-admin.bilingual-field name="stats_attendees_count" label="{{ __('Attendees') }}" :value-ar="old('stats_attendees_count_ar', $values['stats_attendees_count_ar'])" :value-en="old('stats_attendees_count_en', $values['stats_attendees_count_en'])" />
        <x-admin.bilingual-field name="stats_countries_count" label="{{ __('Countries') }}" :value-ar="old('stats_countries_count_ar', $values['stats_countries_count_ar'])" :value-en="old('stats_countries_count_en', $values['stats_countries_count_en'])" />

```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --compact --filter=LandingPageContentCrudTest`
Expected: PASS (3/3)

- [ ] **Step 8: Write the failing public Stats test**

Create `tests/Feature/LandingPageStatsTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\LandingPageContent;
use App\Models\Speaker;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_section_shows_live_and_manual_values(): void
    {
        $event = Event::factory()->create();
        Speaker::factory()->for($event)->create();
        Workshop::factory()->for($event)->create();
        LandingPageContent::factory()->for($event)->create([
            'section' => LandingPageSection::Stats, 'field_key' => 'attendees_count', 'value_en' => '4,200+',
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('id="stats"', false);
        $response->assertSee('4,200+');

        $html = view('landing.partials.stats', ['event' => $event->load('speakers', 'workshops', 'landingPageContent')])->render();
        $this->assertStringContainsString('data-stat-value="speakers">1<', $html);
        $this->assertStringContainsString('data-stat-value="workshops">1<', $html);
    }

    public function test_stats_section_omitted_when_no_data(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertDontSee('id="stats"', false);
    }
}
```

- [ ] **Step 9: Run test to verify it fails**

Run: `php artisan test --compact --filter=LandingPageStatsTest`
Expected: FAIL (`stats.blade.php` doesn't exist, and `show.blade.php` doesn't include it).

- [ ] **Step 10: Create the Stats partial**

Create `resources/views/landing/partials/stats.blade.php`:

```blade
{{-- resources/views/landing/partials/stats.blade.php --}}
@php
    $attendees = $event->contentFor(\App\Enums\LandingPageSection::Stats, 'attendees_count');
    $countries = $event->contentFor(\App\Enums\LandingPageSection::Stats, 'countries_count');
    $attendeesText = app()->getLocale() === 'ar' ? $attendees?->value_ar : $attendees?->value_en;
    $countriesText = app()->getLocale() === 'ar' ? $countries?->value_ar : $countries?->value_en;
    $speakerCount = $event->speakers->count();
    $workshopCount = $event->workshops->count();
@endphp
@if($attendeesText || $countriesText || $speakerCount || $workshopCount)
    <section id="stats" class="ccs-section">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-px bg-white/10 border border-white/10 rounded-2xl overflow-hidden">
            <div class="bg-ccs-black px-8 py-11 text-center">
                <div class="text-4xl md:text-5xl font-extrabold text-ccs-coral" data-stat-value="attendees">{{ $attendeesText ?? '—' }}</div>
                <div class="text-sm text-gray-400 mt-2.5">{{ __('Attendees') }}</div>
            </div>
            <div class="bg-ccs-black px-8 py-11 text-center">
                <div class="text-4xl md:text-5xl font-extrabold text-ccs-teal-light" data-stat-value="speakers">{{ $speakerCount }}</div>
                <div class="text-sm text-gray-400 mt-2.5">{{ __('Speakers') }}</div>
            </div>
            <div class="bg-ccs-black px-8 py-11 text-center">
                <div class="text-4xl md:text-5xl font-extrabold text-ccs-gold" data-stat-value="countries">{{ $countriesText ?? '—' }}</div>
                <div class="text-sm text-gray-400 mt-2.5">{{ __('Countries') }}</div>
            </div>
            <div class="bg-ccs-black px-8 py-11 text-center">
                <div class="text-4xl md:text-5xl font-extrabold" data-stat-value="workshops">{{ $workshopCount }}</div>
                <div class="text-sm text-gray-400 mt-2.5">{{ __('Workshops') }}</div>
            </div>
        </div>
    </section>
@endif
```

- [ ] **Step 11: Insert the include in `show.blade.php`**

In `resources/views/landing/show.blade.php`, insert a new line between the `about` and `speakers` includes:

```blade
    @include('landing.partials.about', ['event' => $event])
    @include('landing.partials.stats', ['event' => $event])
    @include('landing.partials.speakers', ['event' => $event])
```

- [ ] **Step 12: Run tests to verify they pass**

Run: `php artisan test --compact --filter=LandingPageStatsTest`
Expected: PASS (2/2)

- [ ] **Step 13: Run the full suite**

Run: `php artisan test --compact`
Expected: 127 passed

- [ ] **Step 14: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Enums/LandingPageSection.php app/Http/Requests/Admin/LandingPageContentRequest.php app/Http/Controllers/Admin/LandingPageContentController.php resources/views/admin/landing-page-content/edit.blade.php resources/views/landing/partials/stats.blade.php resources/views/landing/show.blade.php tests/Feature/Admin/LandingPageContentCrudTest.php tests/Feature/LandingPageStatsTest.php
git commit -m "feat: add Stats section with live speaker/workshop counts and manual attendee/country fields"
```

---

## Task 5: Restyle About, Awards, Partners, FAQ, and Location

**Files:**
- Modify: `resources/views/landing/partials/about.blade.php`
- Modify: `resources/views/landing/partials/awards-teaser.blade.php`
- Modify: `resources/views/landing/partials/partners.blade.php`
- Modify: `resources/views/landing/partials/faq.blade.php`
- Modify: `resources/views/landing/partials/location.blade.php`

**Interfaces:**
- Consumes: `.ccs-section`, `.ccs-eyebrow` from Task 1.
- No data/route changes — pure visual restyle of 5 sections, all existing tests must keep passing unchanged.

- [ ] **Step 1: Confirm the baseline still passes**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS (all current tests, before any changes in this task)

- [ ] **Step 2: Restyle About**

Replace `resources/views/landing/partials/about.blade.php`:

```blade
{{-- resources/views/landing/partials/about.blade.php --}}
@php $about = $event->contentFor(\App\Enums\LandingPageSection::About, 'body'); @endphp
@if($about)
    <section id="about" class="ccs-section grid grid-cols-1 lg:grid-cols-2 gap-16 items-center pt-32">
        <div>
            <div class="ccs-eyebrow text-ccs-teal-light">{{ __('About the Event') }}</div>
            <p class="text-lg md:text-xl text-gray-300 leading-relaxed max-w-xl">{{ app()->getLocale() === 'ar' ? $about->value_ar : $about->value_en }}</p>
        </div>
        <div class="aspect-[4/5] rounded-2xl border border-white/10 bg-white/5" aria-hidden="true"></div>
    </section>
@endif
```

- [ ] **Step 3: Restyle Awards teaser**

Replace `resources/views/landing/partials/awards-teaser.blade.php`:

```blade
{{-- resources/views/landing/partials/awards-teaser.blade.php --}}
<section id="awards" class="ccs-section">
    <div class="ccs-eyebrow text-ccs-coral">{{ __('CCS Awards') }}</div>
    <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-6">{{ __("Honoring the year's defining work.") }}</h2>
    @php $blurb = $event->contentFor(\App\Enums\LandingPageSection::AwardsTeaser, 'blurb'); @endphp
    @if($blurb)
        <p class="text-lg text-gray-300 max-w-2xl leading-relaxed mb-8">{{ app()->getLocale() === 'ar' ? $blurb->value_ar : $blurb->value_en }}</p>
    @endif
    <a href="{{ route('awards.show', $event) }}" class="inline-block px-7 py-3.5 rounded-lg border border-white/35 text-sm font-bold hover:bg-white hover:text-ccs-black">{{ __('Learn More') }}</a>
</section>
```

- [ ] **Step 4: Restyle Partners**

Replace `resources/views/landing/partials/partners.blade.php`:

```blade
{{-- resources/views/landing/partials/partners.blade.php --}}
@if($event->sponsors->isNotEmpty())
    <section id="partners" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-teal-light">{{ __('Sponsors & Partners') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12">{{ __('Backed by the industry.') }}</h2>
        @foreach($event->sponsors->groupBy('tier') as $tier => $sponsors)
            <div class="mb-10">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-4">{{ ucfirst($tier) }}</div>
                <div class="flex flex-wrap gap-4">
                    @foreach($sponsors as $sponsor)
                        <img src="{{ $sponsor->logo_path ?? '/images/placeholder-logo.png' }}" alt="{{ app()->getLocale() === 'ar' ? $sponsor->name_ar : $sponsor->name_en }}" class="h-12 rounded-lg border border-white/10 bg-white/5 px-4 py-2 object-contain">
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endif
```

- [ ] **Step 5: Restyle FAQ**

Replace `resources/views/landing/partials/faq.blade.php`:

```blade
{{-- resources/views/landing/partials/faq.blade.php --}}
@if($event->faqs->isNotEmpty())
    <section id="faq" class="ccs-section max-w-3xl">
        <div class="ccs-eyebrow text-ccs-red">{{ __('FAQs') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-10">{{ __('Good questions.') }}</h2>
        <div class="flex flex-col">
            @foreach($event->faqs as $faq)
                <div x-data="{ open: false }" class="border-b border-white/10">
                    <button type="button" class="w-full flex justify-between items-center gap-5 py-6 text-left font-display font-bold text-lg" @click="open = !open">
                        <span>{{ app()->getLocale() === 'ar' ? $faq->question_ar : $faq->question_en }}</span>
                        <span class="text-2xl text-gray-500" x-text="open ? '&#8211;' : '+'"></span>
                    </button>
                    <p x-show="open" x-cloak class="pb-6 text-gray-400 leading-relaxed max-w-xl">{{ app()->getLocale() === 'ar' ? $faq->answer_ar : $faq->answer_en }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endif
```

- [ ] **Step 6: Restyle Location**

Replace `resources/views/landing/partials/location.blade.php`:

```blade
{{-- resources/views/landing/partials/location.blade.php --}}
@php $intro = $event->contentFor(\App\Enums\LandingPageSection::Location, 'intro'); @endphp
@if($intro || $event->venue_address_en)
    <section id="location" class="ccs-section grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <div>
            <div class="ccs-eyebrow text-ccs-teal-light">{{ __('Venue') }}</div>
            <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-6">{{ app()->getLocale() === 'ar' ? $event->venue_name_ar : $event->venue_name_en }}</h2>
            @if($intro)
                <p class="text-gray-300 leading-relaxed mb-4">{{ app()->getLocale() === 'ar' ? $intro->value_ar : $intro->value_en }}</p>
            @endif
            <p class="text-gray-400 leading-relaxed">{{ app()->getLocale() === 'ar' ? $event->venue_address_ar : $event->venue_address_en }}</p>
        </div>
        @if($event->map_embed_url)
            <iframe src="{{ $event->map_embed_url }}" class="w-full aspect-video rounded-2xl border border-white/10" style="border:0;" loading="lazy"></iframe>
        @else
            <div class="aspect-video rounded-2xl border border-white/10 bg-white/5" aria-hidden="true"></div>
        @endif
    </section>
@endif
```

- [ ] **Step 7: Run tests to verify nothing broke**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS (all tests, unchanged assertions)

- [ ] **Step 8: Run the full suite**

Run: `php artisan test --compact`
Expected: 127 passed

- [ ] **Step 9: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/landing/partials/about.blade.php resources/views/landing/partials/awards-teaser.blade.php resources/views/landing/partials/partners.blade.php resources/views/landing/partials/faq.blade.php resources/views/landing/partials/location.blade.php
git commit -m "style: redesign About, Awards, Partners, FAQ, and Location sections"
```

---

## Task 6: Restyle Speakers

**Files:**
- Modify: `resources/views/landing/partials/speakers.blade.php`

- [ ] **Step 1: Confirm baseline passes**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS

- [ ] **Step 2: Restyle Speakers**

Replace `resources/views/landing/partials/speakers.blade.php`:

```blade
{{-- resources/views/landing/partials/speakers.blade.php --}}
@if($event->speakers->isNotEmpty())
    <section id="speakers" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-coral">{{ __('Featured Speakers') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12">{{ __('Voices shaping the industry.') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-7">
            @foreach($event->speakers as $speaker)
                <div x-data="{ open: false }" class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    <img src="{{ $speaker->photo_path ?? '/images/placeholder-speaker.png' }}" class="w-full aspect-square object-cover" alt="{{ app()->getLocale() === 'ar' ? $speaker->name_ar : $speaker->name_en }}">
                    <div class="p-5">
                        <h3 class="font-display font-bold text-lg mb-1">{{ app()->getLocale() === 'ar' ? $speaker->name_ar : $speaker->name_en }}</h3>
                        <p class="text-sm text-gray-400 mb-3">{{ app()->getLocale() === 'ar' ? $speaker->title_ar : $speaker->title_en }}</p>
                        <button type="button" class="text-sm font-bold border border-white/25 rounded-md px-3 py-1.5 hover:bg-white hover:text-ccs-black" @click="open = !open">{{ __('Bio') }}</button>
                        <p x-show="open" x-cloak class="text-sm text-gray-400 leading-relaxed mt-3">{{ app()->getLocale() === 'ar' ? $speaker->bio_ar : $speaker->bio_en }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
```

- [ ] **Step 3: Run tests to verify nothing broke**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS

- [ ] **Step 4: Run the full suite**

Run: `php artisan test --compact`
Expected: 127 passed

- [ ] **Step 5: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/landing/partials/speakers.blade.php
git commit -m "style: redesign Speakers section as a card grid"
```

---

## Task 7: Restyle Workshops (real cards, static capacity)

**Files:**
- Modify: `resources/views/landing/partials/workshops-teaser.blade.php`
- Test: Modify `tests/Feature/LandingPageTest.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/LandingPageTest.php`:

```php
    public function test_workshops_section_shows_description_and_capacity(): void
    {
        $event = Event::factory()->create();
        Workshop::factory()->for($event)->create([
            'name_en' => 'Editing at Scale', 'description_en' => 'Hands-on editing techniques.', 'capacity' => 40,
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('Editing at Scale');
        $response->assertSee('Hands-on editing techniques.');
        $response->assertSee('40 seats');
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: FAIL (current teaser only shows the name, not description or capacity)

- [ ] **Step 3: Restyle Workshops**

Replace `resources/views/landing/partials/workshops-teaser.blade.php`:

```blade
{{-- resources/views/landing/partials/workshops-teaser.blade.php --}}
@if($event->workshops->isNotEmpty())
    <section id="workshops" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-gold">{{ __('Workshops') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12 max-w-xl">{{ __('Choose your own workshops.') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            @foreach($event->workshops->take(3) as $workshop)
                <div class="bg-white/5 border border-white/10 rounded-2xl p-7 flex flex-col gap-4">
                    <h3 class="font-display text-lg font-bold">{{ app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en }}</h3>
                    <p class="text-sm text-gray-400 leading-relaxed flex-1">{{ app()->getLocale() === 'ar' ? $workshop->description_ar : $workshop->description_en }}</p>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ trans_choice(':count seat|:count seats', $workshop->capacity, ['count' => $workshop->capacity]) }}</p>
                    <a href="{{ route('workshops.show', [$event, $workshop]) }}" class="text-sm font-bold text-ccs-teal-light hover:underline">{{ __('View Workshop') }}</a>
                </div>
            @endforeach
        </div>
        <a href="{{ route('workshops.index', $event) }}" class="inline-block px-7 py-3.5 rounded-lg border border-white/35 text-sm font-bold hover:bg-white hover:text-ccs-black">{{ __('See All Workshops') }}</a>
    </section>
@endif
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS (including `test_workshops_teaser_links_to_workshops_index`, unchanged)

- [ ] **Step 5: Run the full suite**

Run: `php artisan test --compact`
Expected: 128 passed

- [ ] **Step 6: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/landing/partials/workshops-teaser.blade.php tests/Feature/LandingPageTest.php
git commit -m "style: redesign Workshops section with real capacity, no fabricated seat-fill"
```

---

## Task 8: Restyle Agenda (day-tabbed preview)

**Files:**
- Modify: `resources/views/landing/partials/agenda-teaser.blade.php`
- Test: Modify `tests/Feature/LandingPageTest.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/LandingPageTest.php` (needs `use App\Models\Speaker;`, already imported by Task 7):

```php
    public function test_agenda_section_shows_real_session_details(): void
    {
        $event = Event::factory()->create();
        $speaker = Speaker::factory()->for($event)->create(['name_en' => 'Maya Chen']);
        AgendaItem::factory()->for($event)->create([
            'speaker_id' => $speaker->id, 'title_en' => 'Opening Keynote', 'start_time' => '09:00',
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('Opening Keynote');
        $response->assertSee('Maya Chen');
        $response->assertSee('09:00');
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: FAIL (current teaser only shows a heading and a link, no session details)

- [ ] **Step 3: Restyle Agenda**

Replace `resources/views/landing/partials/agenda-teaser.blade.php`:

```blade
{{-- resources/views/landing/partials/agenda-teaser.blade.php --}}
@if($event->agendaItems->isNotEmpty())
    @php $days = $event->agendaItems->groupBy(fn ($item) => $item->day_date->toDateString())->values(); @endphp
    <section id="agenda-teaser" class="ccs-section" x-data="{ day: 0 }">
        <div class="ccs-eyebrow text-ccs-teal-light">{{ __('Agenda') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-10">{{ __('Three days, deliberately paced.') }}</h2>

        <div class="flex gap-3 mb-10 flex-wrap">
            @foreach($days as $index => $sessions)
                <button type="button" @click="day = {{ $index }}" :class="day === {{ $index }} ? 'bg-ccs-red border-ccs-red' : 'border-white/10'" class="px-6 py-3.5 rounded-lg border text-sm font-bold text-gray-300">
                    {{ __('Day :n', ['n' => $index + 1]) }} &middot; {{ $sessions->first()->day_date->format('M j') }}
                </button>
            @endforeach
        </div>

        @foreach($days as $index => $sessions)
            <div x-show="day === {{ $index }}" x-cloak class="flex flex-col">
                @foreach($sessions as $item)
                    <div class="grid grid-cols-[80px_1fr_auto] md:grid-cols-[110px_1fr_auto] gap-4 md:gap-6 items-center py-6 border-b border-white/10">
                        <span class="text-sm font-bold text-gray-400 tabular-nums">{{ $item->start_time->format('H:i') }}</span>
                        <div>
                            <div class="font-display font-bold text-lg mb-1">{{ app()->getLocale() === 'ar' ? $item->title_ar : $item->title_en }}</div>
                            @if($item->speaker)
                                <div class="text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? $item->speaker->name_ar : $item->speaker->name_en }}</div>
                            @endif
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wide text-ccs-coral border border-ccs-coral/40 rounded-md px-3 py-1.5 whitespace-nowrap">{{ __(ucfirst($item->type->value)) }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach

        <a href="{{ route('agenda.show', $event) }}" class="inline-block mt-10 px-7 py-3.5 rounded-lg border border-white/35 text-sm font-bold hover:bg-white hover:text-ccs-black">{{ __('View Full Agenda') }}</a>
    </section>
@endif
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS (including `test_agenda_teaser_links_to_agenda_page`, unchanged)

- [ ] **Step 5: Run the full suite**

Run: `php artisan test --compact`
Expected: 129 passed

- [ ] **Step 6: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/landing/partials/agenda-teaser.blade.php tests/Feature/LandingPageTest.php
git commit -m "feat: upgrade Agenda section to a real day-tabbed session preview"
```

---

## Task 9: Restyle Tickets (workshop slot label)

**Files:**
- Modify: `resources/views/landing/partials/tickets.blade.php`
- Test: Modify `tests/Feature/LandingPageTest.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/LandingPageTest.php`:

```php
    public function test_tickets_section_shows_workshop_slot_label(): void
    {
        $event = Event::factory()->create();
        TicketType::factory()->for($event)->create(['name_en' => 'VIP', 'workshop_slot_count' => 1]);
        TicketType::factory()->for($event)->create(['name_en' => 'General', 'workshop_slot_count' => 0]);
        TicketType::factory()->for($event)->create(['name_en' => 'Platinum', 'workshop_slot_count' => null]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('1 workshop included');
        $response->assertSee('No workshops included');
        $response->assertSee('Unlimited workshops');
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: FAIL (current tickets section doesn't render `workshop_slot_count` at all)

- [ ] **Step 3: Restyle Tickets**

Replace `resources/views/landing/partials/tickets.blade.php`:

```blade
{{-- resources/views/landing/partials/tickets.blade.php --}}
@if($event->ticketTypes->isNotEmpty())
    <section id="tickets" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-red">{{ __('Ticket Request') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12 max-w-2xl">{{ __("There's no checkout. There's a review.") }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($event->ticketTypes->where('is_active', true) as $ticketType)
                @php
                    $slotCount = $ticketType->workshop_slot_count;
                    $slotLabel = is_null($slotCount)
                        ? __('Unlimited workshops')
                        : ($slotCount === 0 ? __('No workshops included') : trans_choice(':count workshop included|:count workshops included', $slotCount, ['count' => $slotCount]));
                @endphp
                <div class="bg-white/5 border border-white/10 rounded-2xl p-8 flex flex-col gap-5">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-3">{{ app()->getLocale() === 'ar' ? $ticketType->name_ar : $ticketType->name_en }}</div>
                        <div class="text-3xl font-extrabold">{{ $ticketType->price }} {{ $ticketType->currency }}</div>
                    </div>
                    <div class="text-sm font-bold text-ccs-gold">{{ $slotLabel }}</div>
                    <p class="text-sm text-gray-400 leading-relaxed flex-1">{{ app()->getLocale() === 'ar' ? $ticketType->description_ar : $ticketType->description_en }}</p>
                    <a href="{{ route('ticket-requests.create', $event) }}?type={{ $ticketType->id }}" class="text-center px-5 py-3.5 rounded-lg bg-gradient-to-br from-ccs-red to-ccs-maroon text-sm font-bold">
                        {{ __('Request This Ticket') }}
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS (including `test_tickets_section_links_to_request_page`, unchanged)

- [ ] **Step 5: Run the full suite**

Run: `php artisan test --compact`
Expected: 130 passed

- [ ] **Step 6: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/landing/partials/tickets.blade.php tests/Feature/LandingPageTest.php
git commit -m "style: redesign Tickets section with workshop slot count labels"
```

---

## Task 10: Gallery Photos (new — full vertical slice)

**Files:**
- Create: `database/migrations/2026_07_23_100000_create_gallery_photos_table.php`
- Create: `app/Models/GalleryPhoto.php`
- Create: `database/factories/GalleryPhotoFactory.php`
- Modify: `app/Models/Event.php`
- Create: `app/Http/Requests/Admin/GalleryPhotoRequest.php`
- Create: `app/Http/Controllers/Admin/GalleryPhotoController.php`
- Create: `resources/views/admin/gallery-photos/index.blade.php`
- Create: `resources/views/admin/gallery-photos/form.blade.php`
- Modify: `resources/views/admin/partials/sidebar.blade.php`
- Modify: `routes/web.php`
- Create: `resources/views/landing/partials/gallery.blade.php`
- Modify: `resources/views/landing/show.blade.php`
- Modify: `app/Http/Controllers/LandingPageController.php`
- Test: Create `tests/Feature/Admin/GalleryPhotoCrudTest.php`
- Test: Modify `tests/Feature/LandingPageTest.php`

**Interfaces:**
- Produces: `Event::galleryPhotos()` (`HasMany<GalleryPhoto>`, ordered by `sort_order`) — consumed by the public `gallery.blade.php` partial and Task 14's integration test.

- [ ] **Step 1: Create the migration**

Create `database/migrations/2026_07_23_100000_create_gallery_photos_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption_ar')->nullable();
            $table->string('caption_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photos');
    }
};
```

- [ ] **Step 2: Create the model**

Create `app/Models/GalleryPhoto.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GalleryPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'image_path', 'caption_ar', 'caption_en', 'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): GalleryPhotoFactory
    {
        return GalleryPhotoFactory::new();
    }
}
```

- [ ] **Step 3: Create the factory**

Create `database/factories/GalleryPhotoFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

class GalleryPhotoFactory extends Factory
{
    protected $model = GalleryPhoto::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'image_path' => '/images/placeholder-gallery.jpg',
            'caption_ar' => $this->faker->sentence(3),
            'caption_en' => $this->faker->sentence(3),
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 4: Add the relation to Event**

In `app/Models/Event.php`, add after the `faqs()` method:

```php
    public function galleryPhotos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order');
    }
```

- [ ] **Step 5: Write the failing admin CRUD test**

Create `tests/Feature/Admin/GalleryPhotoCrudTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryPhotoCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_gallery_photo(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.gallery-photos.store', $event), [
            'image_path' => '/images/hall.jpg', 'caption_ar' => 'القاعة', 'caption_en' => 'The Hall', 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.gallery-photos.index', $event));
        $this->assertDatabaseHas('gallery_photos', ['event_id' => $event->id, 'caption_en' => 'The Hall']);
    }

    public function test_creating_a_gallery_photo_requires_an_image_path(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.gallery-photos.store', $event), [
            'image_path' => '',
        ]);

        $response->assertSessionHasErrors(['image_path']);
    }

    public function test_admin_can_delete_a_gallery_photo(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $photo = GalleryPhoto::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.gallery-photos.destroy', [$event, $photo]));

        $response->assertRedirect(route('admin.events.gallery-photos.index', $event));
        $this->assertDatabaseMissing('gallery_photos', ['id' => $photo->id]);
    }

    public function test_admin_can_view_the_index_page_with_records(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        GalleryPhoto::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.gallery-photos.index', $event));

        $response->assertOk();
    }

    public function test_admin_can_view_the_edit_page(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $photo = GalleryPhoto::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.gallery-photos.edit', [$event, $photo]));

        $response->assertOk();
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `php artisan test --compact --filter=GalleryPhotoCrudTest`
Expected: FAIL (route `admin.events.gallery-photos.store` doesn't exist yet)

- [ ] **Step 7: Create the FormRequest**

Create `app/Http/Requests/Admin/GalleryPhotoRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GalleryPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image_path' => ['required', 'string', 'max:500'],
            'caption_ar' => ['nullable', 'string', 'max:255'],
            'caption_en' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

- [ ] **Step 8: Create the admin controller**

Create `app/Http/Controllers/Admin/GalleryPhotoController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryPhotoRequest;
use App\Models\Event;
use App\Models\GalleryPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GalleryPhotoController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.gallery-photos.index', ['event' => $event, 'galleryPhotos' => $event->galleryPhotos]);
    }

    public function create(Event $event): View
    {
        return view('admin.gallery-photos.form', ['event' => $event, 'galleryPhoto' => new GalleryPhoto]);
    }

    public function store(GalleryPhotoRequest $request, Event $event): RedirectResponse
    {
        $event->galleryPhotos()->create($request->validated());

        return redirect()->route('admin.events.gallery-photos.index', $event);
    }

    public function edit(Event $event, GalleryPhoto $galleryPhoto): View
    {
        $this->assertBelongsToEvent($event, $galleryPhoto);

        return view('admin.gallery-photos.form', ['event' => $event, 'galleryPhoto' => $galleryPhoto]);
    }

    public function update(GalleryPhotoRequest $request, Event $event, GalleryPhoto $galleryPhoto): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $galleryPhoto);
        $galleryPhoto->update($request->validated());

        return redirect()->route('admin.events.gallery-photos.index', $event);
    }

    public function destroy(Event $event, GalleryPhoto $galleryPhoto): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $galleryPhoto);
        $galleryPhoto->delete();

        return redirect()->route('admin.events.gallery-photos.index', $event);
    }

    private function assertBelongsToEvent(Event $event, GalleryPhoto $galleryPhoto): void
    {
        if ($galleryPhoto->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
```

- [ ] **Step 9: Create the admin views**

Create `resources/views/admin/gallery-photos/index.blade.php`:

```blade
{{-- resources/views/admin/gallery-photos/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Gallery Photos').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.gallery-photos.create', $event) }}">{{ __('New Photo') }}</x-admin.button>
    </x-admin.page-header>

    @if($galleryPhotos->isEmpty())
        <x-admin.empty-state :message="__('No gallery photos yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Photo') }}</th>
                    <th class="py-2 px-3">{{ __('Caption') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($galleryPhotos as $photo)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3"><img src="{{ $photo->image_path }}" class="h-12 w-12 object-cover rounded" alt=""></td>
                        <td class="py-2 px-3">{{ $photo->caption_en }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.gallery-photos.edit', [$event, $photo]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.gallery-photos.destroy', [$event, $photo]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
                                @csrf @method('DELETE')
                                <x-admin.button type="submit" variant="danger" class="ml-2">{{ __('Delete') }}</x-admin.button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @endif
@endsection
```

Create `resources/views/admin/gallery-photos/form.blade.php`:

```blade
{{-- resources/views/admin/gallery-photos/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$galleryPhoto->exists ? __('Edit Photo') : __('New Photo')" />

    <form method="POST" action="{{ $galleryPhoto->exists ? route('admin.events.gallery-photos.update', [$event, $galleryPhoto]) : route('admin.events.gallery-photos.store', $event) }}">
        @csrf
        @if($galleryPhoto->exists) @method('PUT') @endif

        <x-admin.field name="image_path" label="{{ __('Image Path / URL') }}" :value="old('image_path', $galleryPhoto->image_path)" required />
        <x-admin.bilingual-field name="caption" label="{{ __('Caption') }}" :value-ar="old('caption_ar', $galleryPhoto->caption_ar)" :value-en="old('caption_en', $galleryPhoto->caption_en)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $galleryPhoto->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 10: Add the sidebar nav entry**

In `resources/views/admin/partials/sidebar.blade.php`, add to the `$eventNavItems` array, after the `agenda-items` entry:

```php
                        ['prefix' => 'admin.events.gallery-photos', 'route' => 'admin.events.gallery-photos.index', 'label' => __('Gallery')],
```

- [ ] **Step 11: Register the routes**

In `routes/web.php`, add the import `use App\Http\Controllers\Admin\GalleryPhotoController;` (alphabetical order with the other `Admin\` imports), and add inside the authenticated admin group, after the `events.agenda-items` resource:

```php
        Route::resource('events.gallery-photos', GalleryPhotoController::class)
            ->except('show')
            ->parameters(['gallery-photos' => 'galleryPhoto']);
```

- [ ] **Step 12: Run the admin CRUD test to verify it passes**

Run: `php artisan test --compact --filter=GalleryPhotoCrudTest`
Expected: PASS (5/5)

- [ ] **Step 13: Write the failing public section test**

Add to `tests/Feature/LandingPageTest.php`:

```php
    public function test_gallery_section_lists_photos(): void
    {
        $event = Event::factory()->create();
        GalleryPhoto::factory()->for($event)->create(['caption_en' => 'Opening night crowd']);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('id="gallery"', false);
        $response->assertSee('Opening night crowd');
    }

    public function test_gallery_section_omitted_when_no_photos(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertDontSee('id="gallery"', false);
    }
```

Add `use App\Models\GalleryPhoto;` to the imports at the top of the file.

- [ ] **Step 14: Run test to verify it fails**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: FAIL (`gallery.blade.php` doesn't exist)

- [ ] **Step 15: Create the public partial**

Create `resources/views/landing/partials/gallery.blade.php`:

```blade
{{-- resources/views/landing/partials/gallery.blade.php --}}
@if($event->galleryPhotos->isNotEmpty())
    <section id="gallery" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-coral">{{ __('Gallery') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-10">{{ __('Last year, in frames.') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($event->galleryPhotos as $photo)
                <div class="aspect-square rounded-xl overflow-hidden border border-white/10 bg-white/5">
                    <img src="{{ $photo->image_path }}" alt="{{ app()->getLocale() === 'ar' ? $photo->caption_ar : $photo->caption_en }}" class="w-full h-full object-cover">
                </div>
            @endforeach
        </div>
    </section>
@endif
```

- [ ] **Step 16: Insert the include and eager-load the relation**

In `resources/views/landing/show.blade.php`, insert a new line between the `awards-teaser` and `partners` includes:

```blade
    @include('landing.partials.awards-teaser', ['event' => $event])
    @include('landing.partials.gallery', ['event' => $event])
    @include('landing.partials.partners', ['event' => $event])
```

In `app/Http/Controllers/LandingPageController.php`, add `'galleryPhotos'` to the `$event->load([...])` array:

```php
        $event->load([
            'speakers', 'sponsors', 'ticketTypes', 'workshops',
            'agendaItems', 'faqs', 'landingPageContent', 'galleryPhotos',
        ]);
```

- [ ] **Step 17: Run tests to verify they pass**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS

- [ ] **Step 18: Run the full suite**

Run: `php artisan test --compact`
Expected: 137 passed

- [ ] **Step 19: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations/2026_07_23_100000_create_gallery_photos_table.php app/Models/GalleryPhoto.php database/factories/GalleryPhotoFactory.php app/Models/Event.php app/Http/Requests/Admin/GalleryPhotoRequest.php app/Http/Controllers/Admin/GalleryPhotoController.php resources/views/admin/gallery-photos resources/views/admin/partials/sidebar.blade.php routes/web.php resources/views/landing/partials/gallery.blade.php resources/views/landing/show.blade.php app/Http/Controllers/LandingPageController.php tests/Feature/Admin/GalleryPhotoCrudTest.php tests/Feature/LandingPageTest.php
git commit -m "feat: add Gallery Photos with admin CRUD and public grid section"
```

---

## Task 11: Testimonials (new — full vertical slice)

**Files:**
- Create: `database/migrations/2026_07_23_100001_create_testimonials_table.php`
- Create: `app/Models/Testimonial.php`
- Create: `database/factories/TestimonialFactory.php`
- Modify: `app/Models/Event.php`
- Create: `app/Http/Requests/Admin/TestimonialRequest.php`
- Create: `app/Http/Controllers/Admin/TestimonialController.php`
- Create: `resources/views/admin/testimonials/index.blade.php`
- Create: `resources/views/admin/testimonials/form.blade.php`
- Modify: `resources/views/admin/partials/sidebar.blade.php`
- Modify: `routes/web.php`
- Create: `resources/views/landing/partials/testimonials.blade.php`
- Modify: `resources/views/landing/show.blade.php`
- Modify: `app/Http/Controllers/LandingPageController.php`
- Test: Create `tests/Feature/Admin/TestimonialCrudTest.php`
- Test: Modify `tests/Feature/LandingPageTest.php`

**Interfaces:**
- Produces: `Event::testimonials()` (`HasMany<Testimonial>`, ordered by `sort_order`) — consumed by the public `testimonials.blade.php` partial and Task 14's integration test.
- Must run after Task 10 — this task's route/nav/section insertions are positioned relative to Task 10's Gallery entries.

- [ ] **Step 1: Create the migration**

Create `database/migrations/2026_07_23_100001_create_testimonials_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->text('quote_ar');
            $table->text('quote_en');
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('title_ar');
            $table->string('title_en');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
```

- [ ] **Step 2: Create the model**

Create `app/Models/Testimonial.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'quote_ar', 'quote_en', 'name_ar', 'name_en', 'title_ar', 'title_en', 'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): TestimonialFactory
    {
        return TestimonialFactory::new();
    }
}
```

- [ ] **Step 3: Create the factory**

Create `database/factories/TestimonialFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'quote_ar' => $this->faker->paragraph(),
            'quote_en' => $this->faker->paragraph(),
            'name_ar' => $this->faker->name(),
            'name_en' => $this->faker->name(),
            'title_ar' => $this->faker->jobTitle(),
            'title_en' => $this->faker->jobTitle(),
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 4: Add the relation to Event**

In `app/Models/Event.php`, add after the `galleryPhotos()` method:

```php
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->orderBy('sort_order');
    }
```

- [ ] **Step 5: Write the failing admin CRUD test**

Create `tests/Feature/Admin/TestimonialCrudTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_testimonial(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.testimonials.store', $event), [
            'quote_ar' => 'حدث رائع', 'quote_en' => 'A great event',
            'name_ar' => 'سارة', 'name_en' => 'Sarah',
            'title_ar' => 'مؤسسة', 'title_en' => 'Founder', 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.testimonials.index', $event));
        $this->assertDatabaseHas('testimonials', ['event_id' => $event->id, 'quote_en' => 'A great event']);
    }

    public function test_creating_a_testimonial_requires_bilingual_quote(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.testimonials.store', $event), [
            'quote_ar' => '', 'quote_en' => '', 'name_ar' => 'سارة', 'name_en' => 'Sarah', 'title_ar' => 'مؤسسة', 'title_en' => 'Founder',
        ]);

        $response->assertSessionHasErrors(['quote_ar', 'quote_en']);
    }

    public function test_admin_can_delete_a_testimonial(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $testimonial = Testimonial::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.testimonials.destroy', [$event, $testimonial]));

        $response->assertRedirect(route('admin.events.testimonials.index', $event));
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }

    public function test_admin_can_view_the_index_page_with_records(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        Testimonial::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.testimonials.index', $event));

        $response->assertOk();
    }

    public function test_admin_can_view_the_edit_page(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $testimonial = Testimonial::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.testimonials.edit', [$event, $testimonial]));

        $response->assertOk();
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `php artisan test --compact --filter=TestimonialCrudTest`
Expected: FAIL (route doesn't exist yet)

- [ ] **Step 7: Create the FormRequest**

Create `app/Http/Requests/Admin/TestimonialRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quote_ar' => ['required', 'string'],
            'quote_en' => ['required', 'string'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

- [ ] **Step 8: Create the admin controller**

Create `app/Http/Controllers/Admin/TestimonialController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Event;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TestimonialController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.testimonials.index', ['event' => $event, 'testimonials' => $event->testimonials]);
    }

    public function create(Event $event): View
    {
        return view('admin.testimonials.form', ['event' => $event, 'testimonial' => new Testimonial]);
    }

    public function store(TestimonialRequest $request, Event $event): RedirectResponse
    {
        $event->testimonials()->create($request->validated());

        return redirect()->route('admin.events.testimonials.index', $event);
    }

    public function edit(Event $event, Testimonial $testimonial): View
    {
        $this->assertBelongsToEvent($event, $testimonial);

        return view('admin.testimonials.form', ['event' => $event, 'testimonial' => $testimonial]);
    }

    public function update(TestimonialRequest $request, Event $event, Testimonial $testimonial): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $testimonial);
        $testimonial->update($request->validated());

        return redirect()->route('admin.events.testimonials.index', $event);
    }

    public function destroy(Event $event, Testimonial $testimonial): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $testimonial);
        $testimonial->delete();

        return redirect()->route('admin.events.testimonials.index', $event);
    }

    private function assertBelongsToEvent(Event $event, Testimonial $testimonial): void
    {
        if ($testimonial->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
```

- [ ] **Step 9: Create the admin views**

Create `resources/views/admin/testimonials/index.blade.php`:

```blade
{{-- resources/views/admin/testimonials/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Testimonials').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.testimonials.create', $event) }}">{{ __('New Testimonial') }}</x-admin.button>
    </x-admin.page-header>

    @if($testimonials->isEmpty())
        <x-admin.empty-state :message="__('No testimonials yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Name') }}</th>
                    <th class="py-2 px-3">{{ __('Quote') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($testimonials as $testimonial)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $testimonial->name_en }}</td>
                        <td class="py-2 px-3">{{ \Illuminate\Support\Str::limit($testimonial->quote_en, 60) }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.testimonials.edit', [$event, $testimonial]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.testimonials.destroy', [$event, $testimonial]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
                                @csrf @method('DELETE')
                                <x-admin.button type="submit" variant="danger" class="ml-2">{{ __('Delete') }}</x-admin.button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @endif
@endsection
```

Create `resources/views/admin/testimonials/form.blade.php`:

```blade
{{-- resources/views/admin/testimonials/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$testimonial->exists ? __('Edit Testimonial') : __('New Testimonial')" />

    <form method="POST" action="{{ $testimonial->exists ? route('admin.events.testimonials.update', [$event, $testimonial]) : route('admin.events.testimonials.store', $event) }}">
        @csrf
        @if($testimonial->exists) @method('PUT') @endif

        <x-admin.bilingual-field type="textarea" name="quote" label="{{ __('Quote') }}" :value-ar="old('quote_ar', $testimonial->quote_ar)" :value-en="old('quote_en', $testimonial->quote_en)" />
        <x-admin.bilingual-field name="name" label="{{ __('Name') }}" :value-ar="old('name_ar', $testimonial->name_ar)" :value-en="old('name_en', $testimonial->name_en)" />
        <x-admin.bilingual-field name="title" label="{{ __('Title') }}" :value-ar="old('title_ar', $testimonial->title_ar)" :value-en="old('title_en', $testimonial->title_en)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $testimonial->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 10: Add the sidebar nav entry**

In `resources/views/admin/partials/sidebar.blade.php`, add to `$eventNavItems`, after the `gallery-photos` entry:

```php
                        ['prefix' => 'admin.events.testimonials', 'route' => 'admin.events.testimonials.index', 'label' => __('Testimonials')],
```

- [ ] **Step 11: Register the routes**

In `routes/web.php`, add the import `use App\Http\Controllers\Admin\TestimonialController;`, and add inside the authenticated admin group, after the `events.gallery-photos` resource:

```php
        Route::resource('events.testimonials', TestimonialController::class)->except('show');
```

- [ ] **Step 12: Run the admin CRUD test to verify it passes**

Run: `php artisan test --compact --filter=TestimonialCrudTest`
Expected: PASS (5/5)

- [ ] **Step 13: Write the failing public section test**

Add to `tests/Feature/LandingPageTest.php`:

```php
    public function test_testimonials_section_lists_quotes(): void
    {
        $event = Event::factory()->create();
        Testimonial::factory()->for($event)->create(['quote_en' => 'The best conference all year.']);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('id="testimonials"', false);
        $response->assertSee('The best conference all year.');
    }

    public function test_testimonials_section_omitted_when_none_exist(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertDontSee('id="testimonials"', false);
    }
```

Add `use App\Models\Testimonial;` to the imports.

- [ ] **Step 14: Run test to verify it fails**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: FAIL

- [ ] **Step 15: Create the public partial**

Create `resources/views/landing/partials/testimonials.blade.php`:

```blade
{{-- resources/views/landing/partials/testimonials.blade.php --}}
@if($event->testimonials->isNotEmpty())
    <section id="testimonials" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-gold">{{ __('Testimonials') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12">{{ __('What past attendees say.') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-7">
            @foreach($event->testimonials as $testimonial)
                <div class="bg-white/5 border border-white/10 rounded-2xl p-8 flex flex-col gap-6">
                    <p class="text-lg leading-relaxed font-medium">&ldquo;{{ app()->getLocale() === 'ar' ? $testimonial->quote_ar : $testimonial->quote_en }}&rdquo;</p>
                    <div>
                        <div class="font-bold text-sm">{{ app()->getLocale() === 'ar' ? $testimonial->name_ar : $testimonial->name_en }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ app()->getLocale() === 'ar' ? $testimonial->title_ar : $testimonial->title_en }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
```

- [ ] **Step 16: Insert the include and eager-load the relation**

In `resources/views/landing/show.blade.php`, insert a new line between the `gallery` and `partners` includes:

```blade
    @include('landing.partials.gallery', ['event' => $event])
    @include('landing.partials.testimonials', ['event' => $event])
    @include('landing.partials.partners', ['event' => $event])
```

In `app/Http/Controllers/LandingPageController.php`, add `'testimonials'` to the `$event->load([...])` array:

```php
        $event->load([
            'speakers', 'sponsors', 'ticketTypes', 'workshops',
            'agendaItems', 'faqs', 'landingPageContent', 'galleryPhotos', 'testimonials',
        ]);
```

- [ ] **Step 17: Run tests to verify they pass**

Run: `php artisan test --compact --filter=LandingPageTest`
Expected: PASS

- [ ] **Step 18: Run the full suite**

Run: `php artisan test --compact`
Expected: 144 passed

- [ ] **Step 19: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations/2026_07_23_100001_create_testimonials_table.php app/Models/Testimonial.php database/factories/TestimonialFactory.php app/Models/Event.php app/Http/Requests/Admin/TestimonialRequest.php app/Http/Controllers/Admin/TestimonialController.php resources/views/admin/testimonials resources/views/admin/partials/sidebar.blade.php routes/web.php resources/views/landing/partials/testimonials.blade.php resources/views/landing/show.blade.php app/Http/Controllers/LandingPageController.php tests/Feature/Admin/TestimonialCrudTest.php tests/Feature/LandingPageTest.php
git commit -m "feat: add Testimonials with admin CRUD and public quote grid section"
```

---

## Task 12: Contact form (new — full vertical slice)

**Files:**
- Create: `database/migrations/2026_07_23_100002_create_contact_messages_table.php`
- Create: `app/Models/ContactMessage.php`
- Create: `database/factories/ContactMessageFactory.php`
- Modify: `app/Models/Event.php`
- Create: `app/Http/Requests/ContactMessageRequest.php`
- Create: `app/Http/Controllers/ContactMessageController.php`
- Create: `app/Http/Controllers/Admin/ContactMessageController.php`
- Create: `resources/views/admin/contact-messages/index.blade.php`
- Modify: `resources/views/admin/partials/sidebar.blade.php`
- Modify: `routes/web.php`
- Create: `resources/views/landing/partials/contact.blade.php`
- Modify: `resources/views/landing/show.blade.php`
- Test: Create `tests/Feature/ContactMessageTest.php`
- Test: Create `tests/Feature/Admin/ContactMessageIndexTest.php`

**Interfaces:**
- Produces: `Event::contactMessages()` (`HasMany<ContactMessage>`, latest-first).
- The public and admin controllers share the class name `ContactMessageController` in different namespaces — `routes/web.php` imports the admin one aliased as `AdminContactMessageController`, matching the existing `WorkshopController`/`AdminWorkshopController` pattern already in that file.

- [ ] **Step 1: Create the migration**

Create `database/migrations/2026_07_23_100002_create_contact_messages_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
```

- [ ] **Step 2: Create the model**

Create `app/Models/ContactMessage.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'name', 'email', 'message'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): ContactMessageFactory
    {
        return ContactMessageFactory::new();
    }
}
```

- [ ] **Step 3: Create the factory**

Create `database/factories/ContactMessageFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContactMessage;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'message' => $this->faker->paragraph(),
        ];
    }
}
```

- [ ] **Step 4: Add the relation to Event**

In `app/Models/Event.php`, add after the `testimonials()` method:

```php
    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class)->latest();
    }
```

- [ ] **Step 5: Write the failing public form test**

Create `tests/Feature/ContactMessageTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_the_contact_form(): void
    {
        $event = Event::factory()->create();

        $response = $this->post(route('contact.store', $event), [
            'name' => 'Jane Creator', 'email' => 'jane@example.com', 'message' => 'What time do doors open?',
        ]);

        $response->assertRedirect(route('landing.show', $event).'#contact');
        $this->assertDatabaseHas('contact_messages', [
            'event_id' => $event->id, 'name' => 'Jane Creator', 'email' => 'jane@example.com',
        ]);
    }

    public function test_contact_form_requires_a_valid_email(): void
    {
        $event = Event::factory()->create();

        $response = $this->post(route('contact.store', $event), [
            'name' => 'Jane Creator', 'email' => 'not-an-email', 'message' => 'Hello',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_section_renders_the_form(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('id="contact"', false);
        $response->assertSee('action="'.route('contact.store', $event).'"', false);
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `php artisan test --compact --filter=ContactMessageTest`
Expected: FAIL (route `contact.store` doesn't exist)

- [ ] **Step 7: Create the public FormRequest**

Create `app/Http/Requests/ContactMessageRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}
```

- [ ] **Step 8: Create the public controller**

Create `app/Http/Controllers/ContactMessageController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class ContactMessageController extends Controller
{
    public function store(ContactMessageRequest $request, Event $event): RedirectResponse
    {
        $event->contactMessages()->create($request->validated());

        return redirect(route('landing.show', $event).'#contact')->with('contact_success', true);
    }
}
```

- [ ] **Step 9: Create the admin controller**

Create `app/Http/Controllers/Admin/ContactMessageController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.contact-messages.index', ['event' => $event, 'contactMessages' => $event->contactMessages]);
    }
}
```

- [ ] **Step 10: Create the admin index view**

Create `resources/views/admin/contact-messages/index.blade.php`:

```blade
{{-- resources/views/admin/contact-messages/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Contact Messages').' — '.$event->name_en" />

    @if($contactMessages->isEmpty())
        <x-admin.empty-state :message="__('No messages yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Name') }}</th>
                    <th class="py-2 px-3">{{ __('Email') }}</th>
                    <th class="py-2 px-3">{{ __('Message') }}</th>
                    <th class="py-2 px-3">{{ __('Received') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contactMessages as $message)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $message->name }}</td>
                        <td class="py-2 px-3">{{ $message->email }}</td>
                        <td class="py-2 px-3">{{ \Illuminate\Support\Str::limit($message->message, 80) }}</td>
                        <td class="py-2 px-3">{{ $message->created_at->format('M j, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @endif
@endsection
```

- [ ] **Step 11: Add the sidebar nav entry**

In `resources/views/admin/partials/sidebar.blade.php`, add to `$eventNavItems`, after the `testimonials` entry:

```php
                        ['prefix' => 'admin.events.contact-messages', 'route' => 'admin.events.contact-messages.index', 'label' => __('Contact Messages')],
```

- [ ] **Step 12: Register the routes**

In `routes/web.php`:

- Add imports: `use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;` and `use App\Http\Controllers\ContactMessageController;` (alphabetical within their respective groups, matching the existing `WorkshopController`/`AdminWorkshopController` alias pattern).
- Inside the public `events/{event}` group, add after the `ticket-requests.create` route:

```php
    Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact.store');
```

- Inside the authenticated admin group, add after the `events.testimonials` resource:

```php
        Route::get('events/{event}/contact-messages', [AdminContactMessageController::class, 'index'])->name('events.contact-messages.index');
```

- [ ] **Step 13: Run the public form test to verify it passes**

Run: `php artisan test --compact --filter=ContactMessageTest`
Expected: FAIL still on `test_contact_section_renders_the_form` (section doesn't exist yet) — the other 2 should pass once the route exists. Continue to the next step before re-checking.

- [ ] **Step 14: Create the public partial**

Create `resources/views/landing/partials/contact.blade.php`:

```blade
{{-- resources/views/landing/partials/contact.blade.php --}}
<section id="contact" class="ccs-section grid grid-cols-1 lg:grid-cols-2 gap-16">
    <div>
        <div class="ccs-eyebrow text-ccs-coral">{{ __('Contact') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-8">{{ __('Questions before you request?') }}</h2>
    </div>
    <form method="POST" action="{{ route('contact.store', $event) }}" class="flex flex-col gap-4">
        @csrf
        @if(session('contact_success'))
            <p class="text-sm font-bold text-ccs-teal-light">{{ __("Thanks — we'll be in touch soon.") }}</p>
        @endif
        <div>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('Name') }}" class="w-full px-4 py-4 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500">
            @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}" class="w-full px-4 py-4 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500">
            @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <textarea name="message" rows="4" placeholder="{{ __('Message') }}" class="w-full px-4 py-4 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500">{{ old('message') }}</textarea>
            @error('message') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="px-6 py-4 rounded-lg bg-gradient-to-br from-ccs-red to-ccs-maroon font-bold">{{ __('Send Message') }}</button>
    </form>
</section>
```

- [ ] **Step 15: Insert the include**

In `resources/views/landing/show.blade.php`, insert a new line between the `location` include and the `footer` include (Task 2 placed `footer` last, so `contact` goes immediately before it):

```blade
    @include('landing.partials.location', ['event' => $event])
    @include('landing.partials.contact', ['event' => $event])
    @include('landing.partials.footer', ['event' => $event])
```

- [ ] **Step 16: Run tests to verify they pass**

Run: `php artisan test --compact --filter=ContactMessageTest`
Expected: PASS (3/3)

- [ ] **Step 17: Write and run the admin index test**

Create `tests/Feature/Admin/ContactMessageIndexTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_submitted_messages(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        ContactMessage::factory()->for($event)->create(['name' => 'Jane Creator']);

        $response = $this->actingAs($admin)->get(route('admin.events.contact-messages.index', $event));

        $response->assertOk();
        $response->assertSee('Jane Creator');
    }

    public function test_admin_index_shows_empty_state_with_no_messages(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.events.contact-messages.index', $event));

        $response->assertOk();
        $response->assertSee('No messages yet.');
    }
}
```

Run: `php artisan test --compact --filter=ContactMessageIndexTest`
Expected: PASS (2/2)

- [ ] **Step 18: Run the full suite**

Run: `php artisan test --compact`
Expected: 149 passed

- [ ] **Step 19: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations/2026_07_23_100002_create_contact_messages_table.php app/Models/ContactMessage.php database/factories/ContactMessageFactory.php app/Models/Event.php app/Http/Requests/ContactMessageRequest.php app/Http/Controllers/ContactMessageController.php app/Http/Controllers/Admin/ContactMessageController.php resources/views/admin/contact-messages resources/views/admin/partials/sidebar.blade.php routes/web.php resources/views/landing/partials/contact.blade.php resources/views/landing/show.blade.php tests/Feature/ContactMessageTest.php tests/Feature/Admin/ContactMessageIndexTest.php
git commit -m "feat: add Contact form with storage and admin message list"
```

---

## Task 13: Newsletter signup (new — full vertical slice)

**Files:**
- Create: `database/migrations/2026_07_23_100003_create_newsletter_subscribers_table.php`
- Create: `app/Models/NewsletterSubscriber.php`
- Create: `database/factories/NewsletterSubscriberFactory.php`
- Modify: `app/Models/Event.php`
- Create: `app/Http/Requests/NewsletterSubscriberRequest.php`
- Create: `app/Http/Controllers/NewsletterSubscriberController.php`
- Create: `app/Http/Controllers/Admin/NewsletterSubscriberController.php`
- Create: `resources/views/admin/newsletter-subscribers/index.blade.php`
- Modify: `resources/views/admin/partials/sidebar.blade.php`
- Modify: `routes/web.php`
- Create: `resources/views/landing/partials/newsletter.blade.php`
- Modify: `resources/views/landing/show.blade.php`
- Test: Create `tests/Feature/NewsletterSubscriptionTest.php`
- Test: Create `tests/Feature/Admin/NewsletterSubscriberIndexTest.php`

**Interfaces:**
- Produces: `Event::newsletterSubscribers()` (`HasMany<NewsletterSubscriber>`, latest-first).
- Must run after Task 12 — inserts its section include immediately after `contact`, before `footer`.
- Duplicate signups are handled via `firstOrCreate` in the controller (idempotent), not a uniqueness validation rule — a repeat email must not produce a validation error.

- [ ] **Step 1: Create the migration**

Create `database/migrations/2026_07_23_100003_create_newsletter_subscribers_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->timestamps();
            $table->unique(['event_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
```

- [ ] **Step 2: Create the model**

Create `app/Models/NewsletterSubscriber.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NewsletterSubscriberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'email'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): NewsletterSubscriberFactory
    {
        return NewsletterSubscriberFactory::new();
    }
}
```

- [ ] **Step 3: Create the factory**

Create `database/factories/NewsletterSubscriberFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsletterSubscriberFactory extends Factory
{
    protected $model = NewsletterSubscriber::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
```

- [ ] **Step 4: Add the relation to Event**

In `app/Models/Event.php`, add after the `contactMessages()` method:

```php
    public function newsletterSubscribers(): HasMany
    {
        return $this->hasMany(NewsletterSubscriber::class)->latest();
    }
```

- [ ] **Step 5: Write the failing public form test**

Create `tests/Feature/NewsletterSubscriptionTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_subscribe(): void
    {
        $event = Event::factory()->create();

        $response = $this->post(route('newsletter.store', $event), ['email' => 'fan@example.com']);

        $response->assertRedirect(route('landing.show', $event).'#newsletter');
        $this->assertDatabaseHas('newsletter_subscribers', ['event_id' => $event->id, 'email' => 'fan@example.com']);
    }

    public function test_subscribing_requires_a_valid_email(): void
    {
        $event = Event::factory()->create();

        $response = $this->post(route('newsletter.store', $event), ['email' => 'not-an-email']);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_duplicate_subscription_is_idempotent_not_an_error(): void
    {
        $event = Event::factory()->create();
        NewsletterSubscriber::factory()->for($event)->create(['email' => 'fan@example.com']);

        $response = $this->post(route('newsletter.store', $event), ['email' => 'fan@example.com']);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_newsletter_section_renders_the_form(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertSee('id="newsletter"', false);
        $response->assertSee('action="'.route('newsletter.store', $event).'"', false);
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `php artisan test --compact --filter=NewsletterSubscriptionTest`
Expected: FAIL (route `newsletter.store` doesn't exist)

- [ ] **Step 7: Create the public FormRequest**

Create `app/Http/Requests/NewsletterSubscriberRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsletterSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
```

- [ ] **Step 8: Create the public controller**

Create `app/Http/Controllers/NewsletterSubscriberController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscriberRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriberController extends Controller
{
    public function store(NewsletterSubscriberRequest $request, Event $event): RedirectResponse
    {
        $event->newsletterSubscribers()->firstOrCreate(['email' => $request->validated('email')]);

        return redirect(route('landing.show', $event).'#newsletter')->with('newsletter_success', true);
    }
}
```

- [ ] **Step 9: Create the admin controller**

Create `app/Http/Controllers/Admin/NewsletterSubscriberController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class NewsletterSubscriberController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.newsletter-subscribers.index', ['event' => $event, 'newsletterSubscribers' => $event->newsletterSubscribers]);
    }
}
```

- [ ] **Step 10: Create the admin index view**

Create `resources/views/admin/newsletter-subscribers/index.blade.php`:

```blade
{{-- resources/views/admin/newsletter-subscribers/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Newsletter Subscribers').' — '.$event->name_en" />

    @if($newsletterSubscribers->isEmpty())
        <x-admin.empty-state :message="__('No subscribers yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Email') }}</th>
                    <th class="py-2 px-3">{{ __('Subscribed') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($newsletterSubscribers as $subscriber)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $subscriber->email }}</td>
                        <td class="py-2 px-3">{{ $subscriber->created_at->format('M j, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @endif
@endsection
```

- [ ] **Step 11: Add the sidebar nav entry**

In `resources/views/admin/partials/sidebar.blade.php`, add to `$eventNavItems`, after the `contact-messages` entry:

```php
                        ['prefix' => 'admin.events.newsletter-subscribers', 'route' => 'admin.events.newsletter-subscribers.index', 'label' => __('Newsletter')],
```

- [ ] **Step 12: Register the routes**

In `routes/web.php`:

- Add imports: `use App\Http\Controllers\Admin\NewsletterSubscriberController as AdminNewsletterSubscriberController;` and `use App\Http\Controllers\NewsletterSubscriberController;`.
- Inside the public `events/{event}` group, add after the `contact.store` route:

```php
    Route::post('/newsletter', [NewsletterSubscriberController::class, 'store'])->name('newsletter.store');
```

- Inside the authenticated admin group, add after the `events.contact-messages.index` route:

```php
        Route::get('events/{event}/newsletter-subscribers', [AdminNewsletterSubscriberController::class, 'index'])->name('events.newsletter-subscribers.index');
```

- [ ] **Step 13: Create the public partial**

Create `resources/views/landing/partials/newsletter.blade.php`:

```blade
{{-- resources/views/landing/partials/newsletter.blade.php --}}
<section id="newsletter" class="ccs-section text-center pt-24 pb-24 border-t border-b border-white/10" style="background: linear-gradient(160deg, var(--color-ccs-maroon), var(--color-ccs-black));">
    <h2 class="font-display text-2xl md:text-4xl font-extrabold mb-4">{{ __('Stay in the loop.') }}</h2>
    <p class="text-gray-400 mb-8">{{ __('Speaker announcements, agenda updates, and workshop drops — no spam.') }}</p>
    @if(session('newsletter_success'))
        <p class="text-sm font-bold text-ccs-teal-light mb-4">{{ __("You're subscribed — thanks!") }}</p>
    @endif
    <form method="POST" action="{{ route('newsletter.store', $event) }}" class="flex flex-wrap gap-3 justify-center max-w-md mx-auto">
        @csrf
        <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('you@company.com') }}" class="flex-1 min-w-[220px] px-4 py-4 bg-white/10 border border-white/10 rounded-lg text-white placeholder-gray-500">
        <button type="submit" class="px-7 py-4 rounded-lg bg-white text-ccs-black font-bold">{{ __('Subscribe') }}</button>
    </form>
    @error('email') <p class="text-red-300 text-sm mt-3">{{ $message }}</p> @enderror
</section>
```

- [ ] **Step 14: Insert the include**

In `resources/views/landing/show.blade.php`, insert a new line between the `contact` include and the `footer` include:

```blade
    @include('landing.partials.contact', ['event' => $event])
    @include('landing.partials.newsletter', ['event' => $event])
    @include('landing.partials.footer', ['event' => $event])
```

- [ ] **Step 15: Run tests to verify they pass**

Run: `php artisan test --compact --filter=NewsletterSubscriptionTest`
Expected: PASS (4/4)

- [ ] **Step 16: Write and run the admin index test**

Create `tests/Feature/Admin/NewsletterSubscriberIndexTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriberIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_subscribers(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        NewsletterSubscriber::factory()->for($event)->create(['email' => 'fan@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.events.newsletter-subscribers.index', $event));

        $response->assertOk();
        $response->assertSee('fan@example.com');
    }

    public function test_admin_index_shows_empty_state_with_no_subscribers(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.events.newsletter-subscribers.index', $event));

        $response->assertOk();
        $response->assertSee('No subscribers yet.');
    }
}
```

Run: `php artisan test --compact --filter=NewsletterSubscriberIndexTest`
Expected: PASS (2/2)

- [ ] **Step 17: Run the full suite**

Run: `php artisan test --compact`
Expected: 155 passed

- [ ] **Step 18: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations/2026_07_23_100003_create_newsletter_subscribers_table.php app/Models/NewsletterSubscriber.php database/factories/NewsletterSubscriberFactory.php app/Models/Event.php app/Http/Requests/NewsletterSubscriberRequest.php app/Http/Controllers/NewsletterSubscriberController.php app/Http/Controllers/Admin/NewsletterSubscriberController.php resources/views/admin/newsletter-subscribers resources/views/admin/partials/sidebar.blade.php routes/web.php resources/views/landing/partials/newsletter.blade.php resources/views/landing/show.blade.php tests/Feature/NewsletterSubscriptionTest.php tests/Feature/Admin/NewsletterSubscriberIndexTest.php
git commit -m "feat: add Newsletter signup with idempotent subscription and admin subscriber list"
```

---

## Task 14: Full-page integration test

**Files:**
- Test: Create `tests/Feature/LandingPageFullPageTest.php`

**Interfaces:**
- Consumes: every model and route from Tasks 1–13. This is a pure verification task — no production code changes.

- [ ] **Step 1: Write the test**

Create `tests/Feature/LandingPageFullPageTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\LandingPageSection;
use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\Faq;
use App\Models\GalleryPhoto;
use App\Models\LandingPageContent;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\Testimonial;
use App\Models\TicketType;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageFullPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_fully_populated_event_renders_every_section_in_order(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        Speaker::factory()->for($event)->create();
        Workshop::factory()->for($event)->create();
        AgendaItem::factory()->for($event)->create();
        TicketType::factory()->for($event)->create();
        Sponsor::factory()->for($event)->create();
        Faq::factory()->for($event)->create();
        Testimonial::factory()->for($event)->create();
        GalleryPhoto::factory()->for($event)->create();
        LandingPageContent::factory()->for($event)->create(['section' => LandingPageSection::About, 'field_key' => 'body']);
        LandingPageContent::factory()->for($event)->create(['section' => LandingPageSection::Location, 'field_key' => 'intro']);
        LandingPageContent::factory()->for($event)->create(['section' => LandingPageSection::Stats, 'field_key' => 'attendees_count', 'value_en' => '500+']);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertOk();
        $response->assertSeeInOrder([
            'id="hero"', 'id="about"', 'id="stats"', 'id="speakers"', 'id="workshops"',
            'id="agenda-teaser"', 'id="tickets"', 'id="awards"', 'id="gallery"',
            'id="testimonials"', 'id="partners"', 'id="faq"', 'id="location"',
            'id="contact"', 'id="newsletter"',
        ], false);
    }

    public function test_minimal_event_still_renders_ok(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $response = $this->get(route('landing.show', $event));

        $response->assertOk();
        $response->assertSee('id="hero"', false);
        $response->assertSee('id="awards"', false);
        $response->assertSee('id="contact"', false);
        $response->assertSee('id="newsletter"', false);
    }
}
```

- [ ] **Step 2: Run test to verify it passes**

Run: `php artisan test --compact --filter=LandingPageFullPageTest`
Expected: PASS (2/2) — if the order assertion fails, check that each prior task inserted its `@include` at the correct position in `resources/views/landing/show.blade.php` per the Global Constraints section order.

- [ ] **Step 3: Run the full suite**

Run: `php artisan test --compact`
Expected: 157 passed

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/LandingPageFullPageTest.php
git commit -m "test: add full-page integration test verifying all 15 sections render in order"
```

---

## Final Step: Whole-branch review and merge

Once all 14 tasks are complete, follow `superpowers:subagent-driven-development`'s closing steps: dispatch a final whole-branch code reviewer on the most capable available model, address any Critical/Important findings, then use `superpowers:finishing-a-development-branch` to merge.
