<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscriberRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriberController extends Controller
{
    public function store(NewsletterSubscriberRequest $request, Event $event): RedirectResponse
    {
        $event->newsletterSubscribers()->firstOrCreate(['email' => $request->validated('email')]);

        return redirect(route('landing.show', $event).'#newsletter')->with('newsletter_success', true);
    }
}
