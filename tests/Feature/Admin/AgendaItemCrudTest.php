<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\Speaker;
use App\Models\User;
use App\Models\Workshop;
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

    public function test_creating_an_agenda_item_rejects_a_speaker_from_a_different_event(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $otherEventSpeaker = Speaker::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.agenda-items.store', $event), [
            'speaker_id' => $otherEventSpeaker->id,
            'day_date' => '2026-08-15', 'start_time' => '09:00', 'end_time' => '10:00',
            'title_ar' => 'الافتتاح', 'title_en' => 'Opening', 'type' => 'keynote', 'sort_order' => 0,
        ]);

        $response->assertSessionHasErrors('speaker_id');
    }

    public function test_creating_an_agenda_item_rejects_a_workshop_from_a_different_event(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $otherEventWorkshop = Workshop::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.agenda-items.store', $event), [
            'workshop_id' => $otherEventWorkshop->id,
            'day_date' => '2026-08-15', 'start_time' => '09:00', 'end_time' => '10:00',
            'title_ar' => 'الافتتاح', 'title_en' => 'Opening', 'type' => 'keynote', 'sort_order' => 0,
        ]);

        $response->assertSessionHasErrors('workshop_id');
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
