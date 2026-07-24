<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class ContactMessageController extends Controller
{
    public function store(ContactMessageRequest $request, Event $event): RedirectResponse
    {
        $event->contactMessages()->create($request->validated());

        return redirect(route('landing.show', $event).'#contact')->with('contact_success', true);
    }
}
