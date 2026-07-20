<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketRequestController extends Controller
{
    public function create(Event $event, Request $request): View
    {
        return view('ticket-requests.create', [
            'event' => $event,
            'ticketTypes' => $event->ticketTypes()->where('is_active', true)->get(),
            'selectedTicketTypeId' => (int) $request->query('type'),
        ]);
    }
}
