<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Creators Hub events are administered independently from other event brands on this
     * platform (e.g. CCS) — there is no shared "belongs to Creators Hub" flag on Event yet, so
     * this intentionally does not query the Event model. Once Creators Hub has its own events,
     * wire this to a real query; until then the view renders an honest "coming soon" state.
     */
    public function show(): View
    {
        return view('home.show');
    }
}
