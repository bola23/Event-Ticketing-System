<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\View\View;

class EventsController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->where('status', EventStatus::Published)
            ->orderBy('start_date')
            ->get();

        return view('events.index', ['events' => $events]);
    }
}
