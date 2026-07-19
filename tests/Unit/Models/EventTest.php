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
