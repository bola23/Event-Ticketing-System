<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function show(): View
    {
        $events = Event::query()
            ->where('status', EventStatus::Published)
            ->orderBy('start_date')
            ->get();

        return view('home.show', [
            'featuredEvent' => $events->first(),
            'otherEvents' => $events->slice(1),
        ]);
    }
}
