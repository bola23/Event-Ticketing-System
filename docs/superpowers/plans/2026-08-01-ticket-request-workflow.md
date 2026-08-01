# Ticket Request Workflow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the static ticket-request form with a real submission flow — a `Ticket` model, a dynamic admin-configurable request form (Name/Email/Phone fixed + Instagram/Portfolio/CV admin-toggleable), file uploads, and an admin review/approve/reject queue.

**Architecture:** Three new tables (`tickets`, `ticket_request_fields`, `ticket_request_answers`) following this app's existing per-event-owned-resource pattern (`event_id` FK, admin CRUD nested under `admin/events/{event}/...`, public routes nested under `events/{event}/...`). The request form renders a fixed Name/Email/Phone block plus a loop over the event's configured `TicketRequestField`s. Submission creates a `Ticket` + one `TicketRequestAnswer` per dynamic field inside a DB transaction. Files go to Laravel 12's default `local` disk, which is already private (`storage_path('app/private')`, no public symlink) — admins retrieve them through an authenticated streaming route.

**Tech Stack:** Laravel 12 / PHP 8.3, Blade + AlpineJS, Tailwind CSS v4, PHPUnit. New: `propaganistas/laravel-phone` (composer, server-side phone validation) and `intl-tel-input` (npm, client-side phone widget).

## Global Constraints

- No user authentication anywhere in the public flow — the `Ticket` is the attendee's identity (per `.claude/CLAUDE.md` / `.claude/skills/event-domain.md`).
- Name regex: `/^[\pL\pM\s\'\-\.]+$/u` (unicode letters/marks/space/'-. only).
- Email rule: `email:rfc` (not `email:rfc,dns` — the `dns` check does a live DNS lookup, which is fragile in sandboxed/CI environments and inconsistent with the rest of the app's plain `email` rule; discovered and fixed during Task 6 execution).
- Phone: server-side via `(new \Propaganistas\LaravelPhone\Rules\Phone)->international()`; client-side via `intl-tel-input`, converting the input's value to E.164 (`iti.getNumber()`) immediately before submit.
- Instagram regex: `/^@?[A-Za-z0-9_.]{1,30}$/`.
- Portfolio: URL mode → `url|max:2048`. PDF mode → `file|mimes:pdf|max:5120`.
- CV: `file|mimes:pdf,doc,docx|max:5120`.
- Dynamic field input naming (must match exactly between the GET form and the POST handler): `field_{fieldId}` for Instagram; `field_{fieldId}_mode` (`url`|`pdf`), `field_{fieldId}_url`, `field_{fieldId}_file` for Portfolio; `field_{fieldId}` (file) for CV.
- File storage: the `local` disk (NOT `public`) — this is a deviation from the spec's literal wording ("new disk config") because inspecting `config/filesystems.php` showed Laravel 12's default `local` disk already has `root => storage_path('app/private')` with no public symlink, i.e. it's already private. Reuse it; do not add a new disk.
- `ticket_number` format: uppercased event slug with dashes stripped, `-`, then the ticket's `id` zero-padded to 6 digits. Example: slug `ccs-2026` → `CCS2026-000042`.
- `ticket_id`, `workshop_booking_key`, `is_paid`, `payment_method`, `checked_in_at` columns exist on `tickets` now but this plan NEVER sets them (Payment/QR phases do) — every task that creates a `Ticket` must leave these at their column defaults (`null`/`false`).
- XSS/SQLi: all persistence via Eloquent (no raw SQL); every place a stored answer is echoed in a view uses `{{ }}`, never `{!! !!}`.
- Every new file must start with `<?php\n\ndeclare(strict_types=1);` and PHP files must pass `vendor/bin/pint --dirty --format agent` before a task's commit.
- Run `php artisan test --compact --filter=<RelevantTest>` after each task; the plan's final step is a full-suite run.

---

### Task 1: New dependencies

**Files:**
- Modify: `composer.json` (via `composer require`)
- Modify: `package.json` (via `npm install`)

**Interfaces:**
- Produces: `propaganistas/laravel-phone` v6, providing `Propaganistas\LaravelPhone\Rules\Phone`.
- Produces: `intl-tel-input` v29, importable as `intl-tel-input/intlTelInputWithUtils` (bundles validation utils, no separate `utilsScript` URL needed) and `intl-tel-input/styles` (CSS).

- [ ] **Step 1: Install the composer package**

Run: `composer require propaganistas/laravel-phone`

Expected: `composer.json` gains `"propaganistas/laravel-phone": "^6.0"` under `require`; `composer.lock` updates.

- [ ] **Step 2: Verify the rule class autoloads**

Run: `php artisan tinker --execute 'echo class_exists(\Propaganistas\LaravelPhone\Rules\Phone::class) ? "OK" : "MISSING";'`

Expected: `OK`

- [ ] **Step 3: Install the npm package**

Run: `npm install intl-tel-input`

Expected: `package.json` gains `"intl-tel-input": "^29.0.0"` under `dependencies`.

- [ ] **Step 4: Verify the build still succeeds**

Run: `npm run build`

Expected: build completes with no errors (the package isn't imported anywhere yet — this just confirms `npm install` didn't break the toolchain).

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock package.json package-lock.json
git commit -m "chore: add propaganistas/laravel-phone and intl-tel-input dependencies"
```

---

### Task 2: Data model — Ticket, TicketRequestField, TicketRequestAnswer

**Files:**
- Create: `app/Enums/TicketStatus.php`
- Create: `app/Enums/TicketRequestFieldType.php`
- Create: `database/migrations/2026_08_01_100000_create_tickets_table.php`
- Create: `database/migrations/2026_08_01_100001_create_ticket_request_fields_table.php`
- Create: `database/migrations/2026_08_01_100002_create_ticket_request_answers_table.php`
- Create: `app/Models/Ticket.php`
- Create: `app/Models/TicketRequestField.php`
- Create: `app/Models/TicketRequestAnswer.php`
- Modify: `app/Models/Event.php`
- Create: `database/factories/TicketFactory.php`
- Create: `database/factories/TicketRequestFieldFactory.php`
- Create: `database/factories/TicketRequestAnswerFactory.php`
- Test: `tests/Unit/Models/TicketTest.php`

**Interfaces:**
- Produces: `Ticket::$fillable` = `event_id, ticket_type_id, name, email, phone, ticket_number, status, ticket_id, workshop_booking_key, is_paid, payment_method, checked_in_at`; casts `status` → `TicketStatus`, `is_paid` → `boolean`, `checked_in_at` → `datetime`.
- Produces: `Ticket::event(): BelongsTo`, `Ticket::ticketType(): BelongsTo`, `Ticket::answers(): HasMany` (→ `TicketRequestAnswer`).
- Produces: `TicketRequestField::$fillable` = `event_id, type, label_ar, label_en, is_required, sort_order`; casts `type` → `TicketRequestFieldType`, `is_required` → `boolean`.
- Produces: `TicketRequestField::event(): BelongsTo`.
- Produces: `TicketRequestAnswer::$fillable` = `ticket_id, ticket_request_field_id, value, file_path`.
- Produces: `TicketRequestAnswer::ticket(): BelongsTo`, `TicketRequestAnswer::field(): BelongsTo` (→ `TicketRequestField`, FK `ticket_request_field_id`).
- Produces: `Event::tickets(): HasMany`, `Event::ticketRequestFields(): HasMany` (ordered by `sort_order`).
- Produces: `TicketStatus` cases `Pending, Approved, Rejected, PaymentPending, Paid, TicketIssued, CheckedIn, Cancelled` (string-backed, snake_case values e.g. `payment_pending`).
- Produces: `TicketRequestFieldType` cases `Instagram, Portfolio, Cv` (string-backed: `instagram`, `portfolio`, `cv`).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\TicketRequestFieldType;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketRequestAnswer;
use App\Models\TicketRequestField;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_belongs_to_event_and_ticket_type(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create();
        $ticket = Ticket::factory()->for($event)->create(['ticket_type_id' => $ticketType->id]);

        $this->assertTrue($ticket->event->is($event));
        $this->assertTrue($ticket->ticketType->is($ticketType));
    }

    public function test_ticket_status_casts_to_enum_and_defaults_to_pending(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertSame(TicketStatus::Pending, $ticket->status);
    }

    public function test_ticket_forward_looking_columns_default_unset(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertNull($ticket->ticket_id);
        $this->assertNull($ticket->workshop_booking_key);
        $this->assertFalse($ticket->is_paid);
        $this->assertNull($ticket->payment_method);
        $this->assertNull($ticket->checked_in_at);
    }

    public function test_ticket_has_many_answers(): void
    {
        $ticket = Ticket::factory()->create();
        $field = TicketRequestField::factory()->for($ticket->event)->create(['type' => TicketRequestFieldType::Instagram]);
        TicketRequestAnswer::factory()->create(['ticket_id' => $ticket->id, 'ticket_request_field_id' => $field->id, 'value' => '@someone']);

        $this->assertCount(1, $ticket->answers);
        $this->assertSame('@someone', $ticket->answers->first()->value);
    }

    public function test_ticket_request_field_belongs_to_event_and_casts_type(): void
    {
        $event = Event::factory()->create();
        $field = TicketRequestField::factory()->for($event)->create(['type' => TicketRequestFieldType::Portfolio]);

        $this->assertTrue($field->event->is($event));
        $this->assertSame(TicketRequestFieldType::Portfolio, $field->type);
    }

    public function test_event_ticket_request_fields_ordered_by_sort_order(): void
    {
        $event = Event::factory()->create();
        TicketRequestField::factory()->for($event)->create(['sort_order' => 2, 'label_en' => 'Second']);
        TicketRequestField::factory()->for($event)->create(['sort_order' => 1, 'label_en' => 'First']);

        $this->assertSame(['First', 'Second'], $event->ticketRequestFields->pluck('label_en')->all());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/Models/TicketTest.php`
Expected: FAIL — classes `App\Models\Ticket`, `TicketRequestField`, `TicketRequestAnswer`, enums don't exist yet.

- [ ] **Step 3: Create the enums**

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case PaymentPending = 'payment_pending';
    case Paid = 'paid';
    case TicketIssued = 'ticket_issued';
    case CheckedIn = 'checked_in';
    case Cancelled = 'cancelled';
}
```

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketRequestFieldType: string
{
    case Instagram = 'instagram';
    case Portfolio = 'portfolio';
    case Cv = 'cv';
}
```

- [ ] **Step 4: Create the migrations**

`database/migrations/2026_08_01_100000_create_tickets_table.php`:

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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 32);
            $table->string('ticket_number', 32)->nullable()->unique();
            $table->string('status', 20)->default('pending');
            $table->string('ticket_id', 40)->nullable()->unique();
            $table->string('workshop_booking_key', 40)->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('payment_method', 50)->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
