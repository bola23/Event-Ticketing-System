<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SpeakerRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SpeakerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SpeakerRequestController extends Controller
{
    public function index(Event $event, Request $request): View
    {
        $status = $request->query('status', SpeakerRequestStatus::Pending->value);

        $speakerRequests = $event->speakerRequests()
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->get();

        return view('admin.speaker-requests.index', ['event' => $event, 'speakerRequests' => $speakerRequests, 'status' => $status]);
    }

    public function updateStatus(Event $event, SpeakerRequest $speakerRequest, string $status): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $speakerRequest);

        $status = Validator::make(
            ['status' => $status],
            ['status' => ['required', 'in:approved,rejected']],
        )->validate()['status'];

        if ($status === 'approved') {
            DB::transaction(function () use ($event, $speakerRequest): void {
                $event->speakers()->create([
                    'name_ar' => $speakerRequest->name_ar,
                    'name_en' => $speakerRequest->name_en,
                    'title_ar' => $speakerRequest->title_ar,
                    'title_en' => $speakerRequest->title_en,
                    'bio_ar' => $speakerRequest->bio_ar,
                    'bio_en' => $speakerRequest->bio_en,
                    'photo_path' => $speakerRequest->photo_path,
                    'sort_order' => $event->speakers()->max('sort_order') + 1,
                ]);

                $speakerRequest->update(['status' => SpeakerRequestStatus::Approved]);
            });

            return redirect()
                ->route('admin.events.speaker-requests.index', $event)
                ->with('success', __('Speaker request approved and added to speakers.'));
        }

        $speakerRequest->update(['status' => SpeakerRequestStatus::Rejected]);

        return redirect()
            ->route('admin.events.speaker-requests.index', $event)
            ->with('success', __('Speaker request rejected successfully.'));
    }

    private function assertBelongsToEvent(Event $event, SpeakerRequest $speakerRequest): void
    {
        if ($speakerRequest->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
