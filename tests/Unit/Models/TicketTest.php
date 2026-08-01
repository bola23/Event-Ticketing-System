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
