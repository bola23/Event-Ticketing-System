<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Event;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FaqController extends Controller
{
    public function index(Event $event): View
    {
        return view('admin.faqs.index', ['event' => $event, 'faqs' => $event->faqs]);
    }

    public function create(Event $event): View
    {
        return view('admin.faqs.form', ['event' => $event, 'faq' => new Faq]);
    }

    public function store(FaqRequest $request, Event $event): RedirectResponse
    {
        $event->faqs()->create($request->validated());

        return redirect()->route('admin.events.faqs.index', $event);
    }

    public function edit(Event $event, Faq $faq): View
    {
        $this->assertBelongsToEvent($event, $faq);

        return view('admin.faqs.form', ['event' => $event, 'faq' => $faq]);
    }

    public function update(FaqRequest $request, Event $event, Faq $faq): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $faq);
        $faq->update($request->validated());

        return redirect()->route('admin.events.faqs.index', $event);
    }

    public function destroy(Event $event, Faq $faq): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $faq);
        $faq->delete();

        return redirect()->route('admin.events.faqs.index', $event);
    }

    private function assertBelongsToEvent(Event $event, Faq $faq): void
    {
        if ($faq->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
