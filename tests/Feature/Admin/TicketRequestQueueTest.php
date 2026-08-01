<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\TicketStatus;
use App\Mail\TicketRequestRejected;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketRequestAnswer;
use App\Models\TicketRequestField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketRequestQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_pending_tickets_by_default(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        Ticket::factory()->for($event)->create(['status' => TicketStatus::Pending, 'name' => 'Pending Person']);
        Ticket::factory()->for($event)->create(['status' => TicketStatus::Rejected, 'name' => 'Rejected Person']);

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-requests.index', $event));

        $response->assertOk();
        $response->assertSee('Pending Person');
        $response->assertDontSee('Rejected Person');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        Ticket::factory()->for($event)->create(['status' => TicketStatus::Rejected, 'name' => 'Rejected Person']);

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-requests.index', $event).'?status=rejected');

        $response->assertOk();
        $response->assertSee('Rejected Person');
    }

    public function test_approve_moves_ticket_to_payment_pending_and_sends_no_mail(): void
    {
        Mail::fake();
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create(['status' => TicketStatus::Pending]);

        $response = $this->actingAs($admin)->patch(route('admin.events.ticket-requests.approve', [$event, $ticket]));

        $response->assertRedirect(route('admin.events.ticket-requests.index', $event));
        $this->assertSame(TicketStatus::PaymentPending, $ticket->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_reject_moves_ticket_to_rejected_and_sends_mail(): void
    {
        Mail::fake();
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create(['status' => TicketStatus::Pending, 'email' => 'attendee@example.com']);

        $response = $this->actingAs($admin)->patch(route('admin.events.ticket-requests.reject', [$event, $ticket]));

        $response->assertRedirect(route('admin.events.ticket-requests.index', $event));
        $this->assertSame(TicketStatus::Rejected, $ticket->fresh()->status);
        Mail::assertSent(TicketRequestRejected::class, fn ($mail) => $mail->hasTo('attendee@example.com'));
    }

    public function test_guest_cannot_download_an_answer_file(): void
    {
        Storage::fake('local');
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create();
        $field = TicketRequestField::factory()->for($event)->create(['type' => 'cv']);
        $path = 'ticket-uploads/'.$ticket->id.'/resume.pdf';
        Storage::disk('local')->put($path, 'fake-pdf-content');
        $answer = TicketRequestAnswer::factory()->create(['ticket_id' => $ticket->id, 'ticket_request_field_id' => $field->id, 'file_path' => $path, 'value' => null]);

        $response = $this->get(route('admin.events.ticket-requests.answers.download', [$event, $ticket, $answer]));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_download_an_answer_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create();
        $field = TicketRequestField::factory()->for($event)->create(['type' => 'cv']);
        $path = 'ticket-uploads/'.$ticket->id.'/resume.pdf';
        Storage::disk('local')->put($path, 'fake-pdf-content');
        $answer = TicketRequestAnswer::factory()->create(['ticket_id' => $ticket->id, 'ticket_request_field_id' => $field->id, 'file_path' => $path, 'value' => null]);

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-requests.answers.download', [$event, $ticket, $answer]));

        $response->assertOk();
    }

    public function test_downloading_an_answer_from_a_different_event_404s(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $event = Event::factory()->create();
        $otherEvent = Event::factory()->create();
        $ticket = Ticket::factory()->for($otherEvent)->create();
        $field = TicketRequestField::factory()->for($otherEvent)->create(['type' => 'cv']);
        $path = 'ticket-uploads/'.$ticket->id.'/resume.pdf';
        Storage::disk('local')->put($path, 'fake-pdf-content');
        $answer = TicketRequestAnswer::factory()->create(['ticket_id' => $ticket->id, 'ticket_request_field_id' => $field->id, 'file_path' => $path, 'value' => null]);

        $response = $this->actingAs($admin)->get(route('admin.events.ticket-requests.answers.download', [$event, $ticket, $answer]));

        $response->assertStatus(404);
    }
}
