# Admin Panel UI/UX Redesign & Root Route Fix Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix the site root to redirect to the real landing page, and give the entire admin panel (login, dashboard, and 8 CRUD screens) a real, designed UI/UX using the CCS brand identity, instead of bare unstyled HTML.

**Architecture:** Presentation-layer only — no route (besides `/`), controller, validation, or migration changes to existing admin functionality. A new shared `layouts.admin` shell (sidebar + event-switcher) replaces `layouts.app` for authenticated admin pages. A small set of Blade components (`x-admin.field`, `x-admin.bilingual-field`, `x-admin.button`, `x-admin.table`, `x-admin.page-header`, `x-admin.empty-state`) replace hand-written HTML across all 17 admin view files. Login keeps `layouts.app` but gets the one bold brand moment (diagonal gradient, CSS-drawn CCS mark).

**Tech Stack:** Laravel 12, PHP 8.3, Tailwind CSS v4, Blade anonymous components, self-hosted `@fontsource` web fonts via Vite.

## Global Constraints

- Strict typing (`declare(strict_types=1);`) in all new/modified PHP files.
- Every existing form field's `name="..."` attribute must be preserved exactly (FormRequest validation binds to these names; changing one breaks existing tests silently).
- Every existing route name and HTTP verb used by a form's `action`/`method` must be preserved exactly.
- No new business logic, validation rules, or database changes anywhere in this plan.
- CCS palette tokens only (`ccs-coral/red/maroon/black/teal/teal-light/gold/gold-dark`, already in `resources/css/app.css`) — no new colors invented ad hoc.
- Bilingual: every `_ar` input keeps `dir="rtl"`; every `_en` input has no `dir` attribute (inherits `ltr`).
- The flag-motif accent (`.ccs-flag-accent`) appears in exactly three places: the sidebar active-nav-item indicator, `x-admin.page-header`, and the login screen's brand mark (Task 9's deliberate "bold moment" — see Visual system section). Nowhere else.
- Existing full test suite (89 tests as of the last merge) must stay green after every task.

---

### Task 1: Root route redirect

**Files:**
- Modify: `routes/web.php:21-23`
- Test: `tests/Feature/RootRedirectTest.php`

**Interfaces:**
- Consumes: `route('landing.show', ['event' => 'ccs-2026'])` (existing named route from the Landing Page spec).
- Produces: nothing new consumed by later tasks.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    public function test_root_redirects_to_ccs_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/events/ccs-2026');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=RootRedirectTest`
Expected: FAIL (currently returns 200 with the `welcome` view, not a redirect)

- [ ] **Step 3: Replace the root route**

```php
// routes/web.php — replace lines 21-23
Route::get('/', function () {
    return redirect('/events/ccs-2026');
});
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=RootRedirectTest`
Expected: PASS (1 passed)

- [ ] **Step 5: Commit**

```bash
git add routes/web.php tests/Feature/RootRedirectTest.php
git commit -m "feat: redirect root route to the CCS landing page"
```

---

### Task 2: Typography — self-hosted web fonts

**Files:**
- Modify: `package.json` (add devDependencies)
- Modify: `resources/css/app.css`
- Test: manual verification only (no automated test for font loading — see Step 5)

**Interfaces:**
- Produces: `--font-display` (Manrope), `--font-body` (Inter), and `:lang(ar)` override to Cairo — used inline via `font-family: 'Manrope', sans-serif;` etc. in every later task that sets a heading/display font.

- [ ] **Step 1: Install the font packages**

Run:
```bash
npm install --save-dev @fontsource/manrope @fontsource/inter @fontsource/cairo
```

- [ ] **Step 2: Import font weights and add lang-based font rules to the stylesheet**

```css
/* resources/css/app.css — add after the @import "tailwindcss"; line, before @theme */
@import "tailwindcss";

@import "@fontsource/manrope/700.css";
@import "@fontsource/manrope/800.css";
@import "@fontsource/inter/400.css";
@import "@fontsource/inter/500.css";
@import "@fontsource/cairo/400.css";
@import "@fontsource/cairo/700.css";

@theme {
  --color-ccs-coral: #ff7e71;
  --color-ccs-red: #d33333;
  --color-ccs-maroon: #430d14;
  --color-ccs-black: #171f22;
  --color-ccs-teal: #2a7675;
  --color-ccs-teal-light: #7ccbcf;
  --color-ccs-gold: #fad48b;
  --color-ccs-gold-dark: #a48755;
}

body {
  font-family: 'Inter', sans-serif;
}

:lang(ar) {
  font-family: 'Cairo', sans-serif;
}

h1, h2, h3, h4, h5, .font-display {
  font-family: 'Manrope', sans-serif;
}

:lang(ar) h1, :lang(ar) h2, :lang(ar) h3, :lang(ar) h4, :lang(ar) h5, :lang(ar) .font-display {
  font-family: 'Cairo', sans-serif;
}

[dir="rtl"] {
  text-align: right;
}

.ccs-hero {
  background: linear-gradient(135deg, var(--color-ccs-coral) 0%, var(--color-ccs-coral) 12%, var(--color-ccs-red) 13%, var(--color-ccs-maroon) 55%, var(--color-ccs-black) 100%);
}

.ccs-flag-accent {
  display: inline-block;
  clip-path: polygon(0 100%, 0 20%, 100% 0%, 100% 100%);
  background: linear-gradient(180deg, var(--color-ccs-coral), var(--color-ccs-red));
}
```

