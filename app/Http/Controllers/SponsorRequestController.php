<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMediaUploads;
use App\Http\Requests\SponsorRequestStoreRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SponsorRequestController extends Controller
{
    use HandlesMediaUploads;

    public function create(Event $event): View
    {
        return view('sponsor-requests.create', ['event' => $event]);
    }

    public function store(SponsorRequestStoreRequest $request, Event $event): RedirectResponse
    {
        $data = $request->safe()->except(['logo']);
        $data = $this->withUploadedMedia($data, $request, 'logo', 'logo_path', 'sponsor-requests');

        $event->sponsorRequests()->create($data);

        return redirect()->route('sponsor-requests.create', $event)->with('sponsor_request_success', true);
    }
}
