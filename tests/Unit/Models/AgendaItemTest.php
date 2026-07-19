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