```

`database/migrations/2026_08_01_100001_create_ticket_request_fields_table.php`:

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
        Schema::create('ticket_request_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('label_ar');
            $table->string('label_en');
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_request_fields');
    }
};
```

`database/migrations/2026_08_01_100002_create_ticket_request_answers_table.php`:

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
        Schema::create('ticket_request_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_request_field_id')->constrained()->cascadeOnDelete();
            $table->string('value', 2048)->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_request_answers');
    }
};
```

- [ ] **Step 5: Create the models**

`app/Models/Ticket.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'ticket_type_id', 'name', 'email', 'phone', 'ticket_number',
        'status', 'ticket_id', 'workshop_booking_key', 'is_paid', 'payment_method', 'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'is_paid' => 'boolean',
            'checked_in_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TicketRequestAnswer::class);
    }

    protected static function newFactory(): TicketFactory
    {
        return TicketFactory::new();
    }
}
```

`app/Models/TicketRequestField.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketRequestFieldType;
use Database\Factories\TicketRequestFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRequestField extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'type', 'label_ar', 'label_en', 'is_required', 'sort_order'];

    protected function casts(): array
    {
        return [
            'type' => TicketRequestFieldType::class,
            'is_required' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): TicketRequestFieldFactory
    {
        return TicketRequestFieldFactory::new();
    }
}
```

`app/Models/TicketRequestAnswer.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TicketRequestAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRequestAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'ticket_request_field_id', 'value', 'file_path'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(TicketRequestField::class, 'ticket_request_field_id');
    }

    protected static function newFactory(): TicketRequestAnswerFactory
    {
        return TicketRequestAnswerFactory::new();
    }
}
```

- [ ] **Step 6: Add relations to Event**

In `app/Models/Event.php`, add these two methods next to the other `HasMany` relations (e.g. after `testimonials()`), and add `use App\Models\Ticket;`/`use App\Models\TicketRequestField;` are not needed since both are in the same `App\Models` namespace:

```php
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketRequestFields(): HasMany
    {
        return $this->hasMany(TicketRequestField::class)->orderBy('sort_order');
    }
```

- [ ] **Step 7: Create the factories**

`database/factories/TicketFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'ticket_type_id' => TicketType::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => '+201001234567',
            'ticket_number' => strtoupper($this->faker->bothify('TKT-######')),
            'status' => 'pending',
        ];
    }
}
```

`database/factories/TicketRequestFieldFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketRequestField;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketRequestFieldFactory extends Factory
{
    protected $model = TicketRequestField::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'type' => 'instagram',
            'label_ar' => 'إنستغرام',
            'label_en' => 'Instagram',
            'is_required' => false,
            'sort_order' => 0,
        ];
    }
}
```

`database/factories/TicketRequestAnswerFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketRequestAnswer;
use App\Models\TicketRequestField;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketRequestAnswerFactory extends Factory
{
    protected $model = TicketRequestAnswer::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'ticket_request_field_id' => TicketRequestField::factory(),
            'value' => '@'.$this->faker->userName(),
            'file_path' => null,
        ];
    }
}
```

- [ ] **Step 8: Run test to verify it passes**

Run: `php artisan test --compact tests/Unit/Models/TicketTest.php`
Expected: PASS (6 tests)

- [ ] **Step 9: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 10: Commit**

```bash
git add app/Enums/TicketStatus.php app/Enums/TicketRequestFieldType.php \
  database/migrations/2026_08_01_100000_create_tickets_table.php \
  database/migrations/2026_08_01_100001_create_ticket_request_fields_table.php \
  database/migrations/2026_08_01_100002_create_ticket_request_answers_table.php \
  app/Models/Ticket.php app/Models/TicketRequestField.php app/Models/TicketRequestAnswer.php \
  app/Models/Event.php \
  database/factories/TicketFactory.php database/factories/TicketRequestFieldFactory.php database/factories/TicketRequestAnswerFactory.php \
  tests/Unit/Models/TicketTest.php
