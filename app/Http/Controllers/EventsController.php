<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\View\View;

class EventsController extends Controller
{
    /**
     * See HomeController::show() — Creators Hub has no events of its own yet, so this
     * intentionally renders an empty state rather than querying the Event model. Once it does,
     * replace the empty collection with a real Event::query()->where('status', ...)->get().
     */
    public function index(): View
    {
        return view('events.index', ['events' => new Collection]);
    }
}