Note: `.font-display` utility class lets any element opt into the display face without being a real heading tag (used by the login screen's "CCS" wordmark, which is a `<span>`, not an `<h1>`).

- [ ] **Step 3: Build assets**

Run: `npm run build`
Expected: builds cleanly, no missing-module errors for `@fontsource/*`.

- [ ] **Step 4: Run the full test suite to confirm no regressions**

Run: `php artisan test`
Expected: all passing (89 + Task 1's new test)

- [ ] **Step 5: Manual verification**

Run `npm run dev`, visit any page, open browser devtools Network tab, confirm the Manrope/Inter/Cairo `.woff2` files load with 200 status (not 404).

- [ ] **Step 6: Commit**

```bash
git add package.json package-lock.json resources/css/app.css
git commit -m "feat: add self-hosted Manrope/Inter/Cairo typography"
```

---

### Task 3: Admin layout shell and sidebar

**Files:**
- Create: `resources/views/layouts/admin.blade.php`
- Create: `resources/views/admin/partials/sidebar.blade.php`
- Test: `tests/Feature/Admin/SidebarNavigationTest.php`

**Interfaces:**
- Consumes: `.ccs-flag-accent` CSS class (Task 2), CCS color tokens, all existing `admin.*` named routes.
- Produces: `@extends('layouts.admin')` — every admin page task from here on extends this instead of `layouts.app`. The layout yields `@section('content')` exactly like `layouts.app` does, so no calling convention changes for pages being migrated.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_sidebar_has_no_event_scoped_links(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertDontSee(__('Speakers'));
    }

    public function test_speakers_index_sidebar_shows_event_scoped_links(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.events.speakers.index', $event));

        $response->assertSee(__('Speakers'));
        $response->assertSee(__('Ticket Types'));
        $response->assertSee($event->name_en);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SidebarNavigationTest`
Expected: FAIL (route views don't exist yet / dashboard currently has no such content to assert against meaningfully — the second test fails because the current bare views don't render the event name or these labels via the sidebar)

- [ ] **Step 3: Create the sidebar partial**

```blade
{{-- resources/views/admin/partials/sidebar.blade.php --}}
<aside class="w-64 shrink-0 bg-ccs-black border-r border-gray-800 flex flex-col p-6">
    <div class="mb-8">
        <span class="font-display font-bold text-lg">{{ __('CCS Admin') }}</span>
    </div>

    <nav class="flex flex-col gap-1 flex-1">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900' : 'hover:bg-gray-900' }}">
            @if(request()->routeIs('admin.dashboard'))
                <span class="ccs-flag-accent" style="width:4px;height:16px;"></span>
            @endif
            {{ __('Dashboard') }}
        </a>
        <a href="{{ route('admin.events.index') }}" class="flex items-center gap-2 px-3 py-2 rounded {{ request()->routeIs('admin.events.*') ? 'bg-gray-900' : 'hover:bg-gray-900' }}">
            @if(request()->routeIs('admin.events.*'))
                <span class="ccs-flag-accent" style="width:4px;height:16px;"></span>
            @endif
            {{ __('Events') }}
        </a>

        @isset($event)
            <div class="mt-6 pt-6 border-t border-gray-800">
                <p class="text-xs uppercase tracking-wide text-gray-500 px-3 mb-2">{{ $event->name_en }}</p>
                @php
                    $eventNavItems = [
                        ['prefix' => 'admin.events.speakers', 'route' => 'admin.events.speakers.index', 'label' => __('Speakers')],
                        ['prefix' => 'admin.events.sponsors', 'route' => 'admin.events.sponsors.index', 'label' => __('Partners')],
                        ['prefix' => 'admin.events.ticket-types', 'route' => 'admin.events.ticket-types.index', 'label' => __('Ticket Types')],
                        ['prefix' => 'admin.events.workshops', 'route' => 'admin.events.workshops.index', 'label' => __('Workshops')],
                        ['prefix' => 'admin.events.agenda-items', 'route' => 'admin.events.agenda-items.index', 'label' => __('Agenda')],
                        ['prefix' => 'admin.events.faqs', 'route' => 'admin.events.faqs.index', 'label' => __('FAQs')],
                        ['prefix' => 'admin.events.content', 'route' => 'admin.events.content.edit', 'label' => __('Content')],
                    ];
                @endphp
                @foreach($eventNavItems as $item)
                    <a href="{{ route($item['route'], $event) }}" class="flex items-center gap-2 px-3 py-2 rounded {{ request()->routeIs($item['prefix'].'.*') ? 'bg-gray-900' : 'hover:bg-gray-900' }}">
                        @if(request()->routeIs($item['prefix'].'.*'))
                            <span class="ccs-flag-accent" style="width:4px;height:16px;"></span>
                        @endif
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        @endisset
    </nav>

    <form method="POST" action="{{ route('admin.logout') }}" class="mt-6 pt-6 border-t border-gray-800">
        @csrf
        <button type="submit" class="text-sm text-gray-400 hover:text-white">{{ __('Log out') }}</button>
    </form>
</aside>
```

- [ ] **Step 4: Create the admin layout**

```blade
{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('CCS Admin'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ccs-black text-white flex min-h-screen">
    @include('admin.partials.sidebar')
    <main class="flex-1 p-8 overflow-x-hidden">
        @yield('content')
    </main>
</body>
</html>
```

- [ ] **Step 5: Point the dashboard and one CRUD screen at the new layout to make the test pass**

Modify `resources/views/admin/dashboard.blade.php` line 2: change `@extends('layouts.app')` to `@extends('layouts.admin')`.

Modify `resources/views/admin/speakers/index.blade.php` line 2: change `@extends('layouts.app')` to `@extends('layouts.admin')`.

(The remaining 15 admin views switch to `layouts.admin` in their own redesign tasks below — this step only touches the two views the test in this task actually exercises.)

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=SidebarNavigationTest`
Expected: PASS (2 passed)

- [ ] **Step 7: Run the full test suite**

Run: `php artisan test`
Expected: all passing, no regressions

- [ ] **Step 8: Commit**

```bash
git add resources/views/layouts/admin.blade.php resources/views/admin/partials/sidebar.blade.php resources/views/admin/dashboard.blade.php resources/views/admin/speakers/index.blade.php tests/Feature/Admin/SidebarNavigationTest.php
git commit -m "feat: admin layout shell with sidebar navigation"
```

---

### Task 4: `x-admin.button` component

**Files:**
- Create: `resources/views/components/admin/button.blade.php`
- Test: `tests/Feature/Admin/ButtonComponentTest.php`

**Interfaces:**
- Produces: `<x-admin.button>` — usable as a submit button (default) or a link (pass `href`). Props: `href` (string|null), `type` (string, default `'submit'`), `variant` (`'primary'|'secondary'|'danger'`, default `'primary'`). Accepts arbitrary extra HTML attributes (e.g. `class="w-full"`) via Blade's attribute bag, merged on top of the variant's base classes.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ButtonComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_button_renders_as_link_when_href_given(): void
    {
        $html = Blade::render('<x-admin.button href="/somewhere">Go</x-admin.button>');

        $this->assertStringContainsString('<a href="/somewhere"', $html);
        $this->assertStringContainsString('Go', $html);
    }

    public function test_button_renders_as_submit_button_by_default(): void
    {
        $html = Blade::render('<x-admin.button>Save</x-admin.button>');

        $this->assertStringContainsString('<button type="submit"', $html);
        $this->assertStringContainsString('Save', $html);
    }
}
```

Add `use Illuminate\Support\Facades\Blade;` to the imports.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ButtonComponentTest`
Expected: FAIL (component doesn't exist — "Unable to locate a class or view for component [admin.button]")

- [ ] **Step 3: Create the component**

```blade
{{-- resources/views/components/admin/button.blade.php --}}
@props([
    'href' => null,
    'type' => 'submit',
    'variant' => 'primary',
])

@php
$variantClasses = match($variant) {
    'primary' => 'bg-ccs-red hover:bg-ccs-maroon text-white',
    'secondary' => 'border border-gray-600 text-white hover:bg-gray-900',
    'danger' => 'text-red-500 hover:underline',
    default => 'bg-ccs-red hover:bg-ccs-maroon text-white',
};
$baseClasses = $variant === 'danger' ? $variantClasses : "$variantClasses px-4 py-2 rounded";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses . ' inline-block']) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>{{ $slot }}</button>
@endif
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ButtonComponentTest`
Expected: PASS (2 passed)

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/admin/button.blade.php tests/Feature/Admin/ButtonComponentTest.php
git commit -m "feat: add x-admin.button component"
```

---

### Task 5: `x-admin.field` component

**Files:**
- Create: `resources/views/components/admin/field.blade.php`
- Test: `tests/Feature/Admin/FieldComponentTest.php`

**Interfaces:**
- Produces: `<x-admin.field>` — a single form field (label + input/textarea/select/checkbox + validation error). Props: `type` (default `'text'`), `name` (required), `label` (string|null), `value` (mixed|null), `dir` (`'rtl'|null`), `placeholder` (string|null), `checked` (bool, default `false`, checkbox only), `required` (bool, default `false`). For `type="select"`, the default slot holds the `<option>` elements (caller-provided, since option lists vary per field — static lists like status/tier, or dynamic loops like speaker dropdowns).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_text_field_renders_label_and_input(): void
    {
        $html = Blade::render('<x-admin.field name="name_en" label="Name (English)" value="Jane" />');

        $this->assertStringContainsString('for="name_en"', $html);
        $this->assertStringContainsString('Name (English)', $html);
        $this->assertStringContainsString('name="name_en"', $html);
        $this->assertStringContainsString('value="Jane"', $html);
    }

    public function test_rtl_field_gets_dir_attribute(): void
    {
        $html = Blade::render('<x-admin.field name="name_ar" dir="rtl" />');

        $this->assertStringContainsString('dir="rtl"', $html);
    }

    public function test_select_field_renders_slot_options(): void
    {
        $html = Blade::render('<x-admin.field type="select" name="status"><option value="draft">Draft</option></x-admin.field>');

        $this->assertStringContainsString('<select name="status"', $html);
        $this->assertStringContainsString('<option value="draft">Draft</option>', $html);
    }

    public function test_checkbox_field_renders_checked_state(): void
    {
        $html = Blade::render('<x-admin.field type="checkbox" name="is_active" label="Active" :checked="true" />');

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('checked', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=FieldComponentTest`
Expected: FAIL (component doesn't exist)

- [ ] **Step 3: Create the component**

```blade
{{-- resources/views/components/admin/field.blade.php --}}
@props([
    'type' => 'text',
    'name',
    'label' => null,
    'value' => null,
    'dir' => null,
    'placeholder' => null,
    'checked' => false,
    'required' => false,
])

@php $inputClasses = 'w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-ccs-red'; @endphp

@if($type === 'checkbox')
    <div class="flex items-center gap-2 mb-4">
        <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" class="rounded border-gray-600 bg-gray-900" @checked($checked)>
        @if($label)
            <label for="{{ $name }}" class="text-sm text-gray-300">{{ $label }}</label>
        @endif
    </div>
@else
    <div class="mb-4">
        @if($label)
            <label for="{{ $name }}" class="block text-sm text-gray-300 mb-1">{{ $label }}</label>
        @endif

        @if($type === 'textarea')
            <textarea name="{{ $name }}" id="{{ $name }}"
                @if($dir) dir="{{ $dir }}" @endif
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($required) required @endif
                class="{{ $inputClasses }}">{{ $value }}</textarea>
        @elseif($type === 'select')
            <select name="{{ $name }}" id="{{ $name }}" @if($required) required @endif class="{{ $inputClasses }}">
                {{ $slot }}
            </select>
        @else
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}"
                @if($dir) dir="{{ $dir }}" @endif
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($required) required @endif
                class="{{ $inputClasses }}">
        @endif

        @error($name)
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
@endif
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=FieldComponentTest`
Expected: PASS (4 passed)

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/admin/field.blade.php tests/Feature/Admin/FieldComponentTest.php
git commit -m "feat: add x-admin.field component"
```

---

### Task 6: `x-admin.bilingual-field` component

**Files:**
- Create: `resources/views/components/admin/bilingual-field.blade.php`
- Test: `tests/Feature/Admin/BilingualFieldComponentTest.php`

**Interfaces:**
- Consumes: `x-admin.field` (Task 5).
- Produces: `<x-admin.bilingual-field>` — renders an `_ar`/`_en` pair side by side in a responsive grid. Props: `type` (default `'text'`), `name` (base name, e.g. `"name"` renders `name_ar`/`name_en`), `label` (base label, e.g. `"Name"` renders `"Name (Arabic)"`/`"Name (English)"`), `valueAr` (mixed|null), `valueEn` (mixed|null).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BilingualFieldComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_both_ar_and_en_inputs(): void
    {
        $html = Blade::render(
            '<x-admin.bilingual-field name="name" label="Name" :value-ar="$ar" :value-en="$en" />',
            ['ar' => 'اسم', 'en' => 'Name Value']
        );

        $this->assertStringContainsString('name="name_ar"', $html);
        $this->assertStringContainsString('name="name_en"', $html);
        $this->assertStringContainsString('value="اسم"', $html);
        $this->assertStringContainsString('value="Name Value"', $html);
        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringContainsString('Name (Arabic)', $html);
        $this->assertStringContainsString('Name (English)', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=BilingualFieldComponentTest`
Expected: FAIL (component doesn't exist)

- [ ] **Step 3: Create the component**

```blade
{{-- resources/views/components/admin/bilingual-field.blade.php --}}
@props([
    'type' => 'text',
    'name',
    'label' => null,
    'valueAr' => null,
    'valueEn' => null,
])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.field
        :type="$type"
        :name="$name.'_ar'"
        :label="$label ? $label.' ('.__('Arabic').')' : null"
        :value="$valueAr"
        dir="rtl"
    />
    <x-admin.field
        :type="$type"
        :name="$name.'_en'"
        :label="$label ? $label.' ('.__('English').')' : null"
        :value="$valueEn"
    />
</div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=BilingualFieldComponentTest`
Expected: PASS (1 passed)

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/admin/bilingual-field.blade.php tests/Feature/Admin/BilingualFieldComponentTest.php
git commit -m "feat: add x-admin.bilingual-field component"
```

---

### Task 7: `x-admin.table` and `x-admin.empty-state` components

**Files:**
- Create: `resources/views/components/admin/table.blade.php`
- Create: `resources/views/components/admin/empty-state.blade.php`
- Test: `tests/Feature/Admin/TableComponentTest.php`

**Interfaces:**
- Produces: `<x-admin.table>` — a styled `<table>` wrapper; default slot holds `<thead>`/`<tbody>`. `<x-admin.empty-state :message="...">` — a dashed-border placeholder for empty index screens. Prop: `message` (string, required).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_wraps_slot_content(): void
    {
        $html = Blade::render('<x-admin.table><tbody><tr><td>Row</td></tr></tbody></x-admin.table>');

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<tr><td>Row</td></tr>', $html);
    }

    public function test_empty_state_renders_message(): void
    {
        $html = Blade::render('<x-admin.empty-state message="No speakers yet." />');

        $this->assertStringContainsString('No speakers yet.', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TableComponentTest`
Expected: FAIL (components don't exist)

- [ ] **Step 3: Create the components**

```blade
{{-- resources/views/components/admin/table.blade.php --}}
<div class="overflow-x-auto">
    <table class="w-full text-left text-white">
        {{ $slot }}
    </table>
</div>
```

```blade
{{-- resources/views/components/admin/empty-state.blade.php --}}
@props(['message'])

<div class="text-center py-12 text-gray-400 border border-dashed border-gray-700 rounded">
    <p>{{ $message }}</p>
</div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=TableComponentTest`
Expected: PASS (2 passed)

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/admin/table.blade.php resources/views/components/admin/empty-state.blade.php tests/Feature/Admin/TableComponentTest.php
git commit -m "feat: add x-admin.table and x-admin.empty-state components"
```

---

### Task 8: `x-admin.page-header` component

**Files:**
- Create: `resources/views/components/admin/page-header.blade.php`
- Test: `tests/Feature/Admin/PageHeaderComponentTest.php`

**Interfaces:**
- Consumes: `.ccs-flag-accent` (Task 2), `.font-display` (Task 2).
- Produces: `<x-admin.page-header :title="...">` — an `<h1>` with the flag-motif accent; default slot (optional) renders an action (e.g. a "New X" button) right-aligned.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageHeaderComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_title_and_flag_accent(): void
    {
        $html = Blade::render('<x-admin.page-header title="Speakers" />');

        $this->assertStringContainsString('Speakers', $html);
        $this->assertStringContainsString('ccs-flag-accent', $html);
    }

    public function test_renders_optional_action_slot(): void
    {
        $html = Blade::render('<x-admin.page-header title="Speakers"><a href="/new">New</a></x-admin.page-header>');

        $this->assertStringContainsString('<a href="/new">New</a>', $html);
    }

    public function test_no_empty_action_wrapper_when_no_slot_given(): void
    {
        $html = Blade::render('<x-admin.page-header title="Speakers" />');

        $this->assertStringNotContainsString('<div></div>', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PageHeaderComponentTest`
Expected: FAIL (component doesn't exist)

- [ ] **Step 3: Create the component**

```blade
{{-- resources/views/components/admin/page-header.blade.php --}}
@props(['title'])

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <span class="ccs-flag-accent" style="width:8px;height:28px;"></span>
        <h1 class="font-display text-2xl font-bold">{{ $title }}</h1>
    </div>
    @if($slot->isNotEmpty())
        <div>{{ $slot }}</div>
    @endif
</div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=PageHeaderComponentTest`
Expected: PASS (3 passed)

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/admin/page-header.blade.php tests/Feature/Admin/PageHeaderComponentTest.php
git commit -m "feat: add x-admin.page-header component"
```

---

### Task 9: Redesign the login screen

**Files:**
- Modify: `resources/views/admin/auth/login.blade.php`
- Test: `tests/Feature/Admin/LoginPageDesignTest.php` (new — verifies the brand moment renders), plus existing `tests/Feature/Admin/AuthTest.php` (verify no regression)

**Interfaces:**
- Consumes: `x-admin.field` (Task 5), `x-admin.button` (Task 4), `.ccs-hero`/`.ccs-flag-accent`/`.font-display` (Task 2). Keeps `@extends('layouts.app')` — the login page is a guest page with no sidebar, so it does NOT use `layouts.admin`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Tests\TestCase;

class LoginPageDesignTest extends TestCase
{
    public function test_login_page_shows_the_ccs_brand_mark(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('CCS');
        $response->assertSee('ccs-flag-accent', false);
        $response->assertSee('ccs-hero', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LoginPageDesignTest`
Expected: FAIL (current bare login view has none of this markup)

- [ ] **Step 3: Replace the login view**

```blade
{{-- resources/views/admin/auth/login.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="ccs-hero min-h-screen flex flex-col items-center justify-center gap-8 px-4">
    <div class="text-center text-white">
        <div class="flex items-center justify-center gap-2 mb-2">
            <span class="ccs-flag-accent" style="width:36px;height:48px;"></span>
            <span class="font-display text-4xl font-bold">CCS</span>
        </div>
        <p class="uppercase text-xs tracking-widest opacity-80">{{ __('Content Creators Summit') }}</p>
    </div>

    <div class="bg-ccs-black border border-gray-800 rounded-lg p-8 w-full max-w-sm">
        <h1 class="font-display text-xl font-bold mb-6 text-white">{{ __('Admin Login') }}</h1>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <x-admin.field type="email" name="email" label="{{ __('Email') }}" :value="old('email')" required />
            <x-admin.field type="password" name="password" label="{{ __('Password') }}" required />
            <x-admin.button type="submit" class="w-full">{{ __('Log in') }}</x-admin.button>
        </form>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Run the new design test and the existing auth test suite**

Run: `php artisan test --filter=Admin/AuthTest` and `php artisan test --filter=LoginPageDesignTest`
Expected: PASS both (login/logout behavior, routes, and field names are unchanged, only presentation changed; the new design test now passes since the markup exists)

- [ ] **Step 5: Run the full test suite**

Run: `php artisan test`
Expected: all passing

- [ ] **Step 6: Commit**

```bash
git add resources/views/admin/auth/login.blade.php tests/Feature/Admin/LoginPageDesignTest.php
git commit -m "feat: redesign admin login screen with CCS brand identity"
```

---

### Task 10: Redesign the dashboard

**Files:**
- Modify: `app/Http/Controllers/Admin/DashboardController.php`
- Modify: `resources/views/admin/dashboard.blade.php` (already extends `layouts.admin` from Task 3, Step 5)
- Test: `tests/Feature/Admin/DashboardTest.php`

**Interfaces:**
- Consumes: `x-admin.page-header` (Task 8), `x-admin.button` (Task 4), `App\Enums\EventStatus` (existing).
- Produces: `DashboardController::index()` now passes `totalEvents`, `publishedEvents`, `draftEvents` (all `int`) to the view.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_event_counts(): void
    {
        $admin = User::factory()->create();
        Event::factory()->create(['status' => EventStatus::Published]);
        Event::factory()->create(['status' => EventStatus::Published]);
        Event::factory()->create(['status' => EventStatus::Draft]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('3');
        $response->assertSee('2');
        $response->assertSee('1');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DashboardTest`
Expected: FAIL (controller doesn't pass any counts yet)

- [ ] **Step 3: Update the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalEvents' => Event::count(),
            'publishedEvents' => Event::where('status', EventStatus::Published)->count(),
            'draftEvents' => Event::where('status', EventStatus::Draft)->count(),
        ]);
    }
}
```

- [ ] **Step 4: Replace the dashboard view**

```blade
{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Dashboard')" />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-900 rounded-lg p-6">
            <p class="font-display text-3xl font-bold">{{ $totalEvents }}</p>
            <p class="text-gray-400 text-sm mt-1">{{ __('Total Events') }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-6">
            <p class="font-display text-3xl font-bold text-ccs-teal-light">{{ $publishedEvents }}</p>
            <p class="text-gray-400 text-sm mt-1">{{ __('Published') }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-6">
            <p class="font-display text-3xl font-bold text-gray-400">{{ $draftEvents }}</p>
            <p class="text-gray-400 text-sm mt-1">{{ __('Draft') }}</p>
        </div>
    </div>

    <div class="mt-6">
        <x-admin.button href="{{ route('admin.events.index') }}">{{ __('View Events') }}</x-admin.button>
    </div>
@endsection
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=DashboardTest`
Expected: PASS (1 passed)

- [ ] **Step 6: Run the full test suite**

Run: `php artisan test`
Expected: all passing

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/DashboardController.php resources/views/admin/dashboard.blade.php tests/Feature/Admin/DashboardTest.php
git commit -m "feat: redesign admin dashboard with event count summary"
```

---

### Task 11: Redesign Events CRUD screens

**Files:**
- Modify: `resources/views/admin/events/index.blade.php`
- Modify: `resources/views/admin/events/form.blade.php`
- Test: existing `tests/Feature/Admin/EventCrudTest.php` (no new tests — verify no regression)

**Interfaces:**
- Consumes: `x-admin.page-header`, `x-admin.button`, `x-admin.table`, `x-admin.empty-state`, `x-admin.field`, `x-admin.bilingual-field` (Tasks 4-8).

- [ ] **Step 1: Replace the index view**

```blade
{{-- resources/views/admin/events/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Events')">
        <x-admin.button href="{{ route('admin.events.create') }}">{{ __('New Event') }}</x-admin.button>
    </x-admin.page-header>

    @if($events->isEmpty())
        <x-admin.empty-state :message="__('No events yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Name') }}</th>
                    <th class="py-2 px-3">{{ __('Status') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $event->name_en }}</td>
                        <td class="py-2 px-3">{{ $event->status->value }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.edit', $event) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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

- [ ] **Step 2: Replace the form view**

```blade
{{-- resources/views/admin/events/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$event->exists ? __('Edit Event') : __('New Event')" />

    <form method="POST" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}">
        @csrf
        @if($event->exists) @method('PUT') @endif

        <x-admin.field name="slug" label="{{ __('Slug') }}" :value="old('slug', $event->slug)" />
        <x-admin.bilingual-field name="name" label="{{ __('Name') }}" :value-ar="old('name_ar', $event->name_ar)" :value-en="old('name_en', $event->name_en)" />
        <x-admin.bilingual-field name="tagline" label="{{ __('Tagline') }}" :value-ar="old('tagline_ar', $event->tagline_ar)" :value-en="old('tagline_en', $event->tagline_en)" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin.field type="date" name="start_date" label="{{ __('Start Date') }}" :value="old('start_date', optional($event->start_date)->toDateString())" />
            <x-admin.field type="date" name="end_date" label="{{ __('End Date') }}" :value="old('end_date', optional($event->end_date)->toDateString())" />
        </div>

        <x-admin.bilingual-field name="venue_name" label="{{ __('Venue Name') }}" :value-ar="old('venue_name_ar', $event->venue_name_ar)" :value-en="old('venue_name_en', $event->venue_name_en)" />
        <x-admin.bilingual-field name="venue_address" label="{{ __('Venue Address') }}" :value-ar="old('venue_address_ar', $event->venue_address_ar)" :value-en="old('venue_address_en', $event->venue_address_en)" />

        <x-admin.field name="map_embed_url" label="{{ __('Map Embed URL') }}" :value="old('map_embed_url', $event->map_embed_url)" />

        <x-admin.field type="select" name="status" label="{{ __('Status') }}">
            <option value="draft" @selected(old('status', $event->status?->value) === 'draft')>{{ __('Draft') }}</option>
            <option value="published" @selected(old('status', $event->status?->value) === 'published')>{{ __('Published') }}</option>
        </x-admin.field>

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 3: Run the existing Events CRUD test suite to confirm no regression**

Run: `php artisan test --filter=Admin/EventCrudTest`
Expected: PASS (all 5 existing tests — field names, routes, and validation are unchanged)

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`
Expected: all passing

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/events/index.blade.php resources/views/admin/events/form.blade.php
git commit -m "feat: redesign admin Events CRUD screens"
```

---

### Task 12: Redesign Speakers CRUD screens

**Files:**
- Modify: `resources/views/admin/speakers/index.blade.php` (already extends `layouts.admin` from Task 3, Step 5 — only the form needs the extends change)
- Modify: `resources/views/admin/speakers/form.blade.php`
- Test: existing `tests/Feature/Admin/SpeakerCrudTest.php` (no new tests — verify no regression)

**Interfaces:**
- Consumes: `x-admin.page-header`, `x-admin.button`, `x-admin.table`, `x-admin.empty-state`, `x-admin.field`, `x-admin.bilingual-field` (Tasks 4-8).

- [ ] **Step 1: Replace the index view**

```blade
{{-- resources/views/admin/speakers/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Speakers').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.speakers.create', $event) }}">{{ __('New Speaker') }}</x-admin.button>
    </x-admin.page-header>

    @if($speakers->isEmpty())
        <x-admin.empty-state :message="__('No speakers yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3"></th></tr>
            </thead>
            <tbody>
                @foreach($speakers as $speaker)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $speaker->name_en }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.speakers.edit', [$event, $speaker]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.speakers.destroy', [$event, $speaker]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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

- [ ] **Step 2: Replace the form view**

```blade
{{-- resources/views/admin/speakers/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$speaker->exists ? __('Edit Speaker') : __('New Speaker')" />

    <form method="POST" action="{{ $speaker->exists ? route('admin.events.speakers.update', [$event, $speaker]) : route('admin.events.speakers.store', $event) }}">
        @csrf
        @if($speaker->exists) @method('PUT') @endif

        <x-admin.bilingual-field name="name" label="{{ __('Name') }}" :value-ar="old('name_ar', $speaker->name_ar)" :value-en="old('name_en', $speaker->name_en)" />
        <x-admin.bilingual-field name="title" label="{{ __('Title') }}" :value-ar="old('title_ar', $speaker->title_ar)" :value-en="old('title_en', $speaker->title_en)" />
        <x-admin.bilingual-field type="textarea" name="bio" label="{{ __('Bio') }}" :value-ar="old('bio_ar', $speaker->bio_ar)" :value-en="old('bio_en', $speaker->bio_en)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $speaker->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 3: Run the existing Speakers CRUD test suite to confirm no regression**

Run: `php artisan test --filter=Admin/SpeakerCrudTest`
Expected: PASS (all existing tests)

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`
Expected: all passing

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/speakers/index.blade.php resources/views/admin/speakers/form.blade.php
git commit -m "feat: redesign admin Speakers CRUD screens"
```

---

### Task 13: Redesign Sponsors CRUD screens

**Files:**
- Modify: `resources/views/admin/sponsors/index.blade.php`
- Modify: `resources/views/admin/sponsors/form.blade.php`
- Test: existing `tests/Feature/Admin/SponsorCrudTest.php` (no new tests — verify no regression)

**Interfaces:**
- Consumes: `x-admin.page-header`, `x-admin.button`, `x-admin.table`, `x-admin.empty-state`, `x-admin.field`, `x-admin.bilingual-field` (Tasks 4-8).

- [ ] **Step 1: Replace the index view**

```blade
{{-- resources/views/admin/sponsors/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Sponsors').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.sponsors.create', $event) }}">{{ __('New Sponsor') }}</x-admin.button>
    </x-admin.page-header>

    @if($sponsors->isEmpty())
        <x-admin.empty-state :message="__('No sponsors yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3">{{ __('Tier') }}</th><th class="py-2 px-3"></th></tr>
            </thead>
            <tbody>
                @foreach($sponsors as $sponsor)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $sponsor->name_en }}</td>
                        <td class="py-2 px-3">{{ $sponsor->tier }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.sponsors.edit', [$event, $sponsor]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.sponsors.destroy', [$event, $sponsor]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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

- [ ] **Step 2: Replace the form view**

```blade
{{-- resources/views/admin/sponsors/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$sponsor->exists ? __('Edit Sponsor') : __('New Sponsor')" />

    <form method="POST" action="{{ $sponsor->exists ? route('admin.events.sponsors.update', [$event, $sponsor]) : route('admin.events.sponsors.store', $event) }}">
        @csrf
        @if($sponsor->exists) @method('PUT') @endif

        <x-admin.bilingual-field name="name" label="{{ __('Name') }}" :value-ar="old('name_ar', $sponsor->name_ar)" :value-en="old('name_en', $sponsor->name_en)" />

        <x-admin.field type="select" name="tier" label="{{ __('Tier') }}">
            @foreach(['platinum', 'gold', 'silver', 'bronze'] as $tier)
                <option value="{{ $tier }}" @selected(old('tier', $sponsor->tier) === $tier)>{{ ucfirst($tier) }}</option>
            @endforeach
        </x-admin.field>

        <x-admin.field name="website_url" label="{{ __('Website URL') }}" :value="old('website_url', $sponsor->website_url)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $sponsor->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 3: Run the existing Sponsors CRUD test suite to confirm no regression**

Run: `php artisan test --filter=Admin/SponsorCrudTest`
Expected: PASS (all existing tests)

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`
Expected: all passing

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/sponsors/index.blade.php resources/views/admin/sponsors/form.blade.php
git commit -m "feat: redesign admin Sponsors CRUD screens"
```

---

### Task 14: Redesign Ticket Types CRUD screens

**Files:**
- Modify: `resources/views/admin/ticket-types/index.blade.php`
- Modify: `resources/views/admin/ticket-types/form.blade.php`
- Test: existing `tests/Feature/Admin/TicketTypeCrudTest.php` (no new tests — verify no regression)

**Interfaces:**
- Consumes: `x-admin.page-header`, `x-admin.button`, `x-admin.table`, `x-admin.empty-state`, `x-admin.field`, `x-admin.bilingual-field` (Tasks 4-8).

- [ ] **Step 1: Replace the index view**

```blade
{{-- resources/views/admin/ticket-types/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Ticket Types').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.ticket-types.create', $event) }}">{{ __('New Ticket Type') }}</x-admin.button>
    </x-admin.page-header>

    @if($ticketTypes->isEmpty())
        <x-admin.empty-state :message="__('No ticket types yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Name') }}</th>
                    <th class="py-2 px-3">{{ __('Price') }}</th>
                    <th class="py-2 px-3">{{ __('Workshop Slots') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($ticketTypes as $ticketType)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $ticketType->name_en }}</td>
                        <td class="py-2 px-3">{{ $ticketType->price }} {{ $ticketType->currency }}</td>
                        <td class="py-2 px-3">{{ $ticketType->workshop_slot_count ?? __('Unlimited') }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.ticket-types.edit', [$event, $ticketType]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.ticket-types.destroy', [$event, $ticketType]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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

- [ ] **Step 2: Replace the form view**

```blade
{{-- resources/views/admin/ticket-types/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$ticketType->exists ? __('Edit Ticket Type') : __('New Ticket Type')" />

    <form method="POST" action="{{ $ticketType->exists ? route('admin.events.ticket-types.update', [$event, $ticketType]) : route('admin.events.ticket-types.store', $event) }}">
        @csrf
        @if($ticketType->exists) @method('PUT') @endif

        <x-admin.bilingual-field name="name" label="{{ __('Name') }}" :value-ar="old('name_ar', $ticketType->name_ar)" :value-en="old('name_en', $ticketType->name_en)" />
        <x-admin.bilingual-field type="textarea" name="description" label="{{ __('Description') }}" :value-ar="old('description_ar', $ticketType->description_ar)" :value-en="old('description_en', $ticketType->description_en)" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-admin.field type="number" name="price" label="{{ __('Price') }}" :value="old('price', $ticketType->price)" />
            <x-admin.field name="currency" label="{{ __('Currency') }}" :value="old('currency', $ticketType->currency ?? 'SAR')" />
            <x-admin.field type="number" name="workshop_slot_count" label="{{ __('Workshop Slots (blank = unlimited)') }}" :value="old('workshop_slot_count', $ticketType->workshop_slot_count)" />
        </div>

        <x-admin.field type="checkbox" name="is_active" label="{{ __('Active') }}" :checked="old('is_active', $ticketType->is_active ?? true)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $ticketType->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 3: Run the existing Ticket Types CRUD test suite to confirm no regression**

Run: `php artisan test --filter=Admin/TicketTypeCrudTest`
Expected: PASS (all existing tests)

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`
Expected: all passing

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/ticket-types/index.blade.php resources/views/admin/ticket-types/form.blade.php
git commit -m "feat: redesign admin Ticket Types CRUD screens"
```

---

### Task 15: Redesign Workshops CRUD screens

**Files:**
- Modify: `resources/views/admin/workshops/index.blade.php`
- Modify: `resources/views/admin/workshops/form.blade.php`
- Test: existing `tests/Feature/Admin/WorkshopCrudTest.php` (no new tests — verify no regression)

**Interfaces:**
- Consumes: `x-admin.page-header`, `x-admin.button`, `x-admin.table`, `x-admin.empty-state`, `x-admin.field`, `x-admin.bilingual-field` (Tasks 4-8).

- [ ] **Step 1: Replace the index view**

```blade
{{-- resources/views/admin/workshops/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Workshops').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.workshops.create', $event) }}">{{ __('New Workshop') }}</x-admin.button>
    </x-admin.page-header>

    @if($workshops->isEmpty())
        <x-admin.empty-state :message="__('No workshops yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3">{{ __('Capacity') }}</th><th class="py-2 px-3"></th></tr>
            </thead>
            <tbody>
                @foreach($workshops as $workshop)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $workshop->name_en }}</td>
                        <td class="py-2 px-3">{{ $workshop->capacity }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.workshops.edit', [$event, $workshop]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.workshops.destroy', [$event, $workshop]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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

- [ ] **Step 2: Replace the form view**

```blade
{{-- resources/views/admin/workshops/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$workshop->exists ? __('Edit Workshop') : __('New Workshop')" />

    <form method="POST" action="{{ $workshop->exists ? route('admin.events.workshops.update', [$event, $workshop]) : route('admin.events.workshops.store', $event) }}">
        @csrf
        @if($workshop->exists) @method('PUT') @endif

        <x-admin.field name="slug" label="{{ __('Slug') }}" :value="old('slug', $workshop->slug)" />
        <x-admin.bilingual-field name="name" label="{{ __('Name') }}" :value-ar="old('name_ar', $workshop->name_ar)" :value-en="old('name_en', $workshop->name_en)" />
        <x-admin.bilingual-field type="textarea" name="description" label="{{ __('Description') }}" :value-ar="old('description_ar', $workshop->description_ar)" :value-en="old('description_en', $workshop->description_en)" />

        <x-admin.field type="select" name="speaker_id" label="{{ __('Speaker') }}">
            <option value="">{{ __('None') }}</option>
            @foreach($speakers as $speaker)
                <option value="{{ $speaker->id }}" @selected(old('speaker_id', $workshop->speaker_id) === $speaker->id)>{{ $speaker->name_en }}</option>
            @endforeach
        </x-admin.field>

        <x-admin.field type="number" name="capacity" label="{{ __('Capacity') }}" :value="old('capacity', $workshop->capacity ?? 0)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $workshop->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 3: Run the existing Workshops CRUD test suite to confirm no regression**

Run: `php artisan test --filter=Admin/WorkshopCrudTest`
Expected: PASS (all existing tests)

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`
Expected: all passing

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/workshops/index.blade.php resources/views/admin/workshops/form.blade.php
git commit -m "feat: redesign admin Workshops CRUD screens"
```

---

### Task 16: Redesign Agenda Items CRUD screens

**Files:**
- Modify: `resources/views/admin/agenda-items/index.blade.php`
- Modify: `resources/views/admin/agenda-items/form.blade.php`
- Test: existing `tests/Feature/Admin/AgendaItemCrudTest.php` (no new tests — verify no regression)

**Interfaces:**
- Consumes: `x-admin.page-header`, `x-admin.button`, `x-admin.table`, `x-admin.empty-state`, `x-admin.field`, `x-admin.bilingual-field` (Tasks 4-8).

- [ ] **Step 1: Replace the index view**

```blade
{{-- resources/views/admin/agenda-items/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Agenda Items').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.agenda-items.create', $event) }}">{{ __('New Agenda Item') }}</x-admin.button>
    </x-admin.page-header>

    @if($items->isEmpty())
        <x-admin.empty-state :message="__('No agenda items yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Day') }}</th>
                    <th class="py-2 px-3">{{ __('Time') }}</th>
                    <th class="py-2 px-3">{{ __('Title') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $item->day_date->toDateString() }}</td>
                        <td class="py-2 px-3">{{ $item->start_time->format('H:i') }}–{{ $item->end_time->format('H:i') }}</td>
                        <td class="py-2 px-3">{{ $item->title_en }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.agenda-items.edit', [$event, $item]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.agenda-items.destroy', [$event, $item]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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

- [ ] **Step 2: Replace the form view**

```blade
{{-- resources/views/admin/agenda-items/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$item->exists ? __('Edit Agenda Item') : __('New Agenda Item')" />

    <form method="POST" action="{{ $item->exists ? route('admin.events.agenda-items.update', [$event, $item]) : route('admin.events.agenda-items.store', $event) }}">
        @csrf
        @if($item->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-admin.field type="date" name="day_date" label="{{ __('Day') }}" :value="old('day_date', optional($item->day_date)->toDateString())" />
            <x-admin.field type="time" name="start_time" label="{{ __('Start Time') }}" :value="old('start_time', optional($item->start_time)->format('H:i'))" />
            <x-admin.field type="time" name="end_time" label="{{ __('End Time') }}" :value="old('end_time', optional($item->end_time)->format('H:i'))" />
        </div>

        <x-admin.bilingual-field name="title" label="{{ __('Title') }}" :value-ar="old('title_ar', $item->title_ar)" :value-en="old('title_en', $item->title_en)" />

        <x-admin.field type="select" name="type" label="{{ __('Type') }}">
            @foreach($types as $type)
                <option value="{{ $type->value }}" @selected(old('type', $item->type?->value) === $type->value)>{{ ucfirst($type->value) }}</option>
            @endforeach
        </x-admin.field>

        <x-admin.field type="select" name="speaker_id" label="{{ __('Speaker') }}">
            <option value="">{{ __('None') }}</option>
            @foreach($speakers as $speaker)
                <option value="{{ $speaker->id }}" @selected(old('speaker_id', $item->speaker_id) === $speaker->id)>{{ $speaker->name_en }}</option>
            @endforeach
        </x-admin.field>

        <x-admin.field type="select" name="workshop_id" label="{{ __('Workshop') }}">
            <option value="">{{ __('None') }}</option>
            @foreach($workshops as $workshop)
                <option value="{{ $workshop->id }}" @selected(old('workshop_id', $item->workshop_id) === $workshop->id)>{{ $workshop->name_en }}</option>
            @endforeach
        </x-admin.field>

        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $item->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 3: Run the existing Agenda Items CRUD test suite to confirm no regression**

Run: `php artisan test --filter=Admin/AgendaItemCrudTest`
Expected: PASS (all existing tests)

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`
Expected: all passing

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/agenda-items/index.blade.php resources/views/admin/agenda-items/form.blade.php
git commit -m "feat: redesign admin Agenda Items CRUD screens"
```

---

### Task 17: Redesign FAQs CRUD screens

**Files:**
- Modify: `resources/views/admin/faqs/index.blade.php`
- Modify: `resources/views/admin/faqs/form.blade.php`
- Test: existing `tests/Feature/Admin/FaqCrudTest.php` (no new tests — verify no regression)

**Interfaces:**
- Consumes: `x-admin.page-header`, `x-admin.button`, `x-admin.table`, `x-admin.empty-state`, `x-admin.field`, `x-admin.bilingual-field` (Tasks 4-8).

- [ ] **Step 1: Replace the index view**

```blade
{{-- resources/views/admin/faqs/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('FAQs').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.faqs.create', $event) }}">{{ __('New FAQ') }}</x-admin.button>
    </x-admin.page-header>

    @if($faqs->isEmpty())
        <x-admin.empty-state :message="__('No FAQs yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Question') }}</th><th class="py-2 px-3"></th></tr>
            </thead>
            <tbody>
                @foreach($faqs as $faq)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $faq->question_en }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.faqs.edit', [$event, $faq]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.faqs.destroy', [$event, $faq]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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

- [ ] **Step 2: Replace the form view**

```blade
{{-- resources/views/admin/faqs/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$faq->exists ? __('Edit FAQ') : __('New FAQ')" />

    <form method="POST" action="{{ $faq->exists ? route('admin.events.faqs.update', [$event, $faq]) : route('admin.events.faqs.store', $event) }}">
        @csrf
        @if($faq->exists) @method('PUT') @endif

        <x-admin.bilingual-field name="question" label="{{ __('Question') }}" :value-ar="old('question_ar', $faq->question_ar)" :value-en="old('question_en', $faq->question_en)" />
        <x-admin.bilingual-field type="textarea" name="answer" label="{{ __('Answer') }}" :value-ar="old('answer_ar', $faq->answer_ar)" :value-en="old('answer_en', $faq->answer_en)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $faq->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 3: Run the existing FAQs CRUD test suite to confirm no regression**

Run: `php artisan test --filter=Admin/FaqCrudTest`
Expected: PASS (all existing tests)

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`
Expected: all passing

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/faqs/index.blade.php resources/views/admin/faqs/form.blade.php
git commit -m "feat: redesign admin FAQs CRUD screens"
```

---

### Task 18: Redesign Landing Page Content editor

**Files:**
- Modify: `resources/views/admin/landing-page-content/edit.blade.php`
- Test: existing `tests/Feature/Admin/LandingPageContentCrudTest.php` (no new tests — verify no regression)

**Interfaces:**
- Consumes: `x-admin.page-header`, `x-admin.button`, `x-admin.bilingual-field` (Tasks 4-8).

- [ ] **Step 1: Replace the edit view**

```blade
{{-- resources/views/admin/landing-page-content/edit.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Landing Page Content').' — '.$event->name_en" />

    <form method="POST" action="{{ route('admin.events.content.update', $event) }}">
        @csrf
        @method('PUT')

        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('Hero Headline') }}</h2>
        <x-admin.bilingual-field name="hero_headline" :value-ar="old('hero_headline_ar', $values['hero_headline_ar'])" :value-en="old('hero_headline_en', $values['hero_headline_en'])" />

        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('About Body') }}</h2>
        <x-admin.bilingual-field type="textarea" name="about_body" :value-ar="old('about_body_ar', $values['about_body_ar'])" :value-en="old('about_body_en', $values['about_body_en'])" />

        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('Location Intro') }}</h2>
        <x-admin.bilingual-field type="textarea" name="location_intro" :value-ar="old('location_intro_ar', $values['location_intro_ar'])" :value-en="old('location_intro_en', $values['location_intro_en'])" />

        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('Awards Teaser Blurb') }}</h2>
        <x-admin.bilingual-field type="textarea" name="awards_teaser_blurb" :value-ar="old('awards_teaser_blurb_ar', $values['awards_teaser_blurb_ar'])" :value-en="old('awards_teaser_blurb_en', $values['awards_teaser_blurb_en'])" />

        <x-admin.button type="submit" class="mt-4">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 2: Run the existing Landing Page Content test suite to confirm no regression**

Run: `php artisan test --filter=Admin/LandingPageContentCrudTest`
Expected: PASS (all existing tests — note this view uses plain `_ar`/`_en` field name pairs, e.g. `hero_headline_ar`, which is exactly what `x-admin.bilingual-field name="hero_headline"` produces)

- [ ] **Step 3: Run the full test suite**

Run: `php artisan test`
Expected: all passing (this is the last task — full suite should now include Tasks 1, 3, 4, 5, 6, 7, 8, 10's new tests plus every pre-existing test, all green)

- [ ] **Step 4: Commit**

```bash
git add resources/views/admin/landing-page-content/edit.blade.php
git commit -m "feat: redesign admin Landing Page Content editor"
```

---

## Final manual verification (after all tasks)

Run `php artisan migrate:fresh --seed && npm run build && php artisan serve`, then:
1. Visit `/` — confirm it redirects to `/events/ccs-2026` and the real landing page renders.
2. Visit `/admin/login` — confirm the gradient background, CCS wordmark, and centered card render correctly in both `?lang=en` and default (Arabic) locale.
3. Log in with `admin@ccs.test` / `password123` — confirm the dashboard shows event counts.
4. Click into Events → Speakers/Sponsors/Ticket Types/Workshops/Agenda/FAQs/Content for `ccs-2026` — confirm the sidebar shows the event name and all links, confirm each index/form screen renders and the flag accent appears in the page header and the active sidebar item.
5. Submit a form on at least 2 screens (e.g. edit a Speaker, edit Landing Page Content) — confirm saves succeed and redirect correctly.