git commit -m "feat: add Ticket, TicketRequestField, TicketRequestAnswer data model"
```

---

### Task 3: Reusable file drop-zone component

**Files:**
- Create: `resources/views/components/file-dropzone.blade.php`
- Test: `tests/Feature/FileDropzoneComponentTest.php`

**Interfaces:**
- Produces: `<x-file-dropzone :name="string" :accept="string|null" :label="string|null" />` — renders a `<input type="file" name="{name}">` (visually hidden, triggered via a styled button/drag area) with `accept="{accept}"` when given.
- Consumes: nothing from earlier tasks (pure UI component, usable in Task 5's public form).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class FileDropzoneComponentTest extends TestCase
{
    public function test_renders_file_input_with_name_and_accept(): void
    {
        $html = view('components.file-dropzone', ['name' => 'cv_file', 'accept' => '.pdf,.doc,.docx'])->render();

        $this->assertStringContainsString('name="cv_file"', $html);
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('accept=".pdf,.doc,.docx"', $html);
    }

    public function test_renders_without_accept_attribute_when_omitted(): void
    {
        $html = view('components.file-dropzone', ['name' => 'portfolio_file'])->render();

        $this->assertStringContainsString('name="portfolio_file"', $html);
        $this->assertStringNotContainsString('accept=', $html);
    }

    public function test_renders_label_when_given(): void
    {
        $html = view('components.file-dropzone', ['name' => 'cv_file', 'label' => 'Upload your CV'])->render();

        $this->assertStringContainsString('Upload your CV', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/FileDropzoneComponentTest.php`
Expected: FAIL — `View [components.file-dropzone] not found`

- [ ] **Step 3: Create the component**

```blade
{{-- resources/views/components/file-dropzone.blade.php --}}
@props(['name', 'accept' => null, 'label' => null])

<div x-data="{ fileName: null }">
    @if($label)
        <label class="block text-sm text-gray-300 mb-2">{{ $label }}</label>
    @endif

    <div
        class="border-2 border-dashed border-gray-600 rounded-lg p-6 text-center transition-colors hover:border-ccs-teal-light"
        @dragover.prevent
        @drop.prevent="$refs.input.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0]?.name ?? null"
    >
        <input
            type="file"
            name="{{ $name }}"
            x-ref="input"
            @if($accept) accept="{{ $accept }}" @endif
            class="hidden"
            @change="fileName = $event.target.files[0]?.name ?? null"
        >
        <button type="button" @click="$refs.input.click()" class="text-sm font-semibold text-ccs-teal-light hover:underline">
            {{ __('Choose a file or drag it here') }}
        </button>
        <p class="text-xs text-gray-500 mt-2" x-show="fileName" x-cloak x-text="fileName"></p>
    </div>
</div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/FileDropzoneComponentTest.php`
Expected: PASS (3 tests)

- [ ] **Step 5: Add the translation key**

In `lang/en.json`, add (alphabetically, near "Choose your own workshops."):
```json
"Choose a file or drag it here": "Choose a file or drag it here",
```

In `lang/ar.json`, add at the same key:
```json
"Choose a file or drag it here": "اختر ملفًا أو اسحبه هنا",
```

- [ ] **Step 6: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/file-dropzone.blade.php tests/Feature/FileDropzoneComponentTest.php lang/en.json lang/ar.json
git commit -m "feat: add reusable file drop-zone component"
```

---

### Task 4: Admin — Request Form builder

**Files:**
- Create: `app/Http/Requests/Admin/TicketRequestFieldRequest.php`
- Create: `app/Http/Controllers/Admin/TicketRequestFieldController.php`
- Create: `resources/views/admin/request-form-fields/index.blade.php`
- Create: `resources/views/admin/request-form-fields/form.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/admin/partials/sidebar.blade.php`
- Test: `tests/Feature/Admin/TicketRequestFieldCrudTest.php`

**Interfaces:**
- Consumes: `TicketRequestField` model + factory from Task 2; `TicketRequestFieldType` enum from Task 2.
- Produces: routes `admin.events.request-form-fields.{index,create,store,edit,update,destroy}`, route-model-bound as `Event $event, TicketRequestField $requestField`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\TicketRequestField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketRequestFieldCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_request_field(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.request-form-fields.store', $event), [
            'type' => 'portfolio', 'label_ar' => 'ملف الأعمال', 'label_en' => 'Portfolio',
            'is_required' => 1, 'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.events.request-form-fields.index', $event));
        $this->assertDatabaseHas('ticket_request_fields', [
            'event_id' => $event->id, 'type' => 'portfolio', 'label_en' => 'Portfolio', 'is_required' => 1,
        ]);
    }

    public function test_admin_can_update_a_request_field(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $field = TicketRequestField::factory()->for($event)->create(['label_en' => 'Old']);

        $response = $this->actingAs($admin)->put(route('admin.events.request-form-fields.update', [$event, $field]), [
            'type' => 'instagram', 'label_ar' => $field->label_ar, 'label_en' => 'New',
            'is_required' => 0, 'sort_order' => 1,
        ]);

        $response->assertRedirect(route('admin.events.request-form-fields.index', $event));
        $this->assertDatabaseHas('ticket_request_fields', ['id' => $field->id, 'label_en' => 'New']);
    }

    public function test_admin_can_delete_a_request_field(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $field = TicketRequestField::factory()->for($event)->create();

        $response = $this->actingAs($admin)->delete(route('admin.events.request-form-fields.destroy', [$event, $field]));

        $response->assertRedirect(route('admin.events.request-form-fields.index', $event));
        $this->assertDatabaseMissing('ticket_request_fields', ['id' => $field->id]);
    }

    public function test_creating_a_request_field_requires_a_valid_type(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.request-form-fields.store', $event), [
            'type' => 'not-a-real-type', 'label_ar' => 'x', 'label_en' => 'x',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_admin_can_view_the_index_page(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        TicketRequestField::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.request-form-fields.index', $event));

        $response->assertOk();
    }

    public function test_a_request_field_from_another_event_returns_404_on_edit(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $otherEvent = Event::factory()->create();
        $field = TicketRequestField::factory()->for($otherEvent)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.request-form-fields.edit', [$event, $field]));

        $response->assertStatus(404);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Admin/TicketRequestFieldCrudTest.php`
Expected: FAIL — route `admin.events.request-form-fields.store` not defined.

