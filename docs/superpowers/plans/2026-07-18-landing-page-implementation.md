# Landing Page & Public Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up the Laravel app from scratch and deliver the public CCS landing page (+ Workshops, Agenda, Ticket Request, Awards-placeholder pages) plus basic authenticated admin CRUD for the content behind them.

**Architecture:** Laravel 12 MVC with a Service Layer (business logic) and Repository Pattern (data access) between thin Controllers and Eloquent Models. Public routes are read-only and scoped by `{event}` slug. Admin routes sit behind Laravel's built-in session auth (`users` table) and reuse the same Services/Repositories for writes. Bilingual content uses paired `_ar`/`_en` columns on the same row — no separate translations table.

**Tech Stack:** Laravel 12, PHP 8.3, MySQL, Laravel Boost, Tailwind CSS v4 (Vite plugin), AlpineJS where needed.

## Global Constraints

- Laravel 12, PHP 8.3, MySQL. Laravel Boost installed as a dev dependency.
- Tailwind CSS v4 only — no Bootstrap (revised from the original Bootstrap 5 + SCSS choice after Task 2 was first implemented; see Task 2's note). AlpineJS for light interactivity (accordions, countdown, language toggle) — Tailwind ships no JS components, so anything Bootstrap used to handle via `data-bs-*` (e.g. the FAQ accordion) is now plain Alpine `x-data`/`x-show`.
- MVC + Service Layer + Repository Pattern where appropriate. Controllers orchestrate only; they never contain business logic. Services contain business logic. Models contain relationships only (per `.claude/skills/coding-standards.md`).
- Strict typing (`declare(strict_types=1);`) in all new PHP files.
- No authentication for the public/attendee side. Admin routes require Laravel's built-in session auth against a `users` table.
- One deployment serves multiple Events; every public and admin route is scoped by `{event}` (route-model-bound by slug).
- Every translatable field is a pair of columns on the same row: `{field}_ar`, `{field}_en`. Never a separate translations table.
- A landing-page section with zero underlying rows renders nothing — no admin "hide section" toggle exists.
- CCS palette: `#ff7e71`, `#d33333`, `#430d14`, `#171f22`, `#2a7675`, `#7ccbcf`, `#fad48b`, `#a48755`. Dark theme. Subtle animations only, no trendy effects.
- Never hardcode event-specific values (colors above are the CCS seed data's theme, not framework constants).

---

## Phase A — Foundation

### Task 1: Laravel project scaffold, MySQL, Laravel Boost

**Files:**
- Create: entire Laravel 12 skeleton at repo root (`composer.json`, `artisan`, `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `tests/`)
- Create: `.env`, `.env.example` (MySQL driver configured)
- Test: `tests/Feature/SmokeTest.php`

**Interfaces:**
- Produces: a running Laravel 12 app on PHP 8.3 with MySQL configured and Laravel Boost installed — every later task builds inside this skeleton.

- [ ] **Step 1: Scaffold the Laravel 12 app into the current directory**

Run (from repo root, which currently only has `.claude/`, `docs/`, `README.md`, `.gitignore`):
```bash
composer create-project laravel/laravel:^12.0 _laravel_tmp
```
Then move the generated contents up into the repo root (so `artisan`, `app/`, etc. sit at the top level next to `.claude/` and `docs/`), and remove `_laravel_tmp`. Do not overwrite the existing `README.md`, `.gitignore`, `.claude/`, or `docs/` — merge `.gitignore` entries instead of replacing the file.

Expected: `artisan`, `composer.json`, `app/Models/User.php` exist at repo root.

- [ ] **Step 2: Configure MySQL in `.env`**

Edit `.env` (and mirror the non-secret defaults into `.env.example`):
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ccs_ticketing
DB_USERNAME=root
DB_PASSWORD=
```

Run:
```bash
php artisan config:clear
php artisan migrate
```
Expected: default Laravel migrations (`users`, `cache`, `jobs`, etc.) run successfully against the local MySQL database.

- [ ] **Step 3: Install Laravel Boost**

Run:
```bash
composer require laravel/boost --dev
php artisan boost:install
```
Expected: command completes without error; Boost's MCP config is written per its own installer prompts (accept defaults).

- [ ] **Step 4: Write the smoke test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_application_boots(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
```

- [ ] **Step 5: Run the test suite**

Run: `php artisan test --filter=SmokeTest`
Expected: PASS (1 passed)

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "chore: scaffold Laravel 12 app with MySQL and Laravel Boost"
```

### Task 2: Tailwind CSS asset pipeline and bilingual base layout

> **Revised:** this task was originally written and first implemented for Bootstrap 5 + SCSS. Mid-implementation the project switched to Tailwind CSS (see plan Global Constraints). If Bootstrap/SCSS files already exist from an earlier pass, remove them (`resources/scss/`, the `bootstrap`/`@popperjs/core`/`sass` devDependencies) as part of Step 1 rather than leaving both stacks installed.

**Files:**
- Create: `resources/css/app.css`
- Modify: `resources/js/app.js` (AlpineJS only — Tailwind ships no JS)
- Modify: `vite.config.js`
- Create: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/welcome.blade.php` (Tailwind placeholder)
- Create: `app/Http/Middleware/SetLocale.php`
- Modify: `bootstrap/app.php` (register middleware)
- Test: `tests/Feature/LocaleSwitchTest.php`

**Interfaces:**
- Consumes: nothing from Task 1 beyond the base skeleton.
- Produces: `layouts/app` Blade layout (accepts a `$title` slot/section), and `app()->getLocale()` driven by `SetLocale` middleware — every later view extends this layout. Custom CCS color utilities (`bg-ccs-red`, `text-ccs-gold`, etc.) are available everywhere via Tailwind's `@theme` mechanism, defined once in `resources/css/app.css`.

- [ ] **Step 1: Install Tailwind and AlpineJS (remove Bootstrap/Sass if present)**

Run:
```bash
npm uninstall bootstrap @popperjs/core sass
npm install tailwindcss @tailwindcss/vite alpinejs --save-dev
```
If `resources/scss/` exists from an earlier Bootstrap-based pass, delete it — Tailwind uses a single CSS entrypoint, not SCSS partials.

- [ ] **Step 2: Create the Tailwind entrypoint with the CCS theme tokens**

Tailwind v4 needs no separate config file for simple theme tokens — custom colors declared in `@theme` automatically become utility classes (`bg-ccs-red`, `text-ccs-gold`, `border-ccs-teal`, etc.):

```css
/* resources/css/app.css */
@import "tailwindcss";

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

[dir="rtl"] {
  text-align: right;
}

.ccs-hero {
  background: linear-gradient(135deg, var(--color-ccs-coral) 0%, var(--color-ccs-coral) 12%, var(--color-ccs-red) 13%, var(--color-ccs-maroon) 55%, var(--color-ccs-black) 100%);
}
```

- [ ] **Step 3: Wire AlpineJS in `resources/js/app.js`**

```js
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

- [ ] **Step 4: Point Vite at the Tailwind plugin and the new CSS entrypoint**

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

- [ ] **Step 5: Write the failing locale test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    public function test_default_locale_is_arabic_with_rtl_direction(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_english_locale_sets_ltr_direction(): void
    {
        $response = $this->get('/?lang=en');

        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `php artisan test --filter=LocaleSwitchTest`
Expected: FAIL (no `dir="rtl"`/`dir="ltr"` in the default welcome view yet)

- [ ] **Step 7: Create the `SetLocale` middleware**

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang', $request->session()->get('locale', 'ar'));
        $locale = in_array($locale, ['ar', 'en'], true) ? $locale : 'ar';

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}
```

- [ ] **Step 8: Register the middleware globally**

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\SetLocale::class,
    ]);
})
```

- [ ] **Step 9: Create the base bilingual layout and update the welcome placeholder**

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CCS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ccs-black text-white">
    @yield('content')
</body>
</html>
```

Update `resources/views/welcome.blade.php` to a minimal Tailwind placeholder (no product content, no CCS branding — this is Laravel's stock scaffold page, not a real page in this product):

```blade
{{-- resources/views/welcome.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-16 text-center">
        <h1 class="text-2xl font-semibold mb-2">Welcome</h1>
        <p class="text-gray-400">This page is a placeholder. The application is under construction.</p>
    </div>
@endsection
```

- [ ] **Step 10: Run tests to verify they pass**

Run: `php artisan test --filter=LocaleSwitchTest`
Expected: PASS (2 passed)

Run: `php artisan test --filter=SmokeTest`
Expected: PASS (still 1 passed)

- [ ] **Step 11: Commit**

```bash
git add resources vite.config.js app/Http/Middleware/SetLocale.php bootstrap/app.php tests/Feature/LocaleSwitchTest.php package.json package-lock.json
git commit -m "feat: Tailwind CSS asset pipeline and bilingual RTL/LTR base layout"
```

### Task 3: Admin authentication (session-based, `users` table)

**Files:**
- Create: `app/Http/Controllers/Admin/AuthController.php`
- Create: `resources/views/admin/auth/login.blade.php`
- Create: `resources/views/admin/dashboard.blade.php`
- Create: `app/Http/Controllers/Admin/DashboardController.php`
- Create: `database/seeders/AdminUserSeeder.php`
- Modify: `routes/web.php`
- Modify: `bootstrap/app.php` (guest redirect target)
- Test: `tests/Feature/Admin/AuthTest.php`

**Interfaces:**
- Produces: named routes `admin.login` (GET/POST), `admin.logout` (POST), `admin.dashboard` (GET, `auth` middleware). Every admin CRUD task in Phase D nests its routes inside the same `auth`-protected `admin.` route group defined here.

- [ ] **Step 1: Write the failing auth tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_visiting_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@ccs.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@ccs.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@ccs.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@ccs.test',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/logout');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=Admin/AuthTest`
Expected: FAIL (route `admin.login` not defined)

- [ ] **Step 3: Create the admin auth controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(): \Illuminate\View\View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
```

- [ ] **Step 4: Create the dashboard controller and view**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard');
    }
}
```

```blade
{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Admin Dashboard</h1>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit">Log out</button>
    </form>
@endsection
```

```blade
{{-- resources/views/admin/auth/login.blade.php --}}
@extends('layouts.app')

@section('content')
    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>

        @error('email')
            <p>{{ $message }}</p>
        @enderror

        <button type="submit">Log in</button>
    </form>
@endsection
```

- [ ] **Step 5: Wire the routes**

```php
// routes/web.php
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });
});
```

- [ ] **Step 6: Point guest redirects at the admin login route**

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->redirectGuestsTo(fn () => route('admin.login'));
    $middleware->web(append: [
        \App\Http\Middleware\SetLocale::class,
    ]);
})
```

- [ ] **Step 7: Create the admin user seeder**

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'CCS Admin',
            'email' => 'admin@ccs.test',
            'password' => bcrypt('password123'),
        ]);
    }
}
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=Admin/AuthTest`
Expected: PASS (4 passed)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Admin app/Http/Middleware resources/views/admin routes/web.php bootstrap/app.php database/seeders/AdminUserSeeder.php tests/Feature/Admin/AuthTest.php
git commit -m "feat: session-based admin authentication"
```

---

## Phase B — Core data layer

Each task below adds one entity's migration + model + factory, and — if the entity belongs to `Event` — adds the corresponding `belongsTo`/`hasMany` relationship pair to that entity's model and to `Event`. Write-side Services, Form Requests, and admin Controllers are built in Phase D, next to the CRUD screens that are their only caller (YAGNI: no unused service code sitting idle between phases).

### Task 4: Events

**Files:**
- Create: `database/migrations/xxxx_create_events_table.php`
- Create: `app/Models/Event.php`
- Create: `app/Enums/EventStatus.php`
- Create: `database/factories/EventFactory.php`
- Test: `tests/Unit/Models/EventTest.php`

**Interfaces:**
- Produces: `Event` model with `slug` as route key, `EventStatus` enum (`Draft`, `Published`), factory `Event::factory()`. Every later entity task's `belongsTo(Event::class)` relies on this model existing.

- [ ] **Step 1: Write the failing model test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_uses_slug_as_route_key(): void
    {
        $event = Event::factory()->create(['slug' => 'ccs-2026']);

        $this->assertSame('slug', $event->getRouteKeyName());
        $this->assertSame('ccs-2026', $event->getRouteKey());
    }

    public function test_event_has_bilingual_name_fields(): void
    {
        $event = Event::factory()->create([
            'name_ar' => 'قمة صناع المحتوى',
            'name_en' => 'Content Creators Summit',
        ]);

        $this->assertSame('قمة صناع المحتوى', $event->name_ar);
        $this->assertSame('Content Creators Summit', $event->name_en);
    }

    public function test_event_status_casts_to_enum(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $this->assertSame(EventStatus::Published, $event->status);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Unit/Models/EventTest`
Expected: FAIL (class `App\Models\Event` not found)

- [ ] **Step 3: Create the `EventStatus` enum**

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
```

- [ ] **Step 4: Create the migration**

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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('tagline_ar')->nullable();
            $table->string('tagline_en')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('venue_name_ar')->nullable();
            $table->string('venue_name_en')->nullable();
            $table->string('venue_address_ar')->nullable();
            $table->string('venue_address_en')->nullable();
            $table->string('map_embed_url')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
```

- [ ] **Step 5: Create the `Event` model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name_ar', 'name_en', 'tagline_ar', 'tagline_en',
        'start_date', 'end_date',
        'venue_name_ar', 'venue_name_en', 'venue_address_ar', 'venue_address_en',
        'map_embed_url', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => EventStatus::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }
}
```

- [ ] **Step 6: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
            'name_ar' => $this->faker->sentence(3),
            'name_en' => $this->faker->sentence(3),
            'tagline_ar' => $this->faker->sentence(4),
            'tagline_en' => $this->faker->sentence(4),
            'start_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+3 months'),
            'venue_name_ar' => $this->faker->company(),
            'venue_name_en' => $this->faker->company(),
            'venue_address_ar' => $this->faker->address(),
            'venue_address_en' => $this->faker->address(),
            'map_embed_url' => $this->faker->url(),
            'status' => EventStatus::Draft,
        ];
    }
}
```

- [ ] **Step 7: Run migrations and tests**

Run: `php artisan migrate`
Run: `php artisan test --filter=Unit/Models/EventTest`
Expected: PASS (3 passed)

- [ ] **Step 8: Commit**

```bash
git add database/migrations database/factories/EventFactory.php app/Models/Event.php app/Enums/EventStatus.php tests/Unit/Models/EventTest.php
git commit -m "feat: add Event model"
```

### Task 5: Speakers

**Files:**
- Create: `database/migrations/xxxx_create_speakers_table.php`
- Create: `app/Models/Speaker.php`
- Create: `database/factories/SpeakerFactory.php`
- Modify: `app/Models/Event.php` (add `speakers()` relationship)
- Test: `tests/Unit/Models/SpeakerTest.php`

**Interfaces:**
- Consumes: `Event` model (Task 4).
- Produces: `Speaker` model (`belongsTo(Event::class)`), `Event::speakers()` (`hasMany`), factory `Speaker::factory()`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeakerTest extends TestCase
{
    use RefreshDatabase;

    public function test_speaker_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $speaker = Speaker::factory()->for($event)->create();

        $this->assertTrue($speaker->event->is($event));
    }

    public function test_event_has_many_speakers(): void
    {
        $event = Event::factory()->create();
        Speaker::factory()->count(3)->for($event)->create();

        $this->assertCount(3, $event->speakers);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Unit/Models/SpeakerTest`
Expected: FAIL (class `App\Models\Speaker` not found)

- [ ] **Step 3: Create the migration**

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
        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->text('bio_ar')->nullable();
            $table->text('bio_en')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('speakers');
    }
};
```

- [ ] **Step 4: Create the `Speaker` model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SpeakerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Speaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name_ar', 'name_en', 'title_ar', 'title_en',
        'bio_ar', 'bio_en', 'photo_path', 'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): SpeakerFactory
    {
        return SpeakerFactory::new();
    }
}
```

- [ ] **Step 5: Add the inverse relationship to `Event`**

```php
// app/Models/Event.php — add import + method
use Illuminate\Database\Eloquent\Relations\HasMany;
// ...
public function speakers(): HasMany
{
    return $this->hasMany(Speaker::class)->orderBy('sort_order');
}
```

- [ ] **Step 6: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpeakerFactory extends Factory
{
    protected $model = Speaker::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name_ar' => $this->faker->name(),
            'name_en' => $this->faker->name(),
            'title_ar' => $this->faker->jobTitle(),
            'title_en' => $this->faker->jobTitle(),
            'bio_ar' => $this->faker->paragraph(),
            'bio_en' => $this->faker->paragraph(),
            'photo_path' => null,
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 7: Run migrations and tests**

Run: `php artisan migrate`
Run: `php artisan test --filter=Unit/Models/SpeakerTest`
Expected: PASS (2 passed)

- [ ] **Step 8: Commit**

```bash
git add database/migrations database/factories/SpeakerFactory.php app/Models/Speaker.php app/Models/Event.php tests/Unit/Models/SpeakerTest.php
git commit -m "feat: add Speaker model"
```

### Task 6: Sponsors

**Files:**
- Create: `database/migrations/xxxx_create_sponsors_table.php`
- Create: `app/Models/Sponsor.php`
- Create: `database/factories/SponsorFactory.php`
- Modify: `app/Models/Event.php` (add `sponsors()` relationship)
- Test: `tests/Unit/Models/SponsorTest.php`

**Interfaces:**
- Consumes: `Event` model (Task 4).
- Produces: `Sponsor` model (`belongsTo(Event::class)`), `Event::sponsors()` (`hasMany`), factory `Sponsor::factory()`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Sponsor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorTest extends TestCase
{
    use RefreshDatabase;

    public function test_sponsor_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $sponsor = Sponsor::factory()->for($event)->create();

        $this->assertTrue($sponsor->event->is($event));
    }

    public function test_event_has_many_sponsors(): void
    {
        $event = Event::factory()->create();
        Sponsor::factory()->count(2)->for($event)->create(['tier' => 'gold']);

        $this->assertCount(2, $event->sponsors);
        $this->assertSame('gold', $event->sponsors->first()->tier);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Unit/Models/SponsorTest`
Expected: FAIL (class `App\Models\Sponsor` not found)

- [ ] **Step 3: Create the migration**

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
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('logo_path')->nullable();
            $table->string('tier')->default('bronze');
            $table->string('website_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};
```

- [ ] **Step 4: Create the `Sponsor` model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SponsorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name_ar', 'name_en', 'logo_path', 'tier', 'website_url', 'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): SponsorFactory
    {
        return SponsorFactory::new();
    }
}
```

- [ ] **Step 5: Add the inverse relationship to `Event`**

```php
// app/Models/Event.php — add method (HasMany already imported from Task 5)
public function sponsors(): HasMany
{
    return $this->hasMany(Sponsor::class)->orderBy('sort_order');
}
```

- [ ] **Step 6: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\Factory;

class SponsorFactory extends Factory
{
    protected $model = Sponsor::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name_ar' => $this->faker->company(),
            'name_en' => $this->faker->company(),
            'logo_path' => null,
            'tier' => $this->faker->randomElement(['platinum', 'gold', 'silver', 'bronze']),
            'website_url' => $this->faker->url(),
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 7: Run migrations and tests**

Run: `php artisan migrate`
Run: `php artisan test --filter=Unit/Models/SponsorTest`
Expected: PASS (2 passed)

- [ ] **Step 8: Commit**

```bash
git add database/migrations database/factories/SponsorFactory.php app/Models/Sponsor.php app/Models/Event.php tests/Unit/Models/SponsorTest.php
git commit -m "feat: add Sponsor model"
```

### Task 7: Ticket Types

**Files:**
- Create: `database/migrations/xxxx_create_ticket_types_table.php`
- Create: `app/Models/TicketType.php`
- Create: `database/factories/TicketTypeFactory.php`
- Modify: `app/Models/Event.php` (add `ticketTypes()` relationship)
- Test: `tests/Unit/Models/TicketTypeTest.php`

**Interfaces:**
- Consumes: `Event` model (Task 4).
- Produces: `TicketType` model (`belongsTo(Event::class)`), `Event::ticketTypes()` (`hasMany`), factory `TicketType::factory()`. `workshop_slot_count` is a plain integer column (`null` = unlimited) — no relationship to `Workshop` at this layer, per `.claude/skills/workshop-module.md` ("Do not store workshop ids on Tickets").

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_type_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create();

        $this->assertTrue($ticketType->event->is($event));
    }

    public function test_workshop_slot_count_can_be_zero_or_null(): void
    {
        $general = TicketType::factory()->create(['workshop_slot_count' => 0]);
        $platinum = TicketType::factory()->create(['workshop_slot_count' => null]);

        $this->assertSame(0, $general->workshop_slot_count);
        $this->assertNull($platinum->workshop_slot_count);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Unit/Models/TicketTypeTest`
Expected: FAIL (class `App\Models\TicketType` not found)

- [ ] **Step 3: Create the migration**

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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('price');
            $table->string('currency', 3)->default('SAR');
            $table->unsignedInteger('workshop_slot_count')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
```

- [ ] **Step 4: Create the `TicketType` model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TicketTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name_ar', 'name_en', 'description_ar', 'description_en',
        'price', 'currency', 'workshop_slot_count', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): TicketTypeFactory
    {
        return TicketTypeFactory::new();
    }
}
```

- [ ] **Step 5: Add the inverse relationship to `Event`**

```php
// app/Models/Event.php — add method
public function ticketTypes(): HasMany
{
    return $this->hasMany(TicketType::class)->orderBy('sort_order');
}
```

- [ ] **Step 6: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name_ar' => $this->faker->word(),
            'name_en' => $this->faker->word(),
            'description_ar' => $this->faker->sentence(),
            'description_en' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(200, 2000),
            'currency' => 'SAR',
            'workshop_slot_count' => $this->faker->randomElement([0, 1, 2]),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
```

- [ ] **Step 7: Run migrations and tests**

Run: `php artisan migrate`
Run: `php artisan test --filter=Unit/Models/TicketTypeTest`
Expected: PASS (2 passed)

- [ ] **Step 8: Commit**

```bash
git add database/migrations database/factories/TicketTypeFactory.php app/Models/TicketType.php app/Models/Event.php tests/Unit/Models/TicketTypeTest.php
git commit -m "feat: add TicketType model"
```

### Task 8: Workshops

**Files:**
- Create: `database/migrations/xxxx_create_workshops_table.php`
- Create: `app/Models/Workshop.php`
- Create: `database/factories/WorkshopFactory.php`
- Modify: `app/Models/Event.php` (add `workshops()` relationship)
- Test: `tests/Unit/Models/WorkshopTest.php`

**Interfaces:**
- Consumes: `Event` model (Task 4), `Speaker` model (Task 5).
- Produces: `Workshop` model (`belongsTo(Event::class)`, `belongsTo(Speaker::class)` nullable, `slug` route key), `Event::workshops()` (`hasMany`), factory `Workshop::factory()`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Speaker;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopTest extends TestCase
{
    use RefreshDatabase;

    public function test_workshop_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $workshop = Workshop::factory()->for($event)->create();

        $this->assertTrue($workshop->event->is($event));
    }

    public function test_workshop_can_have_a_speaker(): void
    {
        $speaker = Speaker::factory()->create();
        $workshop = Workshop::factory()->create(['speaker_id' => $speaker->id]);

        $this->assertTrue($workshop->speaker->is($speaker));
    }

    public function test_workshop_uses_slug_as_route_key(): void
    {
        $workshop = Workshop::factory()->create(['slug' => 'ai-content-workshop']);

        $this->assertSame('slug', $workshop->getRouteKeyName());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Unit/Models/WorkshopTest`
Expected: FAIL (class `App\Models\Workshop` not found)

- [ ] **Step 3: Create the migration**

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
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('capacity')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
```

- [ ] **Step 4: Create the `Workshop` model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkshopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'speaker_id', 'slug', 'name_ar', 'name_en',
        'description_ar', 'description_en', 'capacity', 'sort_order',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    protected static function newFactory(): WorkshopFactory
    {
        return WorkshopFactory::new();
    }
}
```

- [ ] **Step 5: Add the inverse relationship to `Event`**

```php
// app/Models/Event.php — add method
public function workshops(): HasMany
{
    return $this->hasMany(Workshop::class)->orderBy('sort_order');
}
```

- [ ] **Step 6: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'speaker_id' => null,
            'slug' => $this->faker->unique()->slug(3),
            'name_ar' => $this->faker->sentence(3),
            'name_en' => $this->faker->sentence(3),
            'description_ar' => $this->faker->paragraph(),
            'description_en' => $this->faker->paragraph(),
            'capacity' => $this->faker->numberBetween(10, 50),
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 7: Run migrations and tests**

Run: `php artisan migrate`
Run: `php artisan test --filter=Unit/Models/WorkshopTest`
Expected: PASS (3 passed)

- [ ] **Step 8: Commit**

```bash
git add database/migrations database/factories/WorkshopFactory.php app/Models/Workshop.php app/Models/Event.php tests/Unit/Models/WorkshopTest.php
git commit -m "feat: add Workshop model"
```

### Task 9: Agenda Items

**Files:**
- Create: `database/migrations/xxxx_create_agenda_items_table.php`
- Create: `app/Models/AgendaItem.php`
- Create: `app/Enums/AgendaItemType.php`
- Create: `database/factories/AgendaItemFactory.php`
- Modify: `app/Models/Event.php` (add `agendaItems()` relationship)
- Test: `tests/Unit/Models/AgendaItemTest.php`

**Interfaces:**
- Consumes: `Event` (Task 4), `Speaker` (Task 5), `Workshop` (Task 8).
- Produces: `AgendaItem` model (`belongsTo(Event::class)`, nullable `belongsTo(Speaker::class)`, nullable `belongsTo(Workshop::class)`), `AgendaItemType` enum, `Event::agendaItems()` (`hasMany`), factory `AgendaItem::factory()`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\AgendaItemType;
use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_item_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $item = AgendaItem::factory()->for($event)->create();

        $this->assertTrue($item->event->is($event));
    }

    public function test_agenda_item_type_casts_to_enum(): void
    {
        $item = AgendaItem::factory()->create(['type' => AgendaItemType::Keynote]);

        $this->assertSame(AgendaItemType::Keynote, $item->type);
    }

    public function test_agenda_item_can_link_to_a_workshop(): void
    {
        $workshop = Workshop::factory()->create();
        $item = AgendaItem::factory()->create([
            'workshop_id' => $workshop->id,
            'type' => AgendaItemType::WorkshopSession,
        ]);

        $this->assertTrue($item->workshop->is($workshop));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Unit/Models/AgendaItemTest`
Expected: FAIL (class `App\Models\AgendaItem` not found)

- [ ] **Step 3: Create the `AgendaItemType` enum**

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum AgendaItemType: string
{
    case Keynote = 'keynote';
    case Session = 'session';
    case WorkshopSession = 'workshop';
    case Break = 'break';
    case Panel = 'panel';
}
```

- [ ] **Step 4: Create the migration**

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
        Schema::create('agenda_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workshop_id')->nullable()->constrained()->nullOnDelete();
            $table->date('day_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('title_ar');
            $table->string('title_en');
            $table->string('type')->default('session');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_items');
    }
};
```

- [ ] **Step 5: Create the `AgendaItem` model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AgendaItemType;
use Database\Factories\AgendaItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'speaker_id', 'workshop_id', 'day_date', 'start_time', 'end_time',
        'title_ar', 'title_en', 'type', 'sort_order',
    ];

    protected $casts = [
        'day_date' => 'date',
        'type' => AgendaItemType::class,
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    protected static function newFactory(): AgendaItemFactory
    {
        return AgendaItemFactory::new();
    }
}
```

- [ ] **Step 6: Add the inverse relationship to `Event`**

```php
// app/Models/Event.php — add method
public function agendaItems(): HasMany
{
    return $this->hasMany(AgendaItem::class)->orderBy('day_date')->orderBy('start_time');
}
```

- [ ] **Step 7: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AgendaItemType;
use App\Models\AgendaItem;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgendaItemFactory extends Factory
{
    protected $model = AgendaItem::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'speaker_id' => null,
            'workshop_id' => null,
            'day_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'title_ar' => $this->faker->sentence(3),
            'title_en' => $this->faker->sentence(3),
            'type' => AgendaItemType::Session,
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 8: Run migrations and tests**

Run: `php artisan migrate`
Run: `php artisan test --filter=Unit/Models/AgendaItemTest`
Expected: PASS (3 passed)

- [ ] **Step 9: Commit**

```bash
git add database/migrations database/factories/AgendaItemFactory.php app/Models/AgendaItem.php app/Enums/AgendaItemType.php app/Models/Event.php tests/Unit/Models/AgendaItemTest.php
git commit -m "feat: add AgendaItem model"
```

### Task 10: FAQs

**Files:**
- Create: `database/migrations/xxxx_create_faqs_table.php`
- Create: `app/Models/Faq.php`
- Create: `database/factories/FaqFactory.php`
- Modify: `app/Models/Event.php` (add `faqs()` relationship)
- Test: `tests/Unit/Models/FaqTest.php`

**Interfaces:**
- Consumes: `Event` model (Task 4).
- Produces: `Faq` model (`belongsTo(Event::class)`), `Event::faqs()` (`hasMany`), factory `Faq::factory()`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $faq = Faq::factory()->for($event)->create();

        $this->assertTrue($faq->event->is($event));
    }

    public function test_event_has_many_faqs_ordered_by_sort_order(): void
    {
        $event = Event::factory()->create();
        Faq::factory()->for($event)->create(['sort_order' => 2, 'question_en' => 'Second']);
        Faq::factory()->for($event)->create(['sort_order' => 1, 'question_en' => 'First']);

        $this->assertSame('First', $event->faqs->first()->question_en);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Unit/Models/FaqTest`
Expected: FAIL (class `App\Models\Faq` not found)

- [ ] **Step 3: Create the migration**

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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('question_ar');
            $table->string('question_en');
            $table->text('answer_ar');
            $table->text('answer_en');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
```

- [ ] **Step 4: Create the `Faq` model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'question_ar', 'question_en', 'answer_ar', 'answer_en', 'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): FaqFactory
    {
        return FaqFactory::new();
    }
}
```

- [ ] **Step 5: Add the inverse relationship to `Event`**

```php
// app/Models/Event.php — add method
public function faqs(): HasMany
{
    return $this->hasMany(Faq::class)->orderBy('sort_order');
}
```

- [ ] **Step 6: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'question_ar' => $this->faker->sentence() . '؟',
            'question_en' => $this->faker->sentence() . '?',
            'answer_ar' => $this->faker->paragraph(),
            'answer_en' => $this->faker->paragraph(),
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 7: Run migrations and tests**

Run: `php artisan migrate`
Run: `php artisan test --filter=Unit/Models/FaqTest`
Expected: PASS (2 passed)

- [ ] **Step 8: Commit**

```bash
git add database/migrations database/factories/FaqFactory.php app/Models/Faq.php app/Models/Event.php tests/Unit/Models/FaqTest.php
git commit -m "feat: add Faq model"
```

### Task 11: Landing Page Content (Hero / About / Location / Awards-teaser text)

**Files:**
- Create: `database/migrations/xxxx_create_landing_page_content_table.php`
- Create: `app/Models/LandingPageContent.php`
- Create: `app/Enums/LandingPageSection.php`
- Create: `database/factories/LandingPageContentFactory.php`
- Modify: `app/Models/Event.php` (add `landingPageContent()` relationship)
- Test: `tests/Unit/Models/LandingPageContentTest.php`

**Interfaces:**
- Consumes: `Event` model (Task 4).
- Produces: `LandingPageContent` model (`belongsTo(Event::class)`), `LandingPageSection` enum (`Hero`, `About`, `Location`, `AwardsTeaser`), `Event::landingPageContent()` (`hasMany`). Public page tasks in Phase C read this via a helper (added in this task): `Event::contentFor(LandingPageSection $section, string $fieldKey): ?LandingPageContent`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\LandingPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $content = LandingPageContent::factory()->for($event)->create();

        $this->assertTrue($content->event->is($event));
    }

    public function test_event_can_look_up_a_specific_field(): void
    {
        $event = Event::factory()->create();
        LandingPageContent::factory()->for($event)->create([
            'section' => LandingPageSection::Hero,
            'field_key' => 'headline',
            'value_ar' => 'أثر يتوالى',
            'value_en' => 'Impact that continues',
        ]);

        $field = $event->contentFor(LandingPageSection::Hero, 'headline');

        $this->assertNotNull($field);
        $this->assertSame('Impact that continues', $field->value_en);
    }

    public function test_content_for_returns_null_when_missing(): void
    {
        $event = Event::factory()->create();

        $this->assertNull($event->contentFor(LandingPageSection::About, 'body'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Unit/Models/LandingPageContentTest`
Expected: FAIL (class `App\Models\LandingPageContent` not found)

- [ ] **Step 3: Create the `LandingPageSection` enum**

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum LandingPageSection: string
{
    case Hero = 'hero';
    case About = 'about';
    case Location = 'location';
    case AwardsTeaser = 'awards_teaser';
}
```

- [ ] **Step 4: Create the migration**

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
        Schema::create('landing_page_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('section');
            $table->string('field_key');
            $table->text('value_ar')->nullable();
            $table->text('value_en')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'section', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_content');
    }
};
```

- [ ] **Step 5: Create the `LandingPageContent` model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LandingPageSection;
use Database\Factories\LandingPageContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageContent extends Model
{
    use HasFactory;

    protected $table = 'landing_page_content';

    protected $fillable = [
        'event_id', 'section', 'field_key', 'value_ar', 'value_en',
    ];

    protected $casts = [
        'section' => LandingPageSection::class,
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): LandingPageContentFactory
    {
        return LandingPageContentFactory::new();
    }
}
```

- [ ] **Step 6: Add the relationship and lookup helper to `Event`**

```php
// app/Models/Event.php — add import + methods
use App\Enums\LandingPageSection;
// ...
public function landingPageContent(): HasMany
{
    return $this->hasMany(LandingPageContent::class);
}

public function contentFor(LandingPageSection $section, string $fieldKey): ?LandingPageContent
{
    return $this->landingPageContent
        ->first(fn (LandingPageContent $content) => $content->section === $section && $content->field_key === $fieldKey);
}
```

- [ ] **Step 7: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\LandingPageContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class LandingPageContentFactory extends Factory
{
    protected $model = LandingPageContent::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'section' => LandingPageSection::About,
            'field_key' => 'body',
            'value_ar' => $this->faker->paragraph(),
            'value_en' => $this->faker->paragraph(),
        ];
    }
}
```

- [ ] **Step 8: Run migrations and tests**

Run: `php artisan migrate`
Run: `php artisan test --filter=Unit/Models/LandingPageContentTest`
Expected: PASS (3 passed)

- [ ] **Step 9: Commit**

```bash
git add database/migrations database/factories/LandingPageContentFactory.php app/Models/LandingPageContent.php app/Enums/LandingPageSection.php app/Models/Event.php tests/Unit/Models/LandingPageContentTest.php
git commit -m "feat: add LandingPageContent model"
```

---

## Phase B.1 — Demo data

### Task 12: CCS reference event seeder

**Files:**
- Create: `database/seeders/CcsEventSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/CcsEventSeederTest.php`

**Interfaces:**
- Consumes: every model from Phase B.
- Produces: a `ccs-2026` `Event` row with realistic bilingual content — every Phase C public-page task and Phase D admin-CRUD task is manually verified against this seeded data.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CcsEventSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_ccs_event_with_related_content(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CcsEventSeeder']);

        $event = Event::where('slug', 'ccs-2026')->firstOrFail();

        $this->assertGreaterThanOrEqual(3, $event->speakers()->count());
        $this->assertGreaterThanOrEqual(2, $event->sponsors()->count());
        $this->assertSame(4, $event->ticketTypes()->count());
        $this->assertGreaterThanOrEqual(2, $event->workshops()->count());
        $this->assertGreaterThanOrEqual(1, $event->agendaItems()->count());
        $this->assertGreaterThanOrEqual(3, $event->faqs()->count());
        $this->assertNotNull($event->landingPageContent()->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CcsEventSeederTest`
Expected: FAIL (class `Database\Seeders\CcsEventSeeder` not found)

- [ ] **Step 3: Create the seeder**

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AgendaItemType;
use App\Enums\EventStatus;
use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\TicketType;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class CcsEventSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::create([
            'slug' => 'ccs-2026',
            'name_ar' => 'قمة صناع المحتوى',
            'name_en' => 'Content Creators Summit',
            'tagline_ar' => 'أثر يتوالى',
            'tagline_en' => 'Impact that continues',
            'start_date' => '2026-08-15',
            'end_date' => '2026-08-16',
            'venue_name_ar' => 'مركز المؤتمرات',
            'venue_name_en' => 'Convention Center',
            'venue_address_ar' => 'الرياض، المملكة العربية السعودية',
            'venue_address_en' => 'Riyadh, Saudi Arabia',
            'map_embed_url' => 'https://maps.google.com/?q=Riyadh',
            'status' => EventStatus::Published,
        ]);

        $speakerKareem = Speaker::create([
            'event_id' => $event->id,
            'name_ar' => 'كريم السيد',
            'name_en' => 'Kareem Al-Sayed',
            'title_ar' => 'صانع محتوى',
            'title_en' => 'Content Creator',
            'bio_ar' => 'صانع محتوى رقمي بخبرة تتجاوز عشر سنوات.',
            'bio_en' => 'Digital content creator with over a decade of experience.',
            'sort_order' => 1,
        ]);
        Speaker::factory()->for($event)->count(2)->create();

        Sponsor::create([
            'event_id' => $event->id,
            'name_ar' => 'الراعي الذهبي',
            'name_en' => 'Golden Sponsor Co.',
            'tier' => 'gold',
            'sort_order' => 1,
        ]);
        Sponsor::factory()->for($event)->create(['tier' => 'silver']);

        $ticketSlots = [
            ['name_en' => 'General', 'name_ar' => 'عام', 'slots' => 0, 'price' => 300],
            ['name_en' => 'VIP', 'name_ar' => 'كبار الشخصيات', 'slots' => 1, 'price' => 700],
            ['name_en' => 'Premium', 'name_ar' => 'مميز', 'slots' => 2, 'price' => 1200],
            ['name_en' => 'Platinum', 'name_ar' => 'بلاتيني', 'slots' => null, 'price' => 2500],
        ];
        foreach ($ticketSlots as $i => $tier) {
            TicketType::create([
                'event_id' => $event->id,
                'name_ar' => $tier['name_ar'],
                'name_en' => $tier['name_en'],
                'description_ar' => 'وصف الباقة',
                'description_en' => $tier['name_en'] . ' ticket tier',
                'price' => $tier['price'],
                'currency' => 'SAR',
                'workshop_slot_count' => $tier['slots'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        $workshop = Workshop::create([
            'event_id' => $event->id,
            'speaker_id' => $speakerKareem->id,
            'slug' => 'ai-content-workshop',
            'name_ar' => 'ورشة صناعة المحتوى بالذكاء الاصطناعي',
            'name_en' => 'AI-Powered Content Creation Workshop',
            'description_ar' => 'تعلم كيفية استخدام أدوات الذكاء الاصطناعي في صناعة المحتوى.',
            'description_en' => 'Learn how to use AI tools in content creation.',
            'capacity' => 40,
            'sort_order' => 1,
        ]);
        Workshop::factory()->for($event)->create();

        \App\Models\AgendaItem::create([
            'event_id' => $event->id,
            'speaker_id' => $speakerKareem->id,
            'workshop_id' => $workshop->id,
            'day_date' => '2026-08-15',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'title_ar' => $workshop->name_ar,
            'title_en' => $workshop->name_en,
            'type' => AgendaItemType::WorkshopSession,
            'sort_order' => 1,
        ]);

        $faqs = [
            [
                'question_ar' => 'كيف أحصل على تذكرة؟',
                'question_en' => 'How do I get a ticket?',
                'answer_ar' => 'قدّم طلبك من صفحة التذاكر وسيتم مراجعته من قبل الإدارة.',
                'answer_en' => 'Submit a request from the Tickets section; it will be reviewed by the admin team.',
            ],
            [
                'question_ar' => 'متى أدفع؟',
                'question_en' => 'When do I pay?',
                'answer_ar' => 'بعد الموافقة على طلبك، ستصلك رسالة تحتوي على رابط الدفع.',
                'answer_en' => 'After your request is approved, you will receive an email with a payment link.',
            ],
            [
                'question_ar' => 'كيف أختار ورش العمل؟',
                'question_en' => 'How do I choose my workshops?',
                'answer_ar' => 'بعد إصدار التذكرة، استخدم معرّف التذكرة ومفتاح الحجز لاختيار ورش العمل.',
                'answer_en' => 'Once your ticket is issued, use your Ticket ID and Booking Key to pick your workshops.',
            ],
        ];
        foreach ($faqs as $i => $faq) {
            Faq::create([...$faq, 'event_id' => $event->id, 'sort_order' => $i]);
        }

        $content = [
            [LandingPageSection::Hero, 'headline', 'قمة صناع المحتوى ٢٠٢٦', 'Content Creators Summit 2026'],
            [LandingPageSection::About, 'body', 'قمة صناع المحتوى هي ملتقى محترفي المحتوى الرقمي.', 'Content Creators Summit is where digital content professionals meet.'],
            [LandingPageSection::Location, 'intro', 'يقام الحدث في مركز المؤتمرات بالرياض.', 'The event takes place at the Convention Center in Riyadh.'],
            [LandingPageSection::AwardsTeaser, 'blurb', 'صوّت لصانع المحتوى المفضل لديك قريبًا.', 'Vote for your favorite content creator — coming soon.'],
        ];
        foreach ($content as [$section, $key, $ar, $en]) {
            \App\Models\LandingPageContent::create([
                'event_id' => $event->id,
                'section' => $section,
                'field_key' => $key,
                'value_ar' => $ar,
                'value_en' => $en,
            ]);
        }
    }
}
```

- [ ] **Step 4: Register it in `DatabaseSeeder`**

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    $this->call([
        AdminUserSeeder::class,
        CcsEventSeeder::class,
    ]);
}
```

- [ ] **Step 5: Run the test**

Run: `php artisan test --filter=CcsEventSeederTest`
Expected: PASS (1 passed)

- [ ] **Step 6: Seed the local database for manual verification**

Run: `php artisan migrate:fresh --seed`
Expected: completes without error; `SELECT * FROM events WHERE slug = 'ccs-2026';` returns one row.

- [ ] **Step 7: Commit**

```bash
git add database/seeders tests/Feature/CcsEventSeederTest.php
git commit -m "feat: add CCS reference event seeder"
```

---

## Phase C — Public pages

### Task 13: Landing page shell + Hero + About sections

**Files:**
- Create: `app/Http/Controllers/LandingPageController.php`
- Create: `resources/views/landing/show.blade.php`
- Create: `resources/views/landing/partials/hero.blade.php`
- Create: `resources/views/landing/partials/about.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/LandingPageTest.php`

**Interfaces:**
- Consumes: `Event` and its relationships (Phase B), seeded `ccs-2026` event (Task 12).
- Produces: route `landing.show` (`GET /events/{event}`) rendering `landing.show`, which `@include`s section partials — Task 14 adds the remaining `@include` lines to this same file.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\LandingPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_for_an_event(): void
    {
        $event = Event::factory()->create([
            'slug' => 'ccs-2026',
            'name_en' => 'Content Creators Summit',
            'status' => EventStatus::Published,
        ]);

        $response = $this->get(route('landing.show', $event));

        $response->assertStatus(200);
        $response->assertSee('Content Creators Summit');
    }

    public function test_landing_page_shows_about_content_when_present(): void
    {
        $event = Event::factory()->create();
        LandingPageContent::factory()->for($event)->create([
            'section' => LandingPageSection::About,
            'field_key' => 'body',
            'value_en' => 'Where digital creators meet.',
        ]);

        $response = $this->get(route('landing.show', $event));

        $response->assertSee('Where digital creators meet.');
    }

    public function test_about_section_omitted_when_no_content(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('landing.show', $event));

        $response->assertDontSee('id="about"', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LandingPageTest`
Expected: FAIL (route `landing.show` not defined)

- [ ] **Step 3: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function show(Event $event): View
    {
        $event->load([
            'speakers', 'sponsors', 'ticketTypes', 'workshops',
            'agendaItems', 'faqs', 'landingPageContent',
        ]);

        return view('landing.show', ['event' => $event]);
    }
}
```

- [ ] **Step 4: Register the route**

```php
// routes/web.php
use App\Http\Controllers\LandingPageController;

Route::get('/events/{event}', [LandingPageController::class, 'show'])->name('landing.show');
```

- [ ] **Step 5: Create the Hero partial**

```blade
{{-- resources/views/landing/partials/hero.blade.php --}}
<section id="hero" class="ccs-hero flex flex-col items-center justify-center text-center text-white" style="min-height: 70vh;">
    <p class="uppercase text-sm">CCS &middot; {{ $event->name_ar }}</p>
    <h1 class="text-5xl font-bold">{{ app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en }}</h1>
    <p class="text-lg" x-data="{ now: Date.now(), target: new Date('{{ $event->start_date->toDateString() }}').getTime() }"
       x-init="setInterval(() => now = Date.now(), 1000)">
        <span x-text="Math.max(0, Math.floor((target - now) / 86400000))"></span>
        {{ __('days to go') }}
    </p>
    <p>{{ app()->getLocale() === 'ar' ? $event->venue_name_ar : $event->venue_name_en }}</p>
    <a href="#tickets" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Request Your Ticket') }}</a>
</section>
```

- [ ] **Step 6: Create the About partial**

```blade
{{-- resources/views/landing/partials/about.blade.php --}}
@php $about = $event->contentFor(\App\Enums\LandingPageSection::About, 'body'); @endphp
@if($about)
    <section id="about" class="container mx-auto px-4 py-5">
        <h2>{{ __('About the Event') }}</h2>
        <p>{{ app()->getLocale() === 'ar' ? $about->value_ar : $about->value_en }}</p>
    </section>
@endif
```

- [ ] **Step 7: Create the page shell**

```blade
{{-- resources/views/landing/show.blade.php --}}
@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en)

@section('content')
    @include('landing.partials.hero', ['event' => $event])
    @include('landing.partials.about', ['event' => $event])
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=LandingPageTest`
Expected: PASS (3 passed)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/LandingPageController.php resources/views/landing routes/web.php tests/Feature/LandingPageTest.php
git commit -m "feat: landing page shell with Hero and About sections"
```

### Task 14: Speakers, Partners, FAQ, and Location sections

These four sections don't link out to any other page, so they can land independently of Tasks 15–18 below.

**Files:**
- Create: `resources/views/landing/partials/speakers.blade.php`
- Create: `resources/views/landing/partials/partners.blade.php`
- Create: `resources/views/landing/partials/faq.blade.php`
- Create: `resources/views/landing/partials/location.blade.php`
- Modify: `resources/views/landing/show.blade.php` (add the four `@include`s)
- Test: `tests/Feature/LandingPageTest.php` (append cases)

**Interfaces:**
- Consumes: `$event->speakers`, `$event->sponsors`, `$event->faqs`, `$event->contentFor(LandingPageSection::Location, 'intro')` (Phase B).

- [ ] **Step 1: Append the failing tests**

```php
// tests/Feature/LandingPageTest.php — add methods to the existing class

public function test_speakers_section_lists_speaker_names(): void
{
    $event = Event::factory()->create();
    \App\Models\Speaker::factory()->for($event)->create(['name_en' => 'Jane Creator']);

    $response = $this->get(route('landing.show', $event));

    $response->assertSee('Jane Creator');
}

public function test_partners_section_omitted_when_no_sponsors(): void
{
    $event = Event::factory()->create();

    $response = $this->get(route('landing.show', $event));

    $response->assertDontSee('id="partners"', false);
}

public function test_faq_section_lists_questions(): void
{
    $event = Event::factory()->create();
    \App\Models\Faq::factory()->for($event)->create(['question_en' => 'How do I pay?']);

    $response = $this->get(route('landing.show', $event));

    $response->assertSee('How do I pay?');
}

public function test_location_section_shows_venue_intro(): void
{
    $event = Event::factory()->create();
    LandingPageContent::factory()->for($event)->create([
        'section' => LandingPageSection::Location,
        'field_key' => 'intro',
        'value_en' => 'Held at the Convention Center.',
    ]);

    $response = $this->get(route('landing.show', $event));

    $response->assertSee('Held at the Convention Center.');
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=LandingPageTest`
Expected: FAIL (new assertions don't find the text — partials don't exist yet)

- [ ] **Step 3: Create the Speakers partial**

```blade
{{-- resources/views/landing/partials/speakers.blade.php --}}
@if($event->speakers->isNotEmpty())
    <section id="speakers" class="container mx-auto px-4 py-5">
        <h2>{{ __('Our Speakers') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach($event->speakers as $speaker)
                <div class="mb-4" x-data="{ open: false }">
                    <img src="{{ $speaker->photo_path ?? '/images/placeholder-speaker.png' }}" class="w-full h-auto rounded" alt="">
                    <h5 class="mt-2 font-semibold">{{ app()->getLocale() === 'ar' ? $speaker->name_ar : $speaker->name_en }}</h5>
                    <p class="text-gray-400">{{ app()->getLocale() === 'ar' ? $speaker->title_ar : $speaker->title_en }}</p>
                    <button type="button" class="border border-white text-white px-3 py-1.5 rounded text-sm hover:bg-white hover:text-ccs-black" @click="open = !open">{{ __('Bio') }}</button>
                    <p x-show="open" x-cloak>{{ app()->getLocale() === 'ar' ? $speaker->bio_ar : $speaker->bio_en }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endif
```

- [ ] **Step 4: Create the Partners partial**

```blade
{{-- resources/views/landing/partials/partners.blade.php --}}
@if($event->sponsors->isNotEmpty())
    <section id="partners" class="container mx-auto px-4 py-5">
        <h2>{{ __('Partners') }}</h2>
        @foreach($event->sponsors->groupBy('tier') as $tier => $sponsors)
            <h6 class="uppercase text-gray-400">{{ $tier }}</h6>
            <div class="flex flex-wrap gap-4 mb-4">
                @foreach($sponsors as $sponsor)
                    <img src="{{ $sponsor->logo_path ?? '/images/placeholder-logo.png' }}" alt="{{ $sponsor->name_en }}" style="height:48px;">
                @endforeach
            </div>
        @endforeach
    </section>
@endif
```

- [ ] **Step 5: Create the FAQ partial**

```blade
{{-- resources/views/landing/partials/faq.blade.php --}}
@if($event->faqs->isNotEmpty())
    <section id="faq" class="container mx-auto px-4 py-5">
        <h2>{{ __('FAQ') }}</h2>
        <div class="divide-y divide-gray-700">
            @foreach($event->faqs as $faq)
                <div x-data="{ open: false }">
                    <button type="button" class="w-full text-left py-3 font-semibold" @click="open = !open">
                        {{ app()->getLocale() === 'ar' ? $faq->question_ar : $faq->question_en }}
                    </button>
                    <div x-show="open" x-cloak class="pb-3 text-gray-400">
                        {{ app()->getLocale() === 'ar' ? $faq->answer_ar : $faq->answer_en }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
```

- [ ] **Step 6: Create the Location partial**

```blade
{{-- resources/views/landing/partials/location.blade.php --}}
@php $intro = $event->contentFor(\App\Enums\LandingPageSection::Location, 'intro'); @endphp
@if($intro || $event->venue_address_en)
    <section id="location" class="container mx-auto px-4 py-5">
        <h2>{{ __('Location') }}</h2>
        @if($intro)
            <p>{{ app()->getLocale() === 'ar' ? $intro->value_ar : $intro->value_en }}</p>
        @endif
        <p>{{ app()->getLocale() === 'ar' ? $event->venue_address_ar : $event->venue_address_en }}</p>
        @if($event->map_embed_url)
            <iframe src="{{ $event->map_embed_url }}" width="100%" height="300" style="border:0;" loading="lazy"></iframe>
        @endif
    </section>
@endif
```

- [ ] **Step 7: Include the four partials in the page shell**

```blade
{{-- resources/views/landing/show.blade.php --}}
@section('content')
    @include('landing.partials.hero', ['event' => $event])
    @include('landing.partials.about', ['event' => $event])
    @include('landing.partials.speakers', ['event' => $event])
    @include('landing.partials.partners', ['event' => $event])
    @include('landing.partials.faq', ['event' => $event])
    @include('landing.partials.location', ['event' => $event])
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=LandingPageTest`
Expected: PASS (7 passed)

- [ ] **Step 9: Commit**

```bash
git add resources/views/landing tests/Feature/LandingPageTest.php
git commit -m "feat: add Speakers, Partners, FAQ, Location sections to landing page"
```

### Task 15: Workshops index + detail pages

**Files:**
- Create: `app/Http/Controllers/WorkshopController.php`
- Create: `resources/views/workshops/index.blade.php`
- Create: `resources/views/workshops/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/WorkshopPagesTest.php`

**Interfaces:**
- Consumes: `Event`, `Workshop`, `Speaker` (Phase B).
- Produces: named routes `workshops.index` (`GET /events/{event}/workshops`) and `workshops.show` (`GET /events/{event}/workshops/{workshop}`) — Task 19 links the landing page's Workshops teaser to `workshops.index`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Speaker;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_workshops_for_the_event(): void
    {
        $event = Event::factory()->create();
        Workshop::factory()->for($event)->create(['name_en' => 'AI Content Workshop']);

        $response = $this->get(route('workshops.index', $event));

        $response->assertStatus(200);
        $response->assertSee('AI Content Workshop');
    }

    public function test_show_displays_workshop_detail_with_speaker(): void
    {
        $event = Event::factory()->create();
        $speaker = Speaker::factory()->create(['name_en' => 'Jane Creator']);
        $workshop = Workshop::factory()->for($event)->create([
            'speaker_id' => $speaker->id,
            'name_en' => 'AI Content Workshop',
            'slug' => 'ai-content-workshop',
        ]);

        $response = $this->get(route('workshops.show', [$event, $workshop]));

        $response->assertStatus(200);
        $response->assertSee('AI Content Workshop');
        $response->assertSee('Jane Creator');
    }

    public function test_show_returns_404_for_workshop_from_another_event(): void
    {
        $event = Event::factory()->create();
        $otherEventWorkshop = Workshop::factory()->create();

        $response = $this->get(route('workshops.show', [$event, $otherEventWorkshop]));

        $response->assertStatus(404);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=WorkshopPagesTest`
Expected: FAIL (route `workshops.index` not defined)

- [ ] **Step 3: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Workshop;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorkshopController extends Controller
{
    public function index(Event $event): View
    {
        return view('workshops.index', [
            'event' => $event,
            'workshops' => $event->workshops()->with('speaker')->get(),
        ]);
    }

    public function show(Event $event, Workshop $workshop): View
    {
        if ($workshop->event_id !== $event->id) {
            throw new NotFoundHttpException();
        }

        $workshop->load('speaker');

        return view('workshops.show', ['event' => $event, 'workshop' => $workshop]);
    }
}
```

- [ ] **Step 4: Register the routes**

```php
// routes/web.php
use App\Http\Controllers\WorkshopController;

Route::get('/events/{event}/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/events/{event}/workshops/{workshop}', [WorkshopController::class, 'show'])->name('workshops.show');
```

- [ ] **Step 5: Create the index view**

```blade
{{-- resources/views/workshops/index.blade.php --}}
@extends('layouts.app')

@section('title', __('Workshops'))

@section('content')
    <div class="container mx-auto px-4 py-5">
        <h1>{{ __('Workshops') }}</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($workshops as $workshop)
                <div class="mb-4">
                    <div class="bg-gray-900 rounded-lg shadow overflow-hidden text-white h-full">
                        <div class="p-4">
                            <h5 class="font-semibold">{{ app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en }}</h5>
                            <p class="text-gray-400 text-sm">{{ Str::limit(app()->getLocale() === 'ar' ? $workshop->description_ar : $workshop->description_en, 100) }}</p>
                            <a href="{{ route('workshops.show', [$event, $workshop]) }}" class="border border-white text-white px-3 py-1.5 rounded text-sm hover:bg-white hover:text-ccs-black">{{ __('Details') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
```

- [ ] **Step 6: Create the detail view**

```blade
{{-- resources/views/workshops/show.blade.php --}}
@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en)

@section('content')
    <div class="container mx-auto px-4 py-5">
        <a href="{{ route('workshops.index', $event) }}">&larr; {{ __('All Workshops') }}</a>
        <h1>{{ app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en }}</h1>
        <p>{{ app()->getLocale() === 'ar' ? $workshop->description_ar : $workshop->description_en }}</p>
        <p><strong>{{ __('Capacity') }}:</strong> {{ $workshop->capacity }}</p>
        @if($workshop->speaker)
            <p><strong>{{ __('Speaker') }}:</strong> {{ app()->getLocale() === 'ar' ? $workshop->speaker->name_ar : $workshop->speaker->name_en }}</p>
        @endif
    </div>
@endsection
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --filter=WorkshopPagesTest`
Expected: PASS (3 passed)

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/WorkshopController.php resources/views/workshops routes/web.php tests/Feature/WorkshopPagesTest.php
git commit -m "feat: workshops index and detail pages"
```

### Task 16: Agenda page

**Files:**
- Create: `app/Http/Controllers/AgendaController.php`
- Create: `resources/views/agenda/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/AgendaPageTest.php`

**Interfaces:**
- Consumes: `Event`, `AgendaItem` (Phase B).
- Produces: named route `agenda.show` (`GET /events/{event}/agenda`) — Task 19 links the landing page's Agenda teaser to it.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_page_lists_items_grouped_by_day(): void
    {
        $event = Event::factory()->create();
        AgendaItem::factory()->for($event)->create([
            'title_en' => 'Opening Keynote',
            'day_date' => '2026-08-15',
        ]);

        $response = $this->get(route('agenda.show', $event));

        $response->assertStatus(200);
        $response->assertSee('Opening Keynote');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AgendaPageTest`
Expected: FAIL (route `agenda.show` not defined)

- [ ] **Step 3: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function show(Event $event): View
    {
        $itemsByDay = $event->agendaItems()->with(['speaker', 'workshop'])->get()->groupBy(
            fn ($item) => $item->day_date->toDateString()
        );

        return view('agenda.show', ['event' => $event, 'itemsByDay' => $itemsByDay]);
    }
}
```

- [ ] **Step 4: Register the route**

```php
// routes/web.php
use App\Http\Controllers\AgendaController;

Route::get('/events/{event}/agenda', [AgendaController::class, 'show'])->name('agenda.show');
```

- [ ] **Step 5: Create the view**

```blade
{{-- resources/views/agenda/show.blade.php --}}
@extends('layouts.app')

@section('title', __('Agenda'))

@section('content')
    <div class="container mx-auto px-4 py-5">
        <h1>{{ __('Agenda') }}</h1>
        @foreach($itemsByDay as $day => $items)
            <h4 class="mt-4">{{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('l, F j') }}</h4>
            <ul class="divide-y divide-gray-700 mb-4">
                @foreach($items as $item)
                    <li class="py-3 flex justify-between text-white">
                        <span>{{ $item->start_time->format('H:i') }}–{{ $item->end_time->format('H:i') }}</span>
                        <span>{{ app()->getLocale() === 'ar' ? $item->title_ar : $item->title_en }}</span>
                        <span class="inline-block px-2 py-1 text-xs rounded bg-gray-700 text-white">{{ $item->type->value }}</span>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
@endsection
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=AgendaPageTest`
Expected: PASS (1 passed)

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/AgendaController.php resources/views/agenda routes/web.php tests/Feature/AgendaPageTest.php
git commit -m "feat: agenda page"
```

### Task 17: Ticket Request form page (UI only)

Submission handling (persisting a request, admin approval workflow) is a separate future spec (`.claude/plans/05-ticket-request.md`). This task only renders the form, pre-selecting the ticket type the visitor came from.

**Files:**
- Create: `app/Http/Controllers/TicketRequestController.php`
- Create: `resources/views/ticket-requests/create.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/TicketRequestPageTest.php`

**Interfaces:**
- Consumes: `Event`, `TicketType` (Phase B).
- Produces: named route `ticket-requests.create` (`GET /events/{event}/request`) — Task 19 links the landing page's Tickets section CTA to it.

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketRequestPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_renders_with_all_ticket_types(): void
    {
        $event = Event::factory()->create();
        TicketType::factory()->for($event)->create(['name_en' => 'VIP']);

        $response = $this->get(route('ticket-requests.create', $event));

        $response->assertStatus(200);
        $response->assertSee('VIP');
    }

    public function test_form_preselects_ticket_type_from_query_string(): void
    {
        $event = Event::factory()->create();
        $vip = TicketType::factory()->for($event)->create(['name_en' => 'VIP']);

        $response = $this->get(route('ticket-requests.create', $event) . '?type=' . $vip->id);

        $response->assertStatus(200);
        $response->assertSee('selected', false);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=TicketRequestPageTest`
Expected: FAIL (route `ticket-requests.create` not defined)

- [ ] **Step 3: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketRequestController extends Controller
{
    public function create(Event $event, Request $request): View
    {
        return view('ticket-requests.create', [
            'event' => $event,
            'ticketTypes' => $event->ticketTypes()->where('is_active', true)->get(),
            'selectedTicketTypeId' => (int) $request->query('type'),
        ]);
    }
}
```

- [ ] **Step 4: Register the route**

```php
// routes/web.php
use App\Http\Controllers\TicketRequestController;

Route::get('/events/{event}/request', [TicketRequestController::class, 'create'])->name('ticket-requests.create');
```

- [ ] **Step 5: Create the view**

```blade
{{-- resources/views/ticket-requests/create.blade.php --}}
@extends('layouts.app')

@section('title', __('Request a Ticket'))

@section('content')
    <div class="container mx-auto px-4 py-5">
        <h1>{{ __('Request Your Ticket') }}</h1>
        {{-- Submission handling is a future spec; this form has no action wired up yet. --}}
        <form>
            <label for="ticket_type_id">{{ __('Ticket Type') }}</label>
            <select id="ticket_type_id" name="ticket_type_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @foreach($ticketTypes as $ticketType)
                    <option value="{{ $ticketType->id }}" @selected($ticketType->id === $selectedTicketTypeId)>
                        {{ app()->getLocale() === 'ar' ? $ticketType->name_ar : $ticketType->name_en }} — {{ $ticketType->price }} {{ $ticketType->currency }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endsection
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter=TicketRequestPageTest`
Expected: PASS (2 passed)

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/TicketRequestController.php resources/views/ticket-requests routes/web.php tests/Feature/TicketRequestPageTest.php
git commit -m "feat: ticket request form page (UI only)"
```

### Task 18: Awards placeholder page

Awards/voting mechanics are not yet designed (open question in `.claude/skills/event-domain.md`). This is a minimal informational page the landing page's Awards teaser can link to.

**Files:**
- Create: `app/Http/Controllers/AwardsController.php`
- Create: `resources/views/awards/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/AwardsPageTest.php`

**Interfaces:**
- Consumes: `Event`, `LandingPageContent` (Phase B).
- Produces: named route `awards.show` (`GET /events/{event}/awards`) — Task 19 links the landing page's Awards teaser to it.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_awards_placeholder_page_renders(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('awards.show', $event));

        $response->assertStatus(200);
        $response->assertSee(__('Awards'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AwardsPageTest`
Expected: FAIL (route `awards.show` not defined)

- [ ] **Step 3: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\LandingPageSection;
use App\Models\Event;
use Illuminate\View\View;

class AwardsController extends Controller
{
    public function show(Event $event): View
    {
        return view('awards.show', [
            'event' => $event,
            'blurb' => $event->contentFor(LandingPageSection::AwardsTeaser, 'blurb'),
        ]);
    }
}
```

- [ ] **Step 4: Register the route**

```php
// routes/web.php
use App\Http\Controllers\AwardsController;

Route::get('/events/{event}/awards', [AwardsController::class, 'show'])->name('awards.show');
```

- [ ] **Step 5: Create the view**

```blade
{{-- resources/views/awards/show.blade.php --}}
@extends('layouts.app')

@section('title', __('Awards'))

@section('content')
    <div class="container mx-auto px-4 py-5 text-center">
        <h1>{{ __('Awards') }}</h1>
        @if($blurb)
            <p>{{ app()->getLocale() === 'ar' ? $blurb->value_ar : $blurb->value_en }}</p>
        @endif
        <p class="text-gray-400">{{ __('Voting details coming soon.') }}</p>
    </div>
@endsection
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=AwardsPageTest`
Expected: PASS (1 passed)

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/AwardsController.php resources/views/awards routes/web.php tests/Feature/AwardsPageTest.php
git commit -m "feat: awards placeholder page"
```

### Task 19: Wire Workshops, Agenda, Tickets, and Awards sections into the landing page

Now that `workshops.index`, `agenda.show`, `ticket-requests.create`, and `awards.show` all exist (Tasks 15–18), the landing page can link to them.

**Files:**
- Create: `resources/views/landing/partials/workshops-teaser.blade.php`
- Create: `resources/views/landing/partials/agenda-teaser.blade.php`
- Create: `resources/views/landing/partials/tickets.blade.php`
- Create: `resources/views/landing/partials/awards-teaser.blade.php`
- Modify: `resources/views/landing/show.blade.php`
- Test: `tests/Feature/LandingPageTest.php` (append cases)

**Interfaces:**
- Consumes: `workshops.index`, `agenda.show`, `ticket-requests.create`, `awards.show` route names (Tasks 15–18); `$event->workshops`, `$event->ticketTypes`, `$event->contentFor(LandingPageSection::AwardsTeaser, 'blurb')`.

- [ ] **Step 1: Append the failing tests**

```php
// tests/Feature/LandingPageTest.php — add methods to the existing class

public function test_workshops_teaser_links_to_workshops_index(): void
{
    $event = Event::factory()->create();
    \App\Models\Workshop::factory()->for($event)->create();

    $response = $this->get(route('landing.show', $event));

    $response->assertSee(route('workshops.index', $event), false);
}

public function test_agenda_teaser_links_to_agenda_page(): void
{
    $event = Event::factory()->create();
    \App\Models\AgendaItem::factory()->for($event)->create();

    $response = $this->get(route('landing.show', $event));

    $response->assertSee(route('agenda.show', $event), false);
}

public function test_tickets_section_links_to_request_page(): void
{
    $event = Event::factory()->create();
    $ticketType = \App\Models\TicketType::factory()->for($event)->create(['name_en' => 'General']);

    $response = $this->get(route('landing.show', $event));

    $response->assertSee('General');
    $response->assertSee(route('ticket-requests.create', $event) . '?type=' . $ticketType->id, false);
}

public function test_awards_teaser_links_to_awards_page(): void
{
    $event = Event::factory()->create();

    $response = $this->get(route('landing.show', $event));

    $response->assertSee(route('awards.show', $event), false);
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=LandingPageTest`
Expected: FAIL (new partials don't exist yet, so the links aren't on the page)

- [ ] **Step 3: Create the Workshops teaser partial**

```blade
{{-- resources/views/landing/partials/workshops-teaser.blade.php --}}
@if($event->workshops->isNotEmpty())
    <section id="workshops" class="container mx-auto px-4 py-5">
        <h2>{{ __('Workshops') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($event->workshops->take(3) as $workshop)
                <div class="mb-3">
                    <div class="bg-gray-900 rounded-lg shadow overflow-hidden text-white h-full">
                        <div class="p-4">
                            <h5>{{ app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en }}</h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <a href="{{ route('workshops.index', $event) }}" class="border border-white text-white px-4 py-2 rounded hover:bg-white hover:text-ccs-black">{{ __('See All Workshops') }}</a>
    </section>
@endif
```

- [ ] **Step 4: Create the Agenda teaser partial**

```blade
{{-- resources/views/landing/partials/agenda-teaser.blade.php --}}
@if($event->agendaItems->isNotEmpty())
    <section id="agenda-teaser" class="container mx-auto px-4 py-5">
        <h2>{{ __('Agenda') }}</h2>
        <a href="{{ route('agenda.show', $event) }}" class="border border-white text-white px-4 py-2 rounded hover:bg-white hover:text-ccs-black">{{ __('View Full Agenda') }}</a>
    </section>
@endif
```

- [ ] **Step 5: Create the Tickets partial**

```blade
{{-- resources/views/landing/partials/tickets.blade.php --}}
@if($event->ticketTypes->isNotEmpty())
    <section id="tickets" class="container mx-auto px-4 py-5">
        <h2>{{ __('Tickets') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach($event->ticketTypes->where('is_active', true) as $ticketType)
                <div class="mb-4">
                    <div class="bg-gray-900 rounded-lg shadow overflow-hidden text-white h-full">
                        <div class="p-4">
                            <h5>{{ app()->getLocale() === 'ar' ? $ticketType->name_ar : $ticketType->name_en }}</h5>
                            <p>{{ $ticketType->price }} {{ $ticketType->currency }}</p>
                            <a href="{{ route('ticket-requests.create', $event) }}?type={{ $ticketType->id }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-3 py-1.5 rounded text-sm">
                                {{ __('Request This Ticket') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
```

- [ ] **Step 6: Create the Awards teaser partial**

```blade
{{-- resources/views/landing/partials/awards-teaser.blade.php --}}
<section id="awards" class="container mx-auto px-4 py-5">
    <h2>{{ __('Awards') }}</h2>
    @php $blurb = $event->contentFor(\App\Enums\LandingPageSection::AwardsTeaser, 'blurb'); @endphp
    @if($blurb)
        <p>{{ app()->getLocale() === 'ar' ? $blurb->value_ar : $blurb->value_en }}</p>
    @endif
    <a href="{{ route('awards.show', $event) }}" class="border border-white text-white px-4 py-2 rounded hover:bg-white hover:text-ccs-black">{{ __('Learn More') }}</a>
</section>
```

- [ ] **Step 7: Wire all sections into the page shell in the final fixed order**

```blade
{{-- resources/views/landing/show.blade.php --}}
@section('content')
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
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=LandingPageTest`
Expected: PASS (11 passed)

- [ ] **Step 9: Manual verification**

Run: `php artisan migrate:fresh --seed && php artisan serve`
Visit `http://localhost:8000/events/ccs-2026` — confirm all ten sections render in order with the seeded CCS content, and that Workshops/Agenda/Tickets/Awards links navigate correctly.

- [ ] **Step 10: Commit**

```bash
git add resources/views/landing tests/Feature/LandingPageTest.php
git commit -m "feat: wire Workshops, Agenda, Tickets, Awards sections into landing page"
```

---

## Phase D — Admin CRUD

All routes in this phase sit inside the `auth`-protected `admin.` route group established in Task 3. Controllers call `FormRequest::validated()` straight into the Eloquent model — no Service/Repository layer, since none of these screens have business logic beyond persisting validated fields (Repository Pattern is "where appropriate" per `.claude/skills/coding-standards.md`; plain CRUD isn't one of those places).

### Task 20: Admin Events CRUD

**Files:**
- Create: `app/Http/Controllers/Admin/EventController.php`
- Create: `app/Http/Requests/Admin/EventRequest.php`
- Create: `resources/views/admin/events/index.blade.php`
- Create: `resources/views/admin/events/form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/EventCrudTest.php`

**Interfaces:**
- Consumes: `Event` model (Task 4), `auth` middleware group (Task 3).
- Produces: named routes `admin.events.index/create/store/edit/update/destroy`. Every Phase D task below creates an Event first via `Event::factory()` (as a dependency), not via this UI, so this task has no forward dependency on the others.

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_events_index(): void
    {
        $this->get(route('admin.events.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_an_event(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'slug' => 'ccs-2027',
            'name_ar' => 'قمة صناع المحتوى',
            'name_en' => 'Content Creators Summit 2027',
            'start_date' => '2027-08-15',
            'end_date' => '2027-08-16',
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('admin.events.index'));
        $this->assertDatabaseHas('events', ['slug' => 'ccs-2027']);
    }

    public function test_creating_an_event_requires_bilingual_name(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'slug' => 'ccs-2027',
            'name_ar' => '',
            'name_en' => '',
            'start_date' => '2027-08-15',
            'end_date' => '2027-08-16',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors(['name_ar', 'name_en']);
    }

    public function test_admin_can_update_an_event(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'slug' => $event->slug,
            'name_ar' => 'اسم محدث',
            'name_en' => 'Updated Name',
            'start_date' => $event->start_date->toDateString(),
            'end_date' => $event->end_date->toDateString(),
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.events.index'));
        $this->assertDatabaseHas('events', ['id' => $event->id, 'name_en' => 'Updated Name']);
    }

    public function test_admin_can_delete_an_event(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.destroy', $event));

        $response->assertRedirect(route('admin.events.index'));
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=Admin/EventCrudTest`
Expected: FAIL (route `admin.events.index` not defined)

- [ ] **Step 3: Create the Form Request**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'slug' => ['required', 'string', 'max:255', Rule::unique('events', 'slug')->ignore($eventId)],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'tagline_ar' => ['nullable', 'string', 'max:255'],
            'tagline_en' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'venue_name_ar' => ['nullable', 'string', 'max:255'],
            'venue_name_en' => ['nullable', 'string', 'max:255'],
            'venue_address_ar' => ['nullable', 'string', 'max:255'],
            'venue_address_en' => ['nullable', 'string', 'max:255'],
            'map_embed_url' => ['nullable', 'url'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('admin.events.index', ['events' => Event::orderBy('start_date')->get()]);
    }

    public function create(): View
    {
        return view('admin.events.form', ['event' => new Event()]);
    }

    public function store(EventRequest $request): RedirectResponse
    {
        Event::create($request->validated());

        return redirect()->route('admin.events.index');
    }

    public function edit(Event $event): View
    {
        return view('admin.events.form', ['event' => $event]);
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()->route('admin.events.index');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index');
    }
}
```

- [ ] **Step 5: Register the resource route inside Task 3's existing auth group**

Task 3 already created `Route::prefix('admin')->name('admin.')->group(...)` containing an inner `Route::middleware('auth')->group(function () { Route::get('/', ...)->name('dashboard'); })`. Add the `events` resource inside that same inner block, next to the dashboard route — every admin CRUD task from here on (Tasks 21–27) nests its `Route::resource(...)` call in this same inner block too, so there is exactly one auth-protected admin route group in the whole app.

```php
// routes/web.php
use App\Http\Controllers\Admin\EventController;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('events', EventController::class)->except('show');
});
```

- [ ] **Step 6: Create the index view**

```blade
{{-- resources/views/admin/events/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Events') }}</h1>
        <a href="{{ route('admin.events.create') }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Event') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3">{{ __('Status') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($events as $event)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $event->name_en }}</td>
                        <td class="py-2 px-3">{{ $event->status->value }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.edit', $event) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 7: Create the shared create/edit form view**

```blade
{{-- resources/views/admin/events/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $event->exists ? __('Edit Event') : __('New Event') }}</h1>
        <form method="POST" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}">
            @csrf
            @if($event->exists) @method('PUT') @endif

            <label>{{ __('Slug') }}</label>
            <input name="slug" value="{{ old('slug', $event->slug) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
            @error('slug') <p class="text-red-500">{{ $message }}</p> @enderror

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $event->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $event->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Tagline (Arabic)') }}</label>
                    <input name="tagline_ar" value="{{ old('tagline_ar', $event->tagline_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                </div>
                <div>
                    <label>{{ __('Tagline (English)') }}</label>
                    <input name="tagline_en" value="{{ old('tagline_en', $event->tagline_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" value="{{ old('start_date', optional($event->start_date)->toDateString()) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('start_date') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('End Date') }}</label>
                    <input type="date" name="end_date" value="{{ old('end_date', optional($event->end_date)->toDateString()) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('end_date') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Venue Name (Arabic)') }}</label>
                    <input name="venue_name_ar" value="{{ old('venue_name_ar', $event->venue_name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                </div>
                <div>
                    <label>{{ __('Venue Name (English)') }}</label>
                    <input name="venue_name_en" value="{{ old('venue_name_en', $event->venue_name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Venue Address (Arabic)') }}</label>
                    <input name="venue_address_ar" value="{{ old('venue_address_ar', $event->venue_address_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                </div>
                <div>
                    <label>{{ __('Venue Address (English)') }}</label>
                    <input name="venue_address_en" value="{{ old('venue_address_en', $event->venue_address_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <label>{{ __('Map Embed URL') }}</label>
            <input name="map_embed_url" value="{{ old('map_embed_url', $event->map_embed_url) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <label>{{ __('Status') }}</label>
            <select name="status" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                <option value="draft" @selected(old('status', $event->status?->value) === 'draft')>{{ __('Draft') }}</option>
                <option value="published" @selected(old('status', $event->status?->value) === 'published')>{{ __('Published') }}</option>
            </select>

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=Admin/EventCrudTest`
Expected: PASS (5 passed)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Admin/EventController.php app/Http/Requests/Admin/EventRequest.php resources/views/admin/events routes/web.php tests/Feature/Admin/EventCrudTest.php
git commit -m "feat: admin Events CRUD"
```

### Task 21: Admin Speakers CRUD

Nested under an Event: `/admin/events/{event}/speakers`.

**Files:**
- Create: `app/Http/Controllers/Admin/SpeakerController.php`
- Create: `app/Http/Requests/Admin/SpeakerRequest.php`
- Create: `resources/views/admin/speakers/index.blade.php`
- Create: `resources/views/admin/speakers/form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/SpeakerCrudTest.php`

**Interfaces:**
- Consumes: `Event`, `Speaker` models (Phase B), `auth` group (Task 3).
- Produces: named routes `admin.events.speakers.index/create/store/edit/update/destroy`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeakerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_speaker_for_an_event(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.speakers.store', $event), [
            'name_ar' => 'اسم المتحدث',
            'name_en' => 'Speaker Name',
            'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.speakers.index', $event));
        $this->assertDatabaseHas('speakers', ['event_id' => $event->id, 'name_en' => 'Speaker Name']);
    }

    public function test_creating_a_speaker_requires_bilingual_name(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.speakers.store', $event), [
            'name_ar' => '', 'name_en' => '',
        ]);

        $response->assertSessionHasErrors(['name_ar', 'name_en']);
    }

    public function test_admin_can_update_a_speaker(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $speaker = Speaker::factory()->for($event)->create();

        $response = $this->actingAs($admin)->put(route('admin.events.speakers.update', [$event, $speaker]), [
            'name_ar' => 'محدث', 'name_en' => 'Updated', 'sort_order' => 1,
        ]);

        $response->assertRedirect(route('admin.events.speakers.index', $event));
        $this->assertDatabaseHas('speakers', ['id' => $speaker->id, 'name_en' => 'Updated']);
    }

    public function test_admin_can_delete_a_speaker(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $speaker = Speaker::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.speakers.destroy', [$event, $speaker]));

        $response->assertRedirect(route('admin.events.speakers.index', $event));
        $this->assertDatabaseMissing('speakers', ['id' => $speaker->id]);
    }

    public function test_editing_a_speaker_from_another_event_returns_404(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $otherSpeaker = Speaker::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.events.speakers.edit', [$event, $otherSpeaker]));

        $response->assertStatus(404);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=Admin/SpeakerCrudTest`
Expected: FAIL (route `admin.events.speakers.store` not defined)

- [ ] **Step 3: Create the Form Request**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SpeakerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'bio_ar' => ['nullable', 'string'],
            'bio_en' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpeakerRequest;
use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SpeakerController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.speakers.index', ['event' => $event, 'speakers' => $event->speakers]);
    }

    public function create(Event $event): View
    {
        return view('admin.speakers.form', ['event' => $event, 'speaker' => new Speaker()]);
    }

    public function store(SpeakerRequest $request, Event $event): RedirectResponse
    {
        $event->speakers()->create($request->validated());

        return redirect()->route('admin.events.speakers.index', $event);
    }

    public function edit(Event $event, Speaker $speaker): View
    {
        $this->assertBelongsToEvent($event, $speaker);

        return view('admin.speakers.form', ['event' => $event, 'speaker' => $speaker]);
    }

    public function update(SpeakerRequest $request, Event $event, Speaker $speaker): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $speaker);
        $speaker->update($request->validated());

        return redirect()->route('admin.events.speakers.index', $event);
    }

    public function destroy(Event $event, Speaker $speaker): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $speaker);
        $speaker->delete();

        return redirect()->route('admin.events.speakers.index', $event);
    }

    private function assertBelongsToEvent(Event $event, Speaker $speaker): void
    {
        if ($speaker->event_id !== $event->id) {
            throw new NotFoundHttpException();
        }
    }
}
```

- [ ] **Step 5: Register the nested resource route**

```php
// routes/web.php — inside the existing auth-protected admin group from Task 20
use App\Http\Controllers\Admin\SpeakerController;

Route::resource('events.speakers', SpeakerController::class)->except('show');
```

- [ ] **Step 6: Create the index view**

```blade
{{-- resources/views/admin/speakers/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Speakers') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.speakers.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Speaker') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($speakers as $speaker)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $speaker->name_en }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.speakers.edit', [$event, $speaker]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.speakers.destroy', [$event, $speaker]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 7: Create the shared create/edit form view**

```blade
{{-- resources/views/admin/speakers/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $speaker->exists ? __('Edit Speaker') : __('New Speaker') }}</h1>
        <form method="POST" action="{{ $speaker->exists ? route('admin.events.speakers.update', [$event, $speaker]) : route('admin.events.speakers.store', $event) }}">
            @csrf
            @if($speaker->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $speaker->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $speaker->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Title (Arabic)') }}</label>
                    <input name="title_ar" value="{{ old('title_ar', $speaker->title_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                </div>
                <div>
                    <label>{{ __('Title (English)') }}</label>
                    <input name="title_en" value="{{ old('title_en', $speaker->title_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Bio (Arabic)') }}</label>
                    <textarea name="bio_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">{{ old('bio_ar', $speaker->bio_ar) }}</textarea>
                </div>
                <div>
                    <label>{{ __('Bio (English)') }}</label>
                    <textarea name="bio_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">{{ old('bio_en', $speaker->bio_en) }}</textarea>
                </div>
            </div>

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $speaker->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=Admin/SpeakerCrudTest`
Expected: PASS (5 passed)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Admin/SpeakerController.php app/Http/Requests/Admin/SpeakerRequest.php resources/views/admin/speakers routes/web.php tests/Feature/Admin/SpeakerCrudTest.php
git commit -m "feat: admin Speakers CRUD"
```

### Task 22: Admin Sponsors CRUD

Nested under an Event: `/admin/events/{event}/sponsors`.

**Files:**
- Create: `app/Http/Controllers/Admin/SponsorController.php`
- Create: `app/Http/Requests/Admin/SponsorRequest.php`
- Create: `resources/views/admin/sponsors/index.blade.php`
- Create: `resources/views/admin/sponsors/form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/SponsorCrudTest.php`

**Interfaces:**
- Consumes: `Event`, `Sponsor` models (Phase B), `auth` group (Task 3).
- Produces: named routes `admin.events.sponsors.index/create/store/edit/update/destroy`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_sponsor_for_an_event(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.sponsors.store', $event), [
            'name_ar' => 'الراعي', 'name_en' => 'Sponsor Co.', 'tier' => 'gold', 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.sponsors.index', $event));
        $this->assertDatabaseHas('sponsors', ['event_id' => $event->id, 'name_en' => 'Sponsor Co.', 'tier' => 'gold']);
    }

    public function test_creating_a_sponsor_requires_a_valid_tier(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.sponsors.store', $event), [
            'name_ar' => 'الراعي', 'name_en' => 'Sponsor Co.', 'tier' => 'not-a-tier',
        ]);

        $response->assertSessionHasErrors('tier');
    }

    public function test_admin_can_update_a_sponsor(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $sponsor = Sponsor::factory()->for($event)->create();

        $response = $this->actingAs($admin)->put(route('admin.events.sponsors.update', [$event, $sponsor]), [
            'name_ar' => 'محدث', 'name_en' => 'Updated', 'tier' => 'platinum', 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.sponsors.index', $event));
        $this->assertDatabaseHas('sponsors', ['id' => $sponsor->id, 'tier' => 'platinum']);
    }

    public function test_admin_can_delete_a_sponsor(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $sponsor = Sponsor::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.sponsors.destroy', [$event, $sponsor]));

        $response->assertRedirect(route('admin.events.sponsors.index', $event));
        $this->assertDatabaseMissing('sponsors', ['id' => $sponsor->id]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=Admin/SponsorCrudTest`
Expected: FAIL (route `admin.events.sponsors.store` not defined)

- [ ] **Step 3: Create the Form Request**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SponsorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'tier' => ['required', Rule::in(['platinum', 'gold', 'silver', 'bronze'])],
            'website_url' => ['nullable', 'url'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SponsorRequest;
use App\Models\Event;
use App\Models\Sponsor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SponsorController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.sponsors.index', ['event' => $event, 'sponsors' => $event->sponsors]);
    }

    public function create(Event $event): View
    {
        return view('admin.sponsors.form', ['event' => $event, 'sponsor' => new Sponsor()]);
    }

    public function store(SponsorRequest $request, Event $event): RedirectResponse
    {
        $event->sponsors()->create($request->validated());

        return redirect()->route('admin.events.sponsors.index', $event);
    }

    public function edit(Event $event, Sponsor $sponsor): View
    {
        $this->assertBelongsToEvent($event, $sponsor);

        return view('admin.sponsors.form', ['event' => $event, 'sponsor' => $sponsor]);
    }

    public function update(SponsorRequest $request, Event $event, Sponsor $sponsor): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $sponsor);
        $sponsor->update($request->validated());

        return redirect()->route('admin.events.sponsors.index', $event);
    }

    public function destroy(Event $event, Sponsor $sponsor): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $sponsor);
        $sponsor->delete();

        return redirect()->route('admin.events.sponsors.index', $event);
    }

    private function assertBelongsToEvent(Event $event, Sponsor $sponsor): void
    {
        if ($sponsor->event_id !== $event->id) {
            throw new NotFoundHttpException();
        }
    }
}
```

- [ ] **Step 5: Register the nested resource route**

```php
// routes/web.php — inside the existing auth-protected admin group
use App\Http\Controllers\Admin\SponsorController;

Route::resource('events.sponsors', SponsorController::class)->except('show');
```

- [ ] **Step 6: Create the index view**

```blade
{{-- resources/views/admin/sponsors/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Sponsors') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.sponsors.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Sponsor') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3">{{ __('Tier') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($sponsors as $sponsor)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $sponsor->name_en }}</td>
                        <td class="py-2 px-3">{{ $sponsor->tier }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.sponsors.edit', [$event, $sponsor]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.sponsors.destroy', [$event, $sponsor]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 7: Create the shared create/edit form view**

```blade
{{-- resources/views/admin/sponsors/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $sponsor->exists ? __('Edit Sponsor') : __('New Sponsor') }}</h1>
        <form method="POST" action="{{ $sponsor->exists ? route('admin.events.sponsors.update', [$event, $sponsor]) : route('admin.events.sponsors.store', $event) }}">
            @csrf
            @if($sponsor->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $sponsor->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $sponsor->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <label>{{ __('Tier') }}</label>
            <select name="tier" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @foreach(['platinum', 'gold', 'silver', 'bronze'] as $tier)
                    <option value="{{ $tier }}" @selected(old('tier', $sponsor->tier) === $tier)>{{ ucfirst($tier) }}</option>
                @endforeach
            </select>
            @error('tier') <p class="text-red-500">{{ $message }}</p> @enderror

            <label>{{ __('Website URL') }}</label>
            <input name="website_url" value="{{ old('website_url', $sponsor->website_url) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $sponsor->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=Admin/SponsorCrudTest`
Expected: PASS (4 passed)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Admin/SponsorController.php app/Http/Requests/Admin/SponsorRequest.php resources/views/admin/sponsors routes/web.php tests/Feature/Admin/SponsorCrudTest.php
git commit -m "feat: admin Sponsors CRUD"
```

### Task 23: Admin Ticket Types CRUD

Nested under an Event: `/admin/events/{event}/ticket-types`.

**Files:**
- Create: `app/Http/Controllers/Admin/TicketTypeController.php`
- Create: `app/Http/Requests/Admin/TicketTypeRequest.php`
- Create: `resources/views/admin/ticket-types/index.blade.php`
- Create: `resources/views/admin/ticket-types/form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/TicketTypeCrudTest.php`

**Interfaces:**
- Consumes: `Event`, `TicketType` models (Phase B), `auth` group (Task 3).
- Produces: named routes `admin.events.ticket-types.index/create/store/edit/update/destroy`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_ticket_type_with_unlimited_workshop_slots(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.ticket-types.store', $event), [
            'name_ar' => 'بلاتيني', 'name_en' => 'Platinum',
            'price' => 2500, 'currency' => 'SAR',
            'workshop_slot_count' => '', // blank = unlimited (null)
            'sort_order' => 0, 'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.events.ticket-types.index', $event));
        $this->assertDatabaseHas('ticket_types', ['event_id' => $event->id, 'name_en' => 'Platinum', 'workshop_slot_count' => null]);
    }

    public function test_admin_can_create_a_ticket_type_with_zero_workshop_slots(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.ticket-types.store', $event), [
            'name_ar' => 'عام', 'name_en' => 'General',
            'price' => 300, 'currency' => 'SAR',
            'workshop_slot_count' => 0,
            'sort_order' => 0, 'is_active' => 1,
        ]);

        $this->assertDatabaseHas('ticket_types', ['event_id' => $event->id, 'workshop_slot_count' => 0]);
    }

    public function test_creating_a_ticket_type_requires_a_price(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.ticket-types.store', $event), [
            'name_ar' => 'عام', 'name_en' => 'General', 'price' => '',
        ]);

        $response->assertSessionHasErrors('price');
    }

    public function test_admin_can_delete_a_ticket_type(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.ticket-types.destroy', [$event, $ticketType]));

        $response->assertRedirect(route('admin.events.ticket-types.index', $event));
        $this->assertDatabaseMissing('ticket_types', ['id' => $ticketType->id]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=Admin/TicketTypeCrudTest`
Expected: FAIL (route `admin.events.ticket-types.store` not defined)

- [ ] **Step 3: Create the Form Request**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'workshop_slot_count' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketTypeController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.ticket-types.index', ['event' => $event, 'ticketTypes' => $event->ticketTypes]);
    }

    public function create(Event $event): View
    {
        return view('admin.ticket-types.form', ['event' => $event, 'ticketType' => new TicketType()]);
    }

    public function store(TicketTypeRequest $request, Event $event): RedirectResponse
    {
        $event->ticketTypes()->create($request->validated());

        return redirect()->route('admin.events.ticket-types.index', $event);
    }

    public function edit(Event $event, TicketType $ticketType): View
    {
        $this->assertBelongsToEvent($event, $ticketType);

        return view('admin.ticket-types.form', ['event' => $event, 'ticketType' => $ticketType]);
    }

    public function update(TicketTypeRequest $request, Event $event, TicketType $ticketType): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $ticketType);
        $ticketType->update($request->validated());

        return redirect()->route('admin.events.ticket-types.index', $event);
    }

    public function destroy(Event $event, TicketType $ticketType): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $ticketType);
        $ticketType->delete();

        return redirect()->route('admin.events.ticket-types.index', $event);
    }

    private function assertBelongsToEvent(Event $event, TicketType $ticketType): void
    {
        if ($ticketType->event_id !== $event->id) {
            throw new NotFoundHttpException();
        }
    }
}
```

- [ ] **Step 5: Register the nested resource route**

`Route::resource` derives its route-model-binding parameter name from the URI segment: `ticket-types` → snake_case singular `ticket_type`, which would not match a controller parameter named `$ticketType`. `->parameters()` overrides that derived name so the wildcard binds to `$ticketType` as written in the controller above.

```php
// routes/web.php — inside the existing auth-protected admin group
use App\Http\Controllers\Admin\TicketTypeController;

Route::resource('events.ticket-types', TicketTypeController::class)
    ->except('show')
    ->parameters(['ticket-types' => 'ticketType']);
```

- [ ] **Step 6: Create the index view**

```blade
{{-- resources/views/admin/ticket-types/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Ticket Types') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.ticket-types.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Ticket Type') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3">{{ __('Price') }}</th><th class="py-2 px-3">{{ __('Workshop Slots') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($ticketTypes as $ticketType)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $ticketType->name_en }}</td>
                        <td class="py-2 px-3">{{ $ticketType->price }} {{ $ticketType->currency }}</td>
                        <td class="py-2 px-3">{{ $ticketType->workshop_slot_count ?? __('Unlimited') }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.ticket-types.edit', [$event, $ticketType]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.ticket-types.destroy', [$event, $ticketType]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 7: Create the shared create/edit form view**

```blade
{{-- resources/views/admin/ticket-types/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $ticketType->exists ? __('Edit Ticket Type') : __('New Ticket Type') }}</h1>
        <form method="POST" action="{{ $ticketType->exists ? route('admin.events.ticket-types.update', [$event, $ticketType]) : route('admin.events.ticket-types.store', $event) }}">
            @csrf
            @if($ticketType->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $ticketType->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $ticketType->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Description (Arabic)') }}</label>
                    <textarea name="description_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">{{ old('description_ar', $ticketType->description_ar) }}</textarea>
                </div>
                <div>
                    <label>{{ __('Description (English)') }}</label>
                    <textarea name="description_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">{{ old('description_en', $ticketType->description_en) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label>{{ __('Price') }}</label>
                    <input type="number" name="price" value="{{ old('price', $ticketType->price) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('price') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Currency') }}</label>
                    <input name="currency" value="{{ old('currency', $ticketType->currency ?? 'SAR') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" maxlength="3">
                </div>
                <div>
                    <label>{{ __('Workshop Slots (blank = unlimited)') }}</label>
                    <input type="number" name="workshop_slot_count" value="{{ old('workshop_slot_count', $ticketType->workshop_slot_count) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded" id="is_active" @checked(old('is_active', $ticketType->is_active ?? true))>
                <label for="is_active">{{ __('Active') }}</label>
            </div>

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $ticketType->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=Admin/TicketTypeCrudTest`
Expected: PASS (4 passed)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Admin/TicketTypeController.php app/Http/Requests/Admin/TicketTypeRequest.php resources/views/admin/ticket-types routes/web.php tests/Feature/Admin/TicketTypeCrudTest.php
git commit -m "feat: admin Ticket Types CRUD"
```

### Task 24: Admin Workshops CRUD

Nested under an Event: `/admin/events/{event}/workshops`.

**Files:**
- Create: `app/Http/Controllers/Admin/WorkshopController.php`
- Create: `app/Http/Requests/Admin/WorkshopRequest.php`
- Create: `resources/views/admin/workshops/index.blade.php`
- Create: `resources/views/admin/workshops/form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/WorkshopCrudTest.php`

**Interfaces:**
- Consumes: `Event`, `Workshop`, `Speaker` models (Phase B), `auth` group (Task 3). This controller is namespaced `App\Http\Controllers\Admin\WorkshopController`, distinct from the public `App\Http\Controllers\WorkshopController` (Task 15).
- Produces: named routes `admin.events.workshops.index/create/store/edit/update/destroy`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Speaker;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_workshop_with_a_speaker(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $speaker = Speaker::factory()->for($event)->create();

        $response = $this->actingAs($admin)->post(route('admin.events.workshops.store', $event), [
            'speaker_id' => $speaker->id,
            'slug' => 'ai-workshop',
            'name_ar' => 'ورشة', 'name_en' => 'AI Workshop',
            'capacity' => 30, 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.workshops.index', $event));
        $this->assertDatabaseHas('workshops', ['event_id' => $event->id, 'slug' => 'ai-workshop', 'speaker_id' => $speaker->id]);
    }

    public function test_creating_a_workshop_requires_a_unique_slug(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        Workshop::factory()->create(['slug' => 'ai-workshop']);

        $response = $this->actingAs($admin)->post(route('admin.events.workshops.store', $event), [
            'slug' => 'ai-workshop', 'name_ar' => 'ورشة', 'name_en' => 'AI Workshop', 'capacity' => 30,
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_admin_can_delete_a_workshop(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $workshop = Workshop::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.workshops.destroy', [$event, $workshop]));

        $response->assertRedirect(route('admin.events.workshops.index', $event));
        $this->assertDatabaseMissing('workshops', ['id' => $workshop->id]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=Admin/WorkshopCrudTest`
Expected: FAIL (route `admin.events.workshops.store` not defined)

- [ ] **Step 3: Create the Form Request**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workshopId = $this->route('workshop')?->id;

        return [
            'speaker_id' => ['nullable', 'exists:speakers,id'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('workshops', 'slug')->ignore($workshopId)],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WorkshopRequest;
use App\Models\Event;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorkshopController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.workshops.index', ['event' => $event, 'workshops' => $event->workshops]);
    }

    public function create(Event $event): View
    {
        return view('admin.workshops.form', [
            'event' => $event, 'workshop' => new Workshop(), 'speakers' => $event->speakers,
        ]);
    }

    public function store(WorkshopRequest $request, Event $event): RedirectResponse
    {
        $event->workshops()->create($request->validated());

        return redirect()->route('admin.events.workshops.index', $event);
    }

    public function edit(Event $event, Workshop $workshop): View
    {
        $this->assertBelongsToEvent($event, $workshop);

        return view('admin.workshops.form', [
            'event' => $event, 'workshop' => $workshop, 'speakers' => $event->speakers,
        ]);
    }

    public function update(WorkshopRequest $request, Event $event, Workshop $workshop): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $workshop);
        $workshop->update($request->validated());

        return redirect()->route('admin.events.workshops.index', $event);
    }

    public function destroy(Event $event, Workshop $workshop): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $workshop);
        $workshop->delete();

        return redirect()->route('admin.events.workshops.index', $event);
    }

    private function assertBelongsToEvent(Event $event, Workshop $workshop): void
    {
        if ($workshop->event_id !== $event->id) {
            throw new NotFoundHttpException();
        }
    }
}
```

- [ ] **Step 5: Register the nested resource route**

```php
// routes/web.php — inside the existing auth-protected admin group
use App\Http\Controllers\Admin\WorkshopController as AdminWorkshopController;

Route::resource('events.workshops', AdminWorkshopController::class)->except('show');
```

- [ ] **Step 6: Create the index view**

```blade
{{-- resources/views/admin/workshops/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Workshops') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.workshops.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Workshop') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3">{{ __('Capacity') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($workshops as $workshop)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $workshop->name_en }}</td>
                        <td class="py-2 px-3">{{ $workshop->capacity }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.workshops.edit', [$event, $workshop]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.workshops.destroy', [$event, $workshop]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 7: Create the shared create/edit form view**

```blade
{{-- resources/views/admin/workshops/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $workshop->exists ? __('Edit Workshop') : __('New Workshop') }}</h1>
        <form method="POST" action="{{ $workshop->exists ? route('admin.events.workshops.update', [$event, $workshop]) : route('admin.events.workshops.store', $event) }}">
            @csrf
            @if($workshop->exists) @method('PUT') @endif

            <label>{{ __('Slug') }}</label>
            <input name="slug" value="{{ old('slug', $workshop->slug) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
            @error('slug') <p class="text-red-500">{{ $message }}</p> @enderror

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $workshop->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $workshop->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Description (Arabic)') }}</label>
                    <textarea name="description_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">{{ old('description_ar', $workshop->description_ar) }}</textarea>
                </div>
                <div>
                    <label>{{ __('Description (English)') }}</label>
                    <textarea name="description_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">{{ old('description_en', $workshop->description_en) }}</textarea>
                </div>
            </div>

            <label>{{ __('Speaker') }}</label>
            <select name="speaker_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                <option value="">{{ __('None') }}</option>
                @foreach($speakers as $speaker)
                    <option value="{{ $speaker->id }}" @selected(old('speaker_id', $workshop->speaker_id) === $speaker->id)>{{ $speaker->name_en }}</option>
                @endforeach
            </select>

            <label>{{ __('Capacity') }}</label>
            <input type="number" name="capacity" value="{{ old('capacity', $workshop->capacity ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
            @error('capacity') <p class="text-red-500">{{ $message }}</p> @enderror

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $workshop->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=Admin/WorkshopCrudTest`
Expected: PASS (3 passed)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Admin/WorkshopController.php app/Http/Requests/Admin/WorkshopRequest.php resources/views/admin/workshops routes/web.php tests/Feature/Admin/WorkshopCrudTest.php
git commit -m "feat: admin Workshops CRUD"
```

### Task 25: Admin Agenda Items CRUD

Nested under an Event: `/admin/events/{event}/agenda-items`.

**Files:**
- Create: `app/Http/Controllers/Admin/AgendaItemController.php`
- Create: `app/Http/Requests/Admin/AgendaItemRequest.php`
- Create: `resources/views/admin/agenda-items/index.blade.php`
- Create: `resources/views/admin/agenda-items/form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/AgendaItemCrudTest.php`

**Interfaces:**
- Consumes: `Event`, `AgendaItem`, `Speaker`, `Workshop`, `AgendaItemType` (Phase B), `auth` group (Task 3).
- Produces: named routes `admin.events.agenda-items.index/create/store/edit/update/destroy`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaItemCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_agenda_item(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.agenda-items.store', $event), [
            'day_date' => '2026-08-15', 'start_time' => '09:00', 'end_time' => '10:00',
            'title_ar' => 'الافتتاح', 'title_en' => 'Opening', 'type' => 'keynote', 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.agenda-items.index', $event));
        $this->assertDatabaseHas('agenda_items', ['event_id' => $event->id, 'title_en' => 'Opening', 'type' => 'keynote']);
    }

    public function test_creating_an_agenda_item_requires_end_time_after_start_time(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.agenda-items.store', $event), [
            'day_date' => '2026-08-15', 'start_time' => '10:00', 'end_time' => '09:00',
            'title_ar' => 'الافتتاح', 'title_en' => 'Opening', 'type' => 'keynote',
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_admin_can_delete_an_agenda_item(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $item = AgendaItem::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.agenda-items.destroy', [$event, $item]));

        $response->assertRedirect(route('admin.events.agenda-items.index', $event));
        $this->assertDatabaseMissing('agenda_items', ['id' => $item->id]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=Admin/AgendaItemCrudTest`
Expected: FAIL (route `admin.events.agenda-items.store` not defined)

- [ ] **Step 3: Create the Form Request**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AgendaItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendaItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'speaker_id' => ['nullable', 'exists:speakers,id'],
            'workshop_id' => ['nullable', 'exists:workshops,id'],
            'day_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_column(AgendaItemType::cases(), 'value'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AgendaItemType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AgendaItemRequest;
use App\Models\AgendaItem;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgendaItemController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.agenda-items.index', ['event' => $event, 'items' => $event->agendaItems]);
    }

    public function create(Event $event): View
    {
        return view('admin.agenda-items.form', [
            'event' => $event, 'item' => new AgendaItem(),
            'speakers' => $event->speakers, 'workshops' => $event->workshops,
            'types' => AgendaItemType::cases(),
        ]);
    }

    public function store(AgendaItemRequest $request, Event $event): RedirectResponse
    {
        $event->agendaItems()->create($request->validated());

        return redirect()->route('admin.events.agenda-items.index', $event);
    }

    public function edit(Event $event, AgendaItem $agendaItem): View
    {
        $this->assertBelongsToEvent($event, $agendaItem);

        return view('admin.agenda-items.form', [
            'event' => $event, 'item' => $agendaItem,
            'speakers' => $event->speakers, 'workshops' => $event->workshops,
            'types' => AgendaItemType::cases(),
        ]);
    }

    public function update(AgendaItemRequest $request, Event $event, AgendaItem $agendaItem): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $agendaItem);
        $agendaItem->update($request->validated());

        return redirect()->route('admin.events.agenda-items.index', $event);
    }

    public function destroy(Event $event, AgendaItem $agendaItem): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $agendaItem);
        $agendaItem->delete();

        return redirect()->route('admin.events.agenda-items.index', $event);
    }

    private function assertBelongsToEvent(Event $event, AgendaItem $agendaItem): void
    {
        if ($agendaItem->event_id !== $event->id) {
            throw new NotFoundHttpException();
        }
    }
}
```

- [ ] **Step 5: Register the nested resource route**

Same parameter-name issue as Task 23: `agenda-items` would otherwise derive the wildcard name `agenda_item`, which doesn't match the controller's `$agendaItem`.

```php
// routes/web.php — inside the existing auth-protected admin group
use App\Http\Controllers\Admin\AgendaItemController;

Route::resource('events.agenda-items', AgendaItemController::class)
    ->except('show')
    ->parameters(['agenda-items' => 'agendaItem']);
```

- [ ] **Step 6: Create the index view**

```blade
{{-- resources/views/admin/agenda-items/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Agenda Items') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.agenda-items.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Agenda Item') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Day') }}</th><th class="py-2 px-3">{{ __('Time') }}</th><th class="py-2 px-3">{{ __('Title') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($items as $item)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $item->day_date->toDateString() }}</td>
                        <td class="py-2 px-3">{{ $item->start_time->format('H:i') }}–{{ $item->end_time->format('H:i') }}</td>
                        <td class="py-2 px-3">{{ $item->title_en }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.agenda-items.edit', [$event, $item]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.agenda-items.destroy', [$event, $item]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 7: Create the shared create/edit form view**

```blade
{{-- resources/views/admin/agenda-items/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $item->exists ? __('Edit Agenda Item') : __('New Agenda Item') }}</h1>
        <form method="POST" action="{{ $item->exists ? route('admin.events.agenda-items.update', [$event, $item]) : route('admin.events.agenda-items.store', $event) }}">
            @csrf
            @if($item->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label>{{ __('Day') }}</label>
                    <input type="date" name="day_date" value="{{ old('day_date', optional($item->day_date)->toDateString()) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('day_date') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Start Time') }}</label>
                    <input type="time" name="start_time" value="{{ old('start_time', optional($item->start_time)->format('H:i')) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('start_time') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('End Time') }}</label>
                    <input type="time" name="end_time" value="{{ old('end_time', optional($item->end_time)->format('H:i')) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('end_time') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Title (Arabic)') }}</label>
                    <input name="title_ar" value="{{ old('title_ar', $item->title_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('title_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Title (English)') }}</label>
                    <input name="title_en" value="{{ old('title_en', $item->title_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('title_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <label>{{ __('Type') }}</label>
            <select name="type" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @foreach($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $item->type?->value) === $type->value)>{{ ucfirst($type->value) }}</option>
                @endforeach
            </select>

            <label>{{ __('Speaker') }}</label>
            <select name="speaker_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                <option value="">{{ __('None') }}</option>
                @foreach($speakers as $speaker)
                    <option value="{{ $speaker->id }}" @selected(old('speaker_id', $item->speaker_id) === $speaker->id)>{{ $speaker->name_en }}</option>
                @endforeach
            </select>

            <label>{{ __('Workshop') }}</label>
            <select name="workshop_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                <option value="">{{ __('None') }}</option>
                @foreach($workshops as $workshop)
                    <option value="{{ $workshop->id }}" @selected(old('workshop_id', $item->workshop_id) === $workshop->id)>{{ $workshop->name_en }}</option>
                @endforeach
            </select>

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=Admin/AgendaItemCrudTest`
Expected: PASS (3 passed)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Admin/AgendaItemController.php app/Http/Requests/Admin/AgendaItemRequest.php resources/views/admin/agenda-items routes/web.php tests/Feature/Admin/AgendaItemCrudTest.php
git commit -m "feat: admin Agenda Items CRUD"
```

### Task 26: Admin FAQs CRUD

Nested under an Event: `/admin/events/{event}/faqs`.

**Files:**
- Create: `app/Http/Controllers/Admin/FaqController.php`
- Create: `app/Http/Requests/Admin/FaqRequest.php`
- Create: `resources/views/admin/faqs/index.blade.php`
- Create: `resources/views/admin/faqs/form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/FaqCrudTest.php`

**Interfaces:**
- Consumes: `Event`, `Faq` models (Phase B), `auth` group (Task 3).
- Produces: named routes `admin.events.faqs.index/create/store/edit/update/destroy`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_faq(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.faqs.store', $event), [
            'question_ar' => 'كيف أدفع؟', 'question_en' => 'How do I pay?',
            'answer_ar' => 'بعد الموافقة', 'answer_en' => 'After approval', 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.faqs.index', $event));
        $this->assertDatabaseHas('faqs', ['event_id' => $event->id, 'question_en' => 'How do I pay?']);
    }

    public function test_creating_a_faq_requires_bilingual_answer(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.faqs.store', $event), [
            'question_ar' => 'كيف أدفع؟', 'question_en' => 'How do I pay?',
            'answer_ar' => '', 'answer_en' => '',
        ]);

        $response->assertSessionHasErrors(['answer_ar', 'answer_en']);
    }

    public function test_admin_can_delete_a_faq(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $faq = Faq::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.faqs.destroy', [$event, $faq]));

        $response->assertRedirect(route('admin.events.faqs.index', $event));
        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=Admin/FaqCrudTest`
Expected: FAIL (route `admin.events.faqs.store` not defined)

- [ ] **Step 3: Create the Form Request**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_ar' => ['required', 'string', 'max:255'],
            'question_en' => ['required', 'string', 'max:255'],
            'answer_ar' => ['required', 'string'],
            'answer_en' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Event;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FaqController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.faqs.index', ['event' => $event, 'faqs' => $event->faqs]);
    }

    public function create(Event $event): View
    {
        return view('admin.faqs.form', ['event' => $event, 'faq' => new Faq()]);
    }

    public function store(FaqRequest $request, Event $event): RedirectResponse
    {
        $event->faqs()->create($request->validated());

        return redirect()->route('admin.events.faqs.index', $event);
    }

    public function edit(Event $event, Faq $faq): View
    {
        $this->assertBelongsToEvent($event, $faq);

        return view('admin.faqs.form', ['event' => $event, 'faq' => $faq]);
    }

    public function update(FaqRequest $request, Event $event, Faq $faq): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $faq);
        $faq->update($request->validated());

        return redirect()->route('admin.events.faqs.index', $event);
    }

    public function destroy(Event $event, Faq $faq): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $faq);
        $faq->delete();

        return redirect()->route('admin.events.faqs.index', $event);
    }

    private function assertBelongsToEvent(Event $event, Faq $faq): void
    {
        if ($faq->event_id !== $event->id) {
            throw new NotFoundHttpException();
        }
    }
}
```

- [ ] **Step 5: Register the nested resource route**

```php
// routes/web.php — inside the existing auth-protected admin group
use App\Http\Controllers\Admin\FaqController;

Route::resource('events.faqs', FaqController::class)->except('show');
```

- [ ] **Step 6: Create the index view**

```blade
{{-- resources/views/admin/faqs/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('FAQs') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.faqs.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New FAQ') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Question') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($faqs as $faq)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $faq->question_en }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.faqs.edit', [$event, $faq]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.faqs.destroy', [$event, $faq]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 7: Create the shared create/edit form view**

```blade
{{-- resources/views/admin/faqs/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $faq->exists ? __('Edit FAQ') : __('New FAQ') }}</h1>
        <form method="POST" action="{{ $faq->exists ? route('admin.events.faqs.update', [$event, $faq]) : route('admin.events.faqs.store', $event) }}">
            @csrf
            @if($faq->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Question (Arabic)') }}</label>
                    <input name="question_ar" value="{{ old('question_ar', $faq->question_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('question_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Question (English)') }}</label>
                    <input name="question_en" value="{{ old('question_en', $faq->question_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('question_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Answer (Arabic)') }}</label>
                    <textarea name="answer_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">{{ old('answer_ar', $faq->answer_ar) }}</textarea>
                    @error('answer_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Answer (English)') }}</label>
                    <textarea name="answer_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">{{ old('answer_en', $faq->answer_en) }}</textarea>
                    @error('answer_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=Admin/FaqCrudTest`
Expected: PASS (3 passed)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Admin/FaqController.php app/Http/Requests/Admin/FaqRequest.php resources/views/admin/faqs routes/web.php tests/Feature/Admin/FaqCrudTest.php
git commit -m "feat: admin FAQs CRUD"
```

### Task 27: Admin Landing Page Content editor

Unlike Tasks 21–26, this isn't a list of independent records — it's one settings-style form per event covering the four known `landing_page_content` fields (Hero headline, About body, Location intro, Awards teaser blurb). Submitting it upserts all four rows at once via `updateOrCreate`, so re-saving never creates duplicates.

**Files:**
- Create: `app/Http/Controllers/Admin/LandingPageContentController.php`
- Create: `app/Http/Requests/Admin/LandingPageContentRequest.php`
- Create: `resources/views/admin/landing-page-content/edit.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/LandingPageContentCrudTest.php`

**Interfaces:**
- Consumes: `Event`, `LandingPageContent`, `LandingPageSection` (Phase B), `auth` group (Task 3).
- Produces: named routes `admin.events.content.edit` (`GET`), `admin.events.content.update` (`PUT`).

- [ ] **Step 1: Write the failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\LandingPageSection;
use App\Models\Event;
use App\Models\LandingPageContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageContentCrudTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'hero_headline_ar' => 'العنوان', 'hero_headline_en' => 'Headline',
            'about_body_ar' => 'نبذة', 'about_body_en' => 'About body',
            'location_intro_ar' => 'الموقع', 'location_intro_en' => 'Location intro',
            'awards_teaser_blurb_ar' => 'الجوائز', 'awards_teaser_blurb_en' => 'Awards blurb',
        ];
    }

    public function test_admin_can_set_landing_page_content(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.events.content.update', $event), $this->payload());

        $response->assertRedirect(route('admin.events.content.edit', $event));
        $this->assertSame(4, $event->landingPageContent()->count());
        $this->assertDatabaseHas('landing_page_content', [
            'event_id' => $event->id,
            'section' => LandingPageSection::Hero->value,
            'field_key' => 'headline',
            'value_en' => 'Headline',
        ]);
    }

    public function test_resubmitting_content_updates_instead_of_duplicating(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $this->actingAs($admin)->put(route('admin.events.content.update', $event), $this->payload());

        $updated = array_merge($this->payload(), ['hero_headline_en' => 'Updated Headline']);
        $this->actingAs($admin)->put(route('admin.events.content.update', $event), $updated);

        $this->assertSame(4, $event->landingPageContent()->count());
        $this->assertDatabaseHas('landing_page_content', [
            'event_id' => $event->id, 'field_key' => 'headline', 'value_en' => 'Updated Headline',
        ]);
    }

    public function test_edit_form_prefills_existing_values(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        LandingPageContent::factory()->for($event)->create([
            'section' => LandingPageSection::Hero, 'field_key' => 'headline', 'value_en' => 'Existing Headline',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.content.edit', $event));

        $response->assertSee('Existing Headline');
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=Admin/LandingPageContentCrudTest`
Expected: FAIL (route `admin.events.content.update` not defined)

- [ ] **Step 3: Create the Form Request**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LandingPageContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hero_headline_ar' => ['nullable', 'string'],
            'hero_headline_en' => ['nullable', 'string'],
            'about_body_ar' => ['nullable', 'string'],
            'about_body_en' => ['nullable', 'string'],
            'location_intro_ar' => ['nullable', 'string'],
            'location_intro_en' => ['nullable', 'string'],
            'awards_teaser_blurb_ar' => ['nullable', 'string'],
            'awards_teaser_blurb_en' => ['nullable', 'string'],
        ];
    }
}
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LandingPageSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingPageContentRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingPageContentController extends Controller
{
    /** @var array<string, array{section: LandingPageSection, field_key: string}> */
    private const FIELDS = [
        'hero_headline' => ['section' => LandingPageSection::Hero, 'field_key' => 'headline'],
        'about_body' => ['section' => LandingPageSection::About, 'field_key' => 'body'],
        'location_intro' => ['section' => LandingPageSection::Location, 'field_key' => 'intro'],
        'awards_teaser_blurb' => ['section' => LandingPageSection::AwardsTeaser, 'field_key' => 'blurb'],
    ];

    public function edit(Event $event): View
    {
        $values = [];
        foreach (self::FIELDS as $prefix => $target) {
            $content = $event->contentFor($target['section'], $target['field_key']);
            $values[$prefix . '_ar'] = $content?->value_ar;
            $values[$prefix . '_en'] = $content?->value_en;
        }

        return view('admin.landing-page-content.edit', ['event' => $event, 'values' => $values]);
    }

    public function update(LandingPageContentRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();

        foreach (self::FIELDS as $prefix => $target) {
            $event->landingPageContent()->updateOrCreate(
                ['section' => $target['section'], 'field_key' => $target['field_key']],
                ['value_ar' => $data[$prefix . '_ar'] ?? null, 'value_en' => $data[$prefix . '_en'] ?? null],
            );
        }

        return redirect()->route('admin.events.content.edit', $event);
    }
}
```

- [ ] **Step 5: Register the routes**

```php
// routes/web.php — inside the existing auth-protected admin group
use App\Http\Controllers\Admin\LandingPageContentController;

Route::get('events/{event}/content', [LandingPageContentController::class, 'edit'])->name('events.content.edit');
Route::put('events/{event}/content', [LandingPageContentController::class, 'update'])->name('events.content.update');
```

- [ ] **Step 6: Create the edit view**

```blade
{{-- resources/views/admin/landing-page-content/edit.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Landing Page Content') }} — {{ $event->name_en }}</h1>
        <form method="POST" action="{{ route('admin.events.content.update', $event) }}">
            @csrf
            @method('PUT')

            <h5 class="mt-4">{{ __('Hero Headline') }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><input name="hero_headline_ar" value="{{ old('hero_headline_ar', $values['hero_headline_ar']) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl" placeholder="{{ __('Arabic') }}"></div>
                <div><input name="hero_headline_en" value="{{ old('hero_headline_en', $values['hero_headline_en']) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" placeholder="{{ __('English') }}"></div>
            </div>

            <h5 class="mt-4">{{ __('About Body') }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><textarea name="about_body_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl" placeholder="{{ __('Arabic') }}">{{ old('about_body_ar', $values['about_body_ar']) }}</textarea></div>
                <div><textarea name="about_body_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" placeholder="{{ __('English') }}">{{ old('about_body_en', $values['about_body_en']) }}</textarea></div>
            </div>

            <h5 class="mt-4">{{ __('Location Intro') }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><textarea name="location_intro_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl" placeholder="{{ __('Arabic') }}">{{ old('location_intro_ar', $values['location_intro_ar']) }}</textarea></div>
                <div><textarea name="location_intro_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" placeholder="{{ __('English') }}">{{ old('location_intro_en', $values['location_intro_en']) }}</textarea></div>
            </div>

            <h5 class="mt-4">{{ __('Awards Teaser Blurb') }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><textarea name="awards_teaser_blurb_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl" placeholder="{{ __('Arabic') }}">{{ old('awards_teaser_blurb_ar', $values['awards_teaser_blurb_ar']) }}</textarea></div>
                <div><textarea name="awards_teaser_blurb_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" placeholder="{{ __('English') }}">{{ old('awards_teaser_blurb_en', $values['awards_teaser_blurb_en']) }}</textarea></div>
            </div>

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-4">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --filter=Admin/LandingPageContentCrudTest`
Expected: PASS (3 passed)

- [ ] **Step 8: Run the full test suite**

Run: `php artisan test`
Expected: all tests across every phase pass.

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Admin/LandingPageContentController.php app/Http/Requests/Admin/LandingPageContentRequest.php resources/views/admin/landing-page-content routes/web.php tests/Feature/Admin/LandingPageContentCrudTest.php
git commit -m "feat: admin Landing Page Content editor"
```
