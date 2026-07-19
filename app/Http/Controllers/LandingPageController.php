<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function show(Event $event): View
    {
        $event->load([
            'speakers', 'sponsors', 'ticketTypes', 'workshops',
            'agendaItems', 'faqs', 'landingPageContent',
        ]);

        return view('landing.show', ['event' => $event]);
    }
}
