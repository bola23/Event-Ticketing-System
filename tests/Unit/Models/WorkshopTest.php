<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\AgendaItemType;
use App\Models\AgendaItem;
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

    public function test_workshop_has_many_agenda_items_ordered_by_time(): void
    {
        $event = Event::factory()->create();
        $workshop = Workshop::factory()->for($event)->create();
        AgendaItem::factory()->for($event)->create([
            'workshop_id' => $workshop->id, 'type' => AgendaItemType::WorkshopSession,
            'day_date' => '2026-08-16', 'start_time' => '09:00', 'title_en' => 'Second Session',
        ]);
        AgendaItem::factory()->for($event)->create([
            'workshop_id' => $workshop->id, 'type' => AgendaItemType::WorkshopSession,
            'day_date' => '2026-08-15', 'start_time' => '09:00', 'title_en' => 'First Session',
        ]);

        $this->assertSame(['First Session', 'Second Session'], $workshop->agendaItems->pluck('title_en')->all());
    }
}
