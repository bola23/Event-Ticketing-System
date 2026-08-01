<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Mail\TicketRequestRejected;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketRequestAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketRequestQueueController extends Controller
{
    public function index(Event $event, Request $request): View
    {
        $status = $request->query('status', TicketStatus::Pending->value);

        $tickets = $event->tickets()
            ->with(['ticketType', 'answers.field'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('admin.ticket-requests.index', ['event' => $event, 'tickets' => $tickets, 'status' => $status]);
    }

    public function approve(Event $event, Ticket $ticket): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $ticket);
        $ticket->update(['status' => TicketStatus::PaymentPending]);

        return redirect()->route('admin.events.ticket-requests.index', $event);
    }

    public function reject(Event $event, Ticket $ticket): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $ticket);
        $ticket->update(['status' => TicketStatus::Rejected]);

        Mail::to($ticket->email)->send(new TicketRequestRejected($ticket));

        return redirect()->route('admin.events.ticket-requests.index', $event);
    }

    public function downloadAnswer(Event $event, Ticket $ticket, TicketRequestAnswer $answer): StreamedResponse
    {
        $this->assertBelongsToEvent($event, $ticket);

        if ($answer->ticket_id !== $ticket->id || ! $answer->file_path) {
            throw new NotFoundHttpException;
        }

        return Storage::disk('local')->download($answer->file_path);
    }

    private function assertBelongsToEvent(Event $event, Ticket $ticket): void
    {
        if ($ticket->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
