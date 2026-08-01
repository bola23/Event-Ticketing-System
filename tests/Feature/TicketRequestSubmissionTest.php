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
