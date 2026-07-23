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

    public function test_admin_can_view_the_index_page_with_records(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        TicketType::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-types.index', $event));

        $response->assertOk();
    }

    public function test_admin_can_view_the_edit_page(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create();

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-types.edit', [$event, $ticketType]));

        $response->assertOk();
    }
}
