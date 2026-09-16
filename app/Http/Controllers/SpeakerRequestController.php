<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMediaUploads;
use App\Http\Requests\SpeakerRequestStoreRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SpeakerRequestController extends Controller
{
    use HandlesMediaUploads;

    public function create(Event $event): View
    {
        return view('speaker-requests.create', ['event' => $event]);
    }

    public function store(SpeakerRequestStoreRequest $request, Event $event): RedirectResponse
    {
        $data = $request->safe()->except(['photo']);
        $data = $this->withUploadedMedia($data, $request, 'photo', 'photo_path', 'speaker-requests');

        $event->speakerRequests()->create($data);

        return redirect()->route('speaker-requests.create', $event)->with('speaker_request_success', true);
    }
}
