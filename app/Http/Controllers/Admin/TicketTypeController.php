<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketTypeController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.ticket-types.index', ['event' => $event, 'ticketTypes' => $event->ticketTypes]);
    }

    public function create(Event $event): View
    {
        return view('admin.ticket-types.form', ['event' => $event, 'ticketType' => new TicketType]);
    }

    public function store(TicketTypeRequest $request, Event $event): RedirectResponse
    {
        $event->ticketTypes()->create($request->validated());

        return redirect()->route('admin.events.ticket-types.index', $event);
    }

    public function edit(Event $event, TicketType $ticketType): View
    {
        $this->assertBelongsToEvent($event, $ticketType);

        return view('admin.ticket-types.form', ['event' => $event, 'ticketType' => $ticketType]);
    }

    public function update(TicketTypeRequest $request, Event $event, TicketType $ticketType): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $ticketType);
        $ticketType->update($request->validated());

        return redirect()->route('admin.events.ticket-types.index', $event);
    }

    public function destroy(Event $event, TicketType $ticketType): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $ticketType);
        $ticketType->delete();

        return redirect()->route('admin.events.ticket-types.index', $event);
    }

    private function assertBelongsToEvent(Event $event, TicketType $ticketType): void
    {
        if ($ticketType->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