- [ ] **Step 3: Create the FormRequest**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TicketRequestFieldType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketRequestFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_column(TicketRequestFieldType::cases(), 'value'))],
            'label_ar' => ['required', 'string', 'max:255'],
            'label_en' => ['required', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_required' => $this->boolean('is_required')]);
    }
}
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketRequestFieldRequest;
use App\Models\Event;
use App\Models\TicketRequestField;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketRequestFieldController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.request-form-fields.index', ['event' => $event, 'requestFields' => $event->ticketRequestFields]);
    }

    public function create(Event $event): View
    {
        return view('admin.request-form-fields.form', ['event' => $event, 'requestField' => new TicketRequestField]);
    }

    public function store(TicketRequestFieldRequest $request, Event $event): RedirectResponse
    {
        $event->ticketRequestFields()->create($request->validated());

        return redirect()->route('admin.events.request-form-fields.index', $event);
    }

    public function edit(Event $event, TicketRequestField $requestField): View
    {
        $this->assertBelongsToEvent($event, $requestField);

        return view('admin.request-form-fields.form', ['event' => $event, 'requestField' => $requestField]);
    }

    public function update(TicketRequestFieldRequest $request, Event $event, TicketRequestField $requestField): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $requestField);
        $requestField->update($request->validated());

        return redirect()->route('admin.events.request-form-fields.index', $event);
    }

    public function destroy(Event $event, TicketRequestField $requestField): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $requestField);
        $requestField->delete();

        return redirect()->route('admin.events.request-form-fields.index', $event);
    }

    private function assertBelongsToEvent(Event $event, TicketRequestField $requestField): void
    {
        if ($requestField->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
```

- [ ] **Step 5: Add the routes**

In `routes/web.php`, add the import near the other `Admin\` imports:

```php
use App\Http\Controllers\Admin\TicketRequestFieldController;
```

And inside the existing `Route::middleware('auth')->group(function () { ... })` block in the admin section, add (near the other `Route::resource('events.*', ...)` lines):

```php
        Route::resource('events.request-form-fields', TicketRequestFieldController::class)
            ->except('show')
            ->parameters(['request-form-fields' => 'requestField']);
```

- [ ] **Step 6: Create the views**

`resources/views/admin/request-form-fields/index.blade.php`:

```blade
{{-- resources/views/admin/request-form-fields/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Request Form').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.request-form-fields.create', $event) }}">{{ __('New Request Field') }}</x-admin.button>
    </x-admin.page-header>

    @if($requestFields->isEmpty())
        <x-admin.empty-state :message="__('No request fields yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Type') }}</th>
                    <th class="py-2 px-3">{{ __('Label') }}</th>
                    <th class="py-2 px-3">{{ __('Required') }}</th>
                    <th class="py-2 px-3">{{ __('Sort Order') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($requestFields as $requestField)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ ucfirst($requestField->type->value) }}</td>
                        <td class="py-2 px-3">{{ $requestField->label_en }}</td>
                        <td class="py-2 px-3">{{ $requestField->is_required ? __('Yes') : __('No') }}</td>
                        <td class="py-2 px-3">{{ $requestField->sort_order }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.request-form-fields.edit', [$event, $requestField]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.request-form-fields.destroy', [$event, $requestField]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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

`resources/views/admin/request-form-fields/form.blade.php`:

```blade
{{-- resources/views/admin/request-form-fields/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$requestField->exists ? __('Edit Request Field') : __('New Request Field')" />

    <form method="POST" action="{{ $requestField->exists ? route('admin.events.request-form-fields.update', [$event, $requestField]) : route('admin.events.request-form-fields.store', $event) }}">
        @csrf
        @if($requestField->exists) @method('PUT') @endif

        <x-admin.field type="select" name="type" label="{{ __('Type') }}">
            @foreach(\App\Enums\TicketRequestFieldType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('type', $requestField->type?->value) === $type->value)>{{ ucfirst($type->value) }}</option>
            @endforeach
        </x-admin.field>

        <x-admin.bilingual-field name="label" label="{{ __('Label') }}" :value-ar="old('label_ar', $requestField->label_ar)" :value-en="old('label_en', $requestField->label_en)" />

        <x-admin.field type="checkbox" name="is_required" label="{{ __('Required') }}" :checked="old('is_required', $requestField->is_required ?? false)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $requestField->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
```

- [ ] **Step 7: Add the sidebar link**

In `resources/views/admin/partials/sidebar.blade.php`, add to the `$eventNavItems` array (after the `admin.events.faqs` entry):

```php
                        ['prefix' => 'admin.events.request-form-fields', 'route' => 'admin.events.request-form-fields.index', 'label' => __('Request Form')],
```

- [ ] **Step 8: Add translation keys**

In `lang/en.json`, add (alphabetically):
```json
"Request Form": "Request Form",
"New Request Field": "New Request Field",
"Edit Request Field": "Edit Request Field",
"No request fields yet.": "No request fields yet.",
"Label": "Label",
"Required": "Required",
"Yes": "Yes",
"No": "No",
```
(Skip any that already exist in the file — check first; `"Type"` and `"Sort Order"` already exist, don't duplicate them.)

In `lang/ar.json`, add the same keys with:
```json
"Request Form": "نموذج الطلب",
"New Request Field": "حقل طلب جديد",
"Edit Request Field": "تعديل حقل الطلب",
"No request fields yet.": "لا توجد حقول طلب بعد.",
"Label": "التسمية",
"Required": "مطلوب",
"Yes": "نعم",
"No": "لا",
```

- [ ] **Step 9: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/Admin/TicketRequestFieldCrudTest.php`
Expected: PASS (6 tests)

- [ ] **Step 10: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 11: Commit**

```bash
git add app/Http/Requests/Admin/TicketRequestFieldRequest.php app/Http/Controllers/Admin/TicketRequestFieldController.php \
  resources/views/admin/request-form-fields routes/web.php resources/views/admin/partials/sidebar.blade.php \
  lang/en.json lang/ar.json tests/Feature/Admin/TicketRequestFieldCrudTest.php
git commit -m "feat: add admin request-form field builder"
```

---

### Task 5: Public — dynamic request form (GET)

**Files:**
- Modify: `app/Http/Controllers/TicketRequestController.php`
- Modify: `resources/views/ticket-requests/create.blade.php`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/TicketRequestFormTest.php`

**Interfaces:**
- Consumes: `TicketRequestField`, `TicketRequestFieldType` from Task 2; `<x-file-dropzone>` from Task 3; `intl-tel-input` from Task 1.
- Produces: the `#phone` input id (consumed by the JS init in `app.js`); the `field_{fieldId}[...]` input naming from Global Constraints (consumed by Task 6's POST handler).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\TicketRequestField;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketRequestFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_shows_fixed_name_email_phone_fields(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);

        $response = $this->get(route('ticket-requests.create', $event));

        $response->assertOk();
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('id="phone"', false);
    }

    public function test_form_renders_configured_dynamic_fields_in_order(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $portfolio = TicketRequestField::factory()->for($event)->create(['type' => 'portfolio', 'label_en' => 'Portfolio', 'sort_order' => 1]);
        $instagram = TicketRequestField::factory()->for($event)->create(['type' => 'instagram', 'label_en' => 'Instagram', 'sort_order' => 0]);

        $response = $this->get(route('ticket-requests.create', $event).'?lang=en');

        $response->assertOk();
        $response->assertSeeInOrder(['Instagram', 'Portfolio']);
        $response->assertSee('name="field_'.$instagram->id.'"', false);
        $response->assertSee('name="field_'.$portfolio->id.'_mode"', false);
        $response->assertSee('name="field_'.$portfolio->id.'_url"', false);
        $response->assertSee('name="field_'.$portfolio->id.'_file"', false);
    }

    public function test_form_renders_cv_field_as_dropzone(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $cv = TicketRequestField::factory()->for($event)->create(['type' => 'cv', 'label_en' => 'CV']);

        $response = $this->get(route('ticket-requests.create', $event));

        $response->assertSee('name="field_'.$cv->id.'"', false);
        $response->assertSee('type="file"', false);
    }

    public function test_form_omitted_fields_do_not_render_when_event_has_none(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        TicketType::factory()->for($event)->create();

        $response = $this->get(route('ticket-requests.create', $event));

        $response->assertOk();
        $response->assertDontSee('field_', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/TicketRequestFormTest.php`
Expected: FAIL — current form has no `name`/`email`/`phone` inputs and doesn't loop dynamic fields.

- [ ] **Step 3: Update the controller**

Replace the full contents of `app/Http/Controllers/TicketRequestController.php`'s `create` method — keep the class/namespace, just add `requestFields`:

```php
    public function create(Event $event, Request $request): View
    {
        return view('ticket-requests.create', [
            'event' => $event,
            'ticketTypes' => $event->ticketTypes()->where('is_active', true)->get(),
            'selectedTicketTypeId' => (int) $request->query('type'),
            'requestFields' => $event->ticketRequestFields,
        ]);
    }
```

- [ ] **Step 4: Rewrite the view**

```blade
{{-- resources/views/ticket-requests/create.blade.php --}}
@extends('layouts.app')

@section('title', __('Request a Ticket'))

@section('content')
    <div class="container mx-auto px-4 py-5 max-w-xl">
        <h1 class="font-display text-2xl font-bold mb-6">{{ __('Request Your Ticket') }}</h1>

        @if(session('ticket_request_success'))
            <p class="text-sm font-bold text-ccs-teal-light mb-4">
                {{ __('Request received! Your reference number is :number.', ['number' => session('ticket_request_success')]) }}
            </p>
        @endif

        <form method="POST" action="{{ route('ticket-requests.store', $event) }}" enctype="multipart/form-data" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="ticket_type_id" class="block text-sm text-gray-300 mb-1">{{ __('Ticket Type') }}</label>
                <select id="ticket_type_id" name="ticket_type_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @foreach($ticketTypes as $ticketType)
                        <option value="{{ $ticketType->id }}" @selected($ticketType->id === $selectedTicketTypeId)>
                            {{ app()->getLocale() === 'ar' ? $ticketType->name_ar : $ticketType->name_en }} — {{ $ticketType->price }} {{ $ticketType->currency }}
                        </option>
                    @endforeach
                </select>
                @error('ticket_type_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-sm text-gray-300 mb-1">{{ __('Name') }}</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm text-gray-300 mb-1">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm text-gray-300 mb-1">{{ __('Phone') }}</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @error('phone') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            @foreach($requestFields as $field)
                @php $inputKey = 'field_'.$field->id; @endphp
                <div>
                    <label class="block text-sm text-gray-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? $field->label_ar : $field->label_en }}
                        @if($field->is_required)<span class="text-red-400">*</span>@endif
                    </label>

                    @if($field->type === \App\Enums\TicketRequestFieldType::Instagram)
                        <input type="text" name="{{ $inputKey }}" value="{{ old($inputKey) }}" placeholder="@username" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                        @error($inputKey) <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    @elseif($field->type === \App\Enums\TicketRequestFieldType::Portfolio)
                        <div x-data="{ mode: '{{ old($inputKey.'_mode', 'url') }}' }" class="flex flex-col gap-3">
                            <div class="flex gap-4 text-sm">
                                <label class="flex items-center gap-2"><input type="radio" name="{{ $inputKey }}_mode" value="url" x-model="mode"> {{ __('URL') }}</label>
                                <label class="flex items-center gap-2"><input type="radio" name="{{ $inputKey }}_mode" value="pdf" x-model="mode"> {{ __('PDF') }}</label>
                            </div>
                            <input x-show="mode === 'url'" type="url" name="{{ $inputKey }}_url" value="{{ old($inputKey.'_url') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                            <div x-show="mode === 'pdf'" x-cloak>
                                <x-file-dropzone :name="$inputKey.'_file'" accept=".pdf" />
                            </div>
                        </div>
                        @error($inputKey.'_mode') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        @error($inputKey.'_url') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        @error($inputKey.'_file') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    @elseif($field->type === \App\Enums\TicketRequestFieldType::Cv)
                        <x-file-dropzone :name="$inputKey" accept=".pdf,.doc,.docx" />
                        @error($inputKey) <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    @endif
                </div>
            @endforeach

            <button type="submit" class="px-6 py-3 rounded bg-ccs-red hover:bg-ccs-maroon text-white font-bold">{{ __('Submit Request') }}</button>
        </form>
    </div>
@endsection
```

- [ ] **Step 5: Wire up intl-tel-input in app.js**

In `resources/js/app.js`, add near the top (after existing imports, before the scroll-reveal code):

```js
import intlTelInput from 'intl-tel-input/intlTelInputWithUtils';
import 'intl-tel-input/styles';

const phoneInput = document.querySelector('#phone');
if (phoneInput) {
    const iti = intlTelInput(phoneInput, {
        initialCountry: 'eg',
    });

    phoneInput.closest('form')?.addEventListener('submit', () => {
        phoneInput.value = iti.getNumber();
    });
}
```

- [ ] **Step 6: Add translation keys**

In `lang/en.json`, add:
```json
"Request Your Ticket": "Request Your Ticket",
"Request received! Your reference number is :number.": "Request received! Your reference number is :number.",
"Phone": "Phone",
"URL": "URL",
"PDF": "PDF",
"Submit Request": "Submit Request",
```
(`"Ticket Type"` and `"Name"`/`"Email"` already exist — don't duplicate. `"Request Your Ticket"` already exists too per the current file — check first and skip if present.)

In `lang/ar.json`, add the same keys with:
```json
"Request Your Ticket": "اطلب تذكرتك",
"Request received! Your reference number is :number.": "تم استلام طلبك! الرقم المرجعي هو :number.",
"Phone": "الهاتف",
"URL": "رابط",
"PDF": "ملف PDF",
"Submit Request": "إرسال الطلب",
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/TicketRequestFormTest.php`
Expected: PASS (4 tests)

- [ ] **Step 8: Run the build**

Run: `npm run build`
Expected: succeeds with no errors.

- [ ] **Step 9: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 10: Commit**

```bash
git add app/Http/Controllers/TicketRequestController.php resources/views/ticket-requests/create.blade.php \
  resources/js/app.js lang/en.json lang/ar.json tests/Feature/TicketRequestFormTest.php
git commit -m "feat: render dynamic ticket request form with phone widget"
```

---

### Task 6: Public — request submission (POST)

**Files:**
- Create: `app/Http/Requests/TicketRequestStoreRequest.php`
- Modify: `app/Http/Controllers/TicketRequestController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/TicketRequestSubmissionTest.php`

**Interfaces:**
- Consumes: `field_{fieldId}` / `field_{fieldId}_mode` / `field_{fieldId}_url` / `field_{fieldId}_file` input naming from Task 5; `Ticket`, `TicketRequestAnswer`, `TicketRequestField`, `TicketRequestFieldType`, `TicketStatus` from Task 2; `Propaganistas\LaravelPhone\Rules\Phone` from Task 1.
- Produces: route `ticket-requests.store` (POST `/events/{event}/request`); on success, session key `ticket_request_success` = the created ticket's `ticket_number`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketRequestField;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_submission_creates_a_pending_ticket(): void
    {
        Storage::fake('local');
        $event = Event::factory()->create(['status' => EventStatus::Published, 'slug' => 'ccs-2026']);
        $ticketType = TicketType::factory()->for($event)->create();

        $response = $this->post(route('ticket-requests.store', $event), [
            'ticket_type_id' => $ticketType->id,
            'name' => 'Kareem Al-Sayed',
            'email' => 'kareem@example.com',
            'phone' => '+201001234567',
        ]);

        $response->assertRedirect(route('ticket-requests.create', $event));
        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id, 'name' => 'Kareem Al-Sayed', 'email' => 'kareem@example.com',
            'status' => TicketStatus::Pending->value,
        ]);
        $ticket = Ticket::where('email', 'kareem@example.com')->firstOrFail();
        $this->assertSame('CCS2026-'.str_pad((string) $ticket->id, 6, '0', STR_PAD_LEFT), $ticket->ticket_number);
    }

    public function test_forward_looking_columns_stay_unset_after_submission(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $ticketType = TicketType::factory()->for($event)->create();

        $this->post(route('ticket-requests.store', $event), [
            'ticket_type_id' => $ticketType->id, 'name' => 'Nour Ibrahim', 'email' => 'nour@example.com', 'phone' => '+201009876543',
        ]);

        $ticket = Ticket::where('email', 'nour@example.com')->firstOrFail();
        $this->assertNull($ticket->ticket_id);
        $this->assertNull($ticket->workshop_booking_key);
        $this->assertFalse($ticket->is_paid);
        $this->assertNull($ticket->payment_method);
        $this->assertNull($ticket->checked_in_at);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $ticketType = TicketType::factory()->for($event)->create();

        $response = $this->post(route('ticket-requests.store', $event), [
            'ticket_type_id' => $ticketType->id, 'name' => 'Test', 'email' => 'not-an-email', 'phone' => '+201001234567',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $ticketType = TicketType::factory()->for($event)->create();

        $response = $this->post(route('ticket-requests.store', $event), [
            'ticket_type_id' => $ticketType->id, 'name' => 'Test', 'email' => 'test@example.com', 'phone' => 'not-a-phone',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_name_with_html_tags_is_rejected(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $ticketType = TicketType::factory()->for($event)->create();

        $response = $this->post(route('ticket-requests.store', $event), [
            'ticket_type_id' => $ticketType->id, 'name' => '<script>alert(1)</script>', 'email' => 'test@example.com', 'phone' => '+201001234567',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_required_dynamic_field_is_enforced(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $ticketType = TicketType::factory()->for($event)->create();
        $field = TicketRequestField::factory()->for($event)->create(['type' => 'instagram', 'is_required' => true]);

        $response = $this->post(route('ticket-requests.store', $event), [
            'ticket_type_id' => $ticketType->id, 'name' => 'Test', 'email' => 'test@example.com', 'phone' => '+201001234567',
        ]);

        $response->assertSessionHasErrors('field_'.$field->id);
    }

    public function test_instagram_answer_is_stored(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $ticketType = TicketType::factory()->for($event)->create();
        $field = TicketRequestField::factory()->for($event)->create(['type' => 'instagram']);

        $this->post(route('ticket-requests.store', $event), [
            'ticket_type_id' => $ticketType->id, 'name' => 'Test', 'email' => 'test@example.com', 'phone' => '+201001234567',
            'field_'.$field->id => '@myhandle',
        ]);

        $ticket = Ticket::where('email', 'test@example.com')->firstOrFail();
        $this->assertDatabaseHas('ticket_request_answers', [
            'ticket_id' => $ticket->id, 'ticket_request_field_id' => $field->id, 'value' => '@myhandle',
        ]);
    }

    public function test_cv_upload_is_stored_privately(): void
    {
        Storage::fake('local');
        $event = Event::factory()->create(['status' => EventStatus::Published]);
        $ticketType = TicketType::factory()->for($event)->create();
        $field = TicketRequestField::factory()->for($event)->create(['type' => 'cv']);

        $this->post(route('ticket-requests.store', $event), [
            'ticket_type_id' => $ticketType->id, 'name' => 'Test', 'email' => 'test@example.com', 'phone' => '+201001234567',
            'field_'.$field->id => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
        ]);

        $ticket = Ticket::where('email', 'test@example.com')->firstOrFail();
        $answer = $ticket->answers()->where('ticket_request_field_id', $field->id)->firstOrFail();
        Storage::disk('local')->assertExists($answer->file_path);
    }

    public function test_draft_event_returns_404_on_submit(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::Draft]);
        $ticketType = TicketType::factory()->for($event)->create();

        $response = $this->post(route('ticket-requests.store', $event), [
            'ticket_type_id' => $ticketType->id, 'name' => 'Test', 'email' => 'test@example.com', 'phone' => '+201001234567',
        ]);

        $response->assertStatus(404);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/TicketRequestSubmissionTest.php`
Expected: FAIL — route `ticket-requests.store` not defined.

- [ ] **Step 3: Create the FormRequest**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TicketRequestFieldType;
use Illuminate\Foundation\Http\FormRequest;
use Propaganistas\LaravelPhone\Rules\Phone;

class TicketRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\pM\s\'\-\.]+$/u'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', (new Phone)->international()],
        ];

        /** @var \App\Models\Event $event */
        $event = $this->route('event');

        foreach ($event->ticketRequestFields as $field) {
            $inputKey = 'field_'.$field->id;
            $requiredRule = $field->is_required ? 'required' : 'nullable';

            $rules += match ($field->type) {
                TicketRequestFieldType::Instagram => [
                    $inputKey => [$requiredRule, 'regex:/^@?[A-Za-z0-9_.]{1,30}$/'],
                ],
                TicketRequestFieldType::Portfolio => [
                    $inputKey.'_mode' => [$requiredRule, 'in:url,pdf'],
                    $inputKey.'_url' => ['nullable', 'required_if:'.$inputKey.'_mode,url', 'url', 'max:2048'],
                    $inputKey.'_file' => ['nullable', 'required_if:'.$inputKey.'_mode,pdf', 'file', 'mimes:pdf', 'max:5120'],
                ],
                TicketRequestFieldType::Cv => [
                    $inputKey => [$requiredRule, 'file', 'mimes:pdf,doc,docx', 'max:5120'],
                ],
            };
        }

        return $rules;
    }
}
```

- [ ] **Step 4: Add the store method to the controller**

Add `use` imports and the `store` method to `app/Http/Controllers/TicketRequestController.php` (final file):

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TicketRequestFieldType;
use App\Enums\TicketStatus;
use App\Http\Requests\TicketRequestStoreRequest;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketRequestField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketRequestController extends Controller
{
    public function create(Event $event, Request $request): View
    {
        return view('ticket-requests.create', [
            'event' => $event,
            'ticketTypes' => $event->ticketTypes()->where('is_active', true)->get(),
            'selectedTicketTypeId' => (int) $request->query('type'),
            'requestFields' => $event->ticketRequestFields,
        ]);
    }

    public function store(TicketRequestStoreRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();
        $fields = $event->ticketRequestFields;

        $ticket = DB::transaction(function () use ($validated, $event, $fields, $request) {
            $ticket = $event->tickets()->create([
                'ticket_type_id' => $validated['ticket_type_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => TicketStatus::Pending,
            ]);

            $ticket->update(['ticket_number' => $this->generateTicketNumber($event, $ticket)]);

            foreach ($fields as $field) {
                $this->storeAnswerFor($ticket, $field, $request);
            }

            return $ticket;
        });

        return redirect()->route('ticket-requests.create', $event)->with('ticket_request_success', $ticket->ticket_number);
    }

    private function storeAnswerFor(Ticket $ticket, TicketRequestField $field, Request $request): void
    {
        $inputKey = 'field_'.$field->id;

        if ($field->type === TicketRequestFieldType::Portfolio) {
            $mode = $request->input($inputKey.'_mode');

            if ($mode === 'pdf' && $request->hasFile($inputKey.'_file')) {
                $path = $request->file($inputKey.'_file')->store('ticket-uploads/'.$ticket->id, 'local');
                $ticket->answers()->create(['ticket_request_field_id' => $field->id, 'file_path' => $path]);
            } elseif ($mode === 'url' && $request->filled($inputKey.'_url')) {
                $ticket->answers()->create(['ticket_request_field_id' => $field->id, 'value' => $request->input($inputKey.'_url')]);
            }

            return;
        }

        if ($field->type === TicketRequestFieldType::Cv) {
            if ($request->hasFile($inputKey)) {
                $path = $request->file($inputKey)->store('ticket-uploads/'.$ticket->id, 'local');
                $ticket->answers()->create(['ticket_request_field_id' => $field->id, 'file_path' => $path]);
            }

            return;
        }

        if ($request->filled($inputKey)) {
            $ticket->answers()->create(['ticket_request_field_id' => $field->id, 'value' => $request->input($inputKey)]);
        }
    }

    private function generateTicketNumber(Event $event, Ticket $ticket): string
    {
        $prefix = strtoupper(str_replace('-', '', $event->slug));

        return $prefix.'-'.str_pad((string) $ticket->id, 6, '0', STR_PAD_LEFT);
    }
}
```

- [ ] **Step 5: Add the route**

In `routes/web.php`, inside the existing `Route::prefix('events/{event}')->middleware(EnsureEventIsPublished::class)->group(...)` block, add right after the `ticket-requests.create` line:

```php
    Route::post('/request', [TicketRequestController::class, 'store'])->name('ticket-requests.store');
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/TicketRequestSubmissionTest.php`
Expected: PASS (9 tests)

- [ ] **Step 7: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 8: Commit**

```bash
git add app/Http/Requests/TicketRequestStoreRequest.php app/Http/Controllers/TicketRequestController.php \
  routes/web.php tests/Feature/TicketRequestSubmissionTest.php
git commit -m "feat: handle ticket request submission with dynamic field validation"
```

---

### Task 7: Admin — ticket request queue, approve/reject, rejection email, file download

**Files:**
- Create: `app/Mail/TicketRequestRejected.php`
- Create: `resources/views/emails/ticket-requests/rejected.blade.php`
- Create: `app/Http/Controllers/Admin/TicketRequestQueueController.php`
- Create: `resources/views/admin/ticket-requests/index.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/admin/partials/sidebar.blade.php`
- Test: `tests/Feature/Admin/TicketRequestQueueTest.php`

**Interfaces:**
- Consumes: `Ticket`, `TicketRequestAnswer`, `TicketStatus` from Task 2; tickets created via Task 6's submission flow.
- Produces: routes `admin.events.ticket-requests.index` (GET), `.approve` / `.reject` (PATCH, param `Ticket $ticket`), `.answers.download` (GET, params `Ticket $ticket, TicketRequestAnswer $answer`).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\TicketStatus;
use App\Mail\TicketRequestRejected;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketRequestAnswer;
use App\Models\TicketRequestField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketRequestQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_pending_tickets_by_default(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        Ticket::factory()->for($event)->create(['status' => TicketStatus::Pending, 'name' => 'Pending Person']);
        Ticket::factory()->for($event)->create(['status' => TicketStatus::Rejected, 'name' => 'Rejected Person']);

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-requests.index', $event));

        $response->assertOk();
        $response->assertSee('Pending Person');
        $response->assertDontSee('Rejected Person');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        Ticket::factory()->for($event)->create(['status' => TicketStatus::Rejected, 'name' => 'Rejected Person']);

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-requests.index', $event).'?status=rejected');

        $response->assertOk();
        $response->assertSee('Rejected Person');
    }

    public function test_approve_moves_ticket_to_payment_pending_and_sends_no_mail(): void
    {
        Mail::fake();
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create(['status' => TicketStatus::Pending]);

        $response = $this->actingAs($admin)->patch(route('admin.events.ticket-requests.approve', [$event, $ticket]));

        $response->assertRedirect(route('admin.events.ticket-requests.index', $event));
        $this->assertSame(TicketStatus::PaymentPending, $ticket->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_reject_moves_ticket_to_rejected_and_sends_mail(): void
    {
        Mail::fake();
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create(['status' => TicketStatus::Pending, 'email' => 'attendee@example.com']);

        $response = $this->actingAs($admin)->patch(route('admin.events.ticket-requests.reject', [$event, $ticket]));

        $response->assertRedirect(route('admin.events.ticket-requests.index', $event));
        $this->assertSame(TicketStatus::Rejected, $ticket->fresh()->status);
        Mail::assertSent(TicketRequestRejected::class, fn ($mail) => $mail->hasTo('attendee@example.com'));
    }

    public function test_guest_cannot_download_an_answer_file(): void
    {
        Storage::fake('local');
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create();
        $field = TicketRequestField::factory()->for($event)->create(['type' => 'cv']);
        $path = 'ticket-uploads/'.$ticket->id.'/resume.pdf';
        Storage::disk('local')->put($path, 'fake-pdf-content');
        $answer = TicketRequestAnswer::factory()->create(['ticket_id' => $ticket->id, 'ticket_request_field_id' => $field->id, 'file_path' => $path, 'value' => null]);

        $response = $this->get(route('admin.events.ticket-requests.answers.download', [$event, $ticket, $answer]));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_download_an_answer_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create();
        $field = TicketRequestField::factory()->for($event)->create(['type' => 'cv']);
        $path = 'ticket-uploads/'.$ticket->id.'/resume.pdf';
        Storage::disk('local')->put($path, 'fake-pdf-content');
        $answer = TicketRequestAnswer::factory()->create(['ticket_id' => $ticket->id, 'ticket_request_field_id' => $field->id, 'file_path' => $path, 'value' => null]);

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-requests.answers.download', [$event, $ticket, $answer]));

        $response->assertOk();
    }

    public function test_downloading_an_answer_from_a_different_event_404s(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $otherEvent = Event::factory()->create();
        $ticket = Ticket::factory()->for($otherEvent)->create();
        $field = TicketRequestField::factory()->for($otherEvent)->create(['type' => 'cv']);
        $path = 'ticket-uploads/'.$ticket->id.'/resume.pdf';
        Storage::disk('local')->put($path, 'fake-pdf-content');
        $answer = TicketRequestAnswer::factory()->create(['ticket_id' => $ticket->id, 'ticket_request_field_id' => $field->id, 'file_path' => $path, 'value' => null]);

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-requests.answers.download', [$event, $ticket, $answer]));

        $response->assertStatus(404);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Admin/TicketRequestQueueTest.php`
Expected: FAIL — route `admin.events.ticket-requests.index` not defined.

- [ ] **Step 3: Create the Mailable**

```php
<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketRequestRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your ticket request was not approved'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-requests.rejected',
        );
    }
}
```

```blade
{{-- resources/views/emails/ticket-requests/rejected.blade.php --}}
<p>{{ __('Hi') }} {{ $ticket->name }},</p>
<p>
    {{ __('Thank you for your interest in') }}
    {{ app()->getLocale() === 'ar' ? $ticket->event->name_ar : $ticket->event->name_en }}.
    {{ __("Unfortunately, we're unable to approve your ticket request at this time.") }}
</p>
<p>{{ __('Reference') }}: {{ $ticket->ticket_number }}</p>
```

- [ ] **Step 4: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Mail\TicketRequestRejected;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketRequestAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketRequestQueueController extends Controller
{
    public function index(Event $event, Request $request): View
    {
        $status = $request->query('status', TicketStatus::Pending->value);

        $tickets = $event->tickets()
            ->with(['ticketType', 'answers.field'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('admin.ticket-requests.index', ['event' => $event, 'tickets' => $tickets, 'status' => $status]);
    }

    public function approve(Event $event, Ticket $ticket): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $ticket);
        $ticket->update(['status' => TicketStatus::PaymentPending]);

        return redirect()->route('admin.events.ticket-requests.index', $event);
    }

    public function reject(Event $event, Ticket $ticket): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $ticket);
        $ticket->update(['status' => TicketStatus::Rejected]);

        Mail::to($ticket->email)->send(new TicketRequestRejected($ticket));

        return redirect()->route('admin.events.ticket-requests.index', $event);
    }

    public function downloadAnswer(Event $event, Ticket $ticket, TicketRequestAnswer $answer): StreamedResponse
    {
        $this->assertBelongsToEvent($event, $ticket);

        if ($answer->ticket_id !== $ticket->id || ! $answer->file_path) {
            throw new NotFoundHttpException;
        }

        return Storage::disk('local')->download($answer->file_path);
    }

    private function assertBelongsToEvent(Event $event, Ticket $ticket): void
    {
        if ($ticket->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
```

- [ ] **Step 5: Add the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\TicketRequestQueueController;
```

And inside the admin `Route::middleware('auth')->group(...)` block, add (after the `request-form-fields` resource from Task 4):

```php
        Route::get('events/{event}/ticket-requests', [TicketRequestQueueController::class, 'index'])->name('events.ticket-requests.index');
        Route::patch('events/{event}/ticket-requests/{ticket}/approve', [TicketRequestQueueController::class, 'approve'])->name('events.ticket-requests.approve');
        Route::patch('events/{event}/ticket-requests/{ticket}/reject', [TicketRequestQueueController::class, 'reject'])->name('events.ticket-requests.reject');
        Route::get('events/{event}/ticket-requests/{ticket}/answers/{answer}/download', [TicketRequestQueueController::class, 'downloadAnswer'])->name('events.ticket-requests.answers.download');
```

- [ ] **Step 6: Create the view**

```blade
{{-- resources/views/admin/ticket-requests/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Ticket Requests').' — '.$event->name_en" />

    <div class="mb-4 flex gap-2 text-sm">
        @foreach(['pending', 'approved', 'rejected', 'payment_pending', 'all'] as $option)
            <a href="{{ route('admin.events.ticket-requests.index', $event) }}?status={{ $option }}"
               class="px-3 py-1.5 rounded {{ $status === $option ? 'bg-ccs-red text-white' : 'border border-gray-600 text-gray-300' }}">
                {{ ucfirst(str_replace('_', ' ', $option)) }}
            </a>
        @endforeach
    </div>

    @if($tickets->isEmpty())
        <x-admin.empty-state :message="__('No ticket requests yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Name') }}</th>
                    <th class="py-2 px-3">{{ __('Email') }}</th>
                    <th class="py-2 px-3">{{ __('Ticket Type') }}</th>
                    <th class="py-2 px-3">{{ __('Status') }}</th>
                    <th class="py-2 px-3">{{ __('Answers') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    <tr class="border-b border-gray-800 align-top">
                        <td class="py-2 px-3">{{ $ticket->name }}</td>
                        <td class="py-2 px-3">{{ $ticket->email }}</td>
                        <td class="py-2 px-3">{{ $ticket->ticketType->name_en }}</td>
                        <td class="py-2 px-3">{{ ucfirst(str_replace('_', ' ', $ticket->status->value)) }}</td>
                        <td class="py-2 px-3">
                            @foreach($ticket->answers as $answer)
                                <div class="text-xs text-gray-400">
                                    {{ $answer->field->label_en }}:
                                    @if($answer->file_path)
                                        <a href="{{ route('admin.events.ticket-requests.answers.download', [$event, $ticket, $answer]) }}" class="text-ccs-teal-light hover:underline">{{ __('Download') }}</a>
                                    @else
                                        {{ $answer->value }}
                                    @endif
                                </div>
                            @endforeach
                        </td>
                        <td class="py-2 px-3 text-right">
                            @if($ticket->status === \App\Enums\TicketStatus::Pending)
                                <form method="POST" action="{{ route('admin.events.ticket-requests.approve', [$event, $ticket]) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <x-admin.button type="submit">{{ __('Approve') }}</x-admin.button>
                                </form>
                                <form method="POST" action="{{ route('admin.events.ticket-requests.reject', [$event, $ticket]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
                                    @csrf @method('PATCH')
                                    <x-admin.button type="submit" variant="danger" class="ml-2">{{ __('Reject') }}</x-admin.button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @endif
@endsection
```

- [ ] **Step 7: Add the sidebar link**

In `resources/views/admin/partials/sidebar.blade.php`, add to `$eventNavItems` (after the `request-form-fields` entry added in Task 4):

```php
                        ['prefix' => 'admin.events.ticket-requests', 'route' => 'admin.events.ticket-requests.index', 'label' => __('Ticket Requests')],
```

- [ ] **Step 8: Add translation keys**

In `lang/en.json`, add (skip any already present — `"Name"`, `"Email"`, `"Ticket Type"`, `"Status"` already exist):
```json
"Ticket Requests": "Ticket Requests",
"No ticket requests yet.": "No ticket requests yet.",
"Answers": "Answers",
"Download": "Download",
"Approve": "Approve",
"Reject": "Reject",
"Your ticket request was not approved": "Your ticket request was not approved",
"Hi": "Hi",
"Thank you for your interest in": "Thank you for your interest in",
"Unfortunately, we're unable to approve your ticket request at this time.": "Unfortunately, we're unable to approve your ticket request at this time.",
"Reference": "Reference",
```

In `lang/ar.json`, add the same keys with:
```json
"Ticket Requests": "طلبات التذاكر",
"No ticket requests yet.": "لا توجد طلبات تذاكر بعد.",
"Answers": "الإجابات",
"Download": "تنزيل",
"Approve": "قبول",
"Reject": "رفض",
"Your ticket request was not approved": "لم تتم الموافقة على طلب تذكرتك",
"Hi": "مرحبًا",
"Thank you for your interest in": "شكرًا لاهتمامك بـ",
"Unfortunately, we're unable to approve your ticket request at this time.": "للأسف، لا يمكننا الموافقة على طلب تذكرتك في الوقت الحالي.",
"Reference": "الرقم المرجعي",
```

- [ ] **Step 9: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/Admin/TicketRequestQueueTest.php`
Expected: PASS (7 tests)

- [ ] **Step 10: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 11: Commit**

```bash
git add app/Mail/TicketRequestRejected.php resources/views/emails resources/views/admin/ticket-requests \
  app/Http/Controllers/Admin/TicketRequestQueueController.php routes/web.php resources/views/admin/partials/sidebar.blade.php \
  lang/en.json lang/ar.json tests/Feature/Admin/TicketRequestQueueTest.php
git commit -m "feat: add admin ticket request queue with approve/reject and rejection email"
```

---

### Final steps (after all 7 tasks)

- [ ] Run the full suite: `php artisan test --compact` — expect all tests passing (previous 165 + this plan's new tests).
- [ ] Run `vendor/bin/pint --format agent` (whole repo, not just `--dirty`) to catch anything missed.
- [ ] Run `npm run build` once more to confirm the final bundle is clean.
- [ ] Update `.claude/plans/00-status.md` and `.claude/plans/05-ticket-request.md`: mark "Ticket model + migration", "Form submission / store endpoint", and "Admin review/approve/reject screen" as done; "Approval email with payment link" stays unchecked (Payment phase). Also update `.claude/plans/03-admin-panel.md`'s "Ticket Request review/approve/reject queue" line to done.
- [ ] `php artisan migrate --force` against the real dev database (three new tables), then smoke-check with a manual request submission.
