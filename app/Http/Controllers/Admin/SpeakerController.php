<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpeakerRequest;
use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SpeakerController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.speakers.index', ['event' => $event, 'speakers' => $event->speakers]);
    }

    public function create(Event $event): View
    {
        return view('admin.speakers.form', ['event' => $event, 'speaker' => new Speaker]);
    }

    public function store(SpeakerRequest $request, Event $event): RedirectResponse
    {
        $event->speakers()->create($request->validated());

        return redirect()->route('admin.events.speakers.index', $event);
    }

    public function edit(Event $event, Speaker $speaker): View
    {
        $this->assertBelongsToEvent($event, $speaker);

        return view('admin.speakers.form', ['event' => $event, 'speaker' => $speaker]);
    }

    public function update(SpeakerRequest $request, Event $event, Speaker $speaker): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $speaker);
        $speaker->update($request->validated());

        return redirect()->route('admin.events.speakers.index', $event);
    }

    public function destroy(Event $event, Speaker $speaker): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $speaker);
        $speaker->delete();

        return redirect()->route('admin.events.speakers.index', $event);
    }

    private function assertBelongsToEvent(Event $event, Speaker $speaker): void
    {
        if ($speaker->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
