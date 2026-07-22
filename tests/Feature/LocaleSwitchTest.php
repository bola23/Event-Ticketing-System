<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_locale_is_arabic_with_rtl_direction(): void
    {
        $event = Event::factory()->create([
            'slug' => 'ccs-2026',
            'status' => EventStatus::Published,
        ]);

        $response = $this->get(route('landing.show', $event));

        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_english_locale_sets_ltr_direction(): void
    {
        $event = Event::factory()->create([
            'slug' => 'ccs-2026',
            'status' => EventStatus::Published,
        ]);

        $response = $this->get(route('landing.show', $event).'?lang=en');

        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
    }
}
