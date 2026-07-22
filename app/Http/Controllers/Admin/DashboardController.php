<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalEvents' => Event::count(),
            'publishedEvents' => Event::where('status', EventStatus::Published)->count(),
            'draftEvents' => Event::where('status', EventStatus::Draft)->count(),
        ]);
    }
}
