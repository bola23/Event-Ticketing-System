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
