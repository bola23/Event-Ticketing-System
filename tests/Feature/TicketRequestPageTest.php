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

        $response = $this->get(route('ticket-requests.create', $event).'?lang=en');

        $response->assertStatus(200);
        $response->assertSee('VIP');
    }

    public function test_form_preselects_ticket_type_from_query_string(): void
    {
        $event = Event::factory()->create();
        $vip = TicketType::factory()->for($event)->create(['name_en' => 'VIP']);

        $response = $this->get(route('ticket-requests.create', $event).'?type='.$vip->id.'&lang=en');

        $response->assertStatus(200);
        $response->assertSee('selected', false);
    }
}
