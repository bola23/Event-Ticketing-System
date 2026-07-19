<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function show(Event $event): View
    {
        $itemsByDay = $event->agendaItems()->with(['speaker', 'workshop'])->get()->groupBy(
            fn ($item) => $item->day_date->toDateString()
        );

        return view('agenda.show', ['event' => $event, 'itemsByDay' => $itemsByDay]);
    }
}
