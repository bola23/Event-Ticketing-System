<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketRequestFieldRequest;
use App\Models\Event;
use App\Models\TicketRequestField;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketRequestFieldController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.request-form-fields.index', ['event' => $event, 'requestFields' => $event->ticketRequestFields]);
    }

    public function create(Event $event): View
    {
        return view('admin.request-form-fields.form', ['event' => $event, 'requestField' => new TicketRequestField]);
    }

    public function store(TicketRequestFieldRequest $request, Event $event): RedirectResponse
    {
        $event->ticketRequestFields()->create($request->validated());

        return redirect()->route('admin.events.request-form-fields.index', $event);
    }

    public function edit(Event $event, TicketRequestField $requestField): View
    {
        $this->assertBelongsToEvent($event, $requestField);

        return view('admin.request-form-fields.form', ['event' => $event, 'requestField' => $requestField]);
    }

    public function update(TicketRequestFieldRequest $request, Event $event, TicketRequestField $requestField): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $requestField);
        $requestField->update($request->validated());

        return redirect()->route('admin.events.request-form-fields.index', $event);
    }

    public function destroy(Event $event, TicketRequestField $requestField): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $requestField);
        $requestField->delete();

        return redirect()->route('admin.events.request-form-fields.index', $event);
    }

    private function assertBelongsToEvent(Event $event, TicketRequestField $requestField): void
    {
        if ($requestField->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
