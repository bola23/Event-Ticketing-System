<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SponsorRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SponsorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SponsorRequestController extends Controller
{
    public function index(Event $event, Request $request): View
    {
        $status = $request->query('status', SponsorRequestStatus::Pending->value);

        $sponsorRequests = $event->sponsorRequests()
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->get();

        return view('admin.sponsor-requests.index', ['event' => $event, 'sponsorRequests' => $sponsorRequests, 'status' => $status]);
    }

    public function updateStatus(Event $event, SponsorRequest $sponsorRequest, string $status, Request $request): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $sponsorRequest);

        $validated = Validator::make(
            ['status' => $status, 'tier' => $request->input('tier')],
            [
                'status' => ['required', 'in:approved,rejected'],
                'tier' => [Rule::requiredIf($status === 'approved'), 'nullable', Rule::in(['platinum', 'gold', 'silver', 'bronze'])],
            ],
        )->validate();

        if ($validated['status'] === 'approved') {
            DB::transaction(function () use ($event, $sponsorRequest, $validated): void {
                $event->sponsors()->create([
                    'name_ar' => $sponsorRequest->name_ar,
                    'name_en' => $sponsorRequest->name_en,
                    'logo_path' => $sponsorRequest->logo_path,
                    'tier' => $validated['tier'],
                    'website_url' => $sponsorRequest->website_url,
                    'sort_order' => $event->sponsors()->max('sort_order') + 1,
                ]);

                $sponsorRequest->update(['status' => SponsorRequestStatus::Approved]);
            });

            return redirect()
                ->route('admin.events.sponsor-requests.index', $event)
                ->with('success', __('Sponsor request approved and added to sponsors.'));
        }

        $sponsorRequest->update(['status' => SponsorRequestStatus::Rejected]);

        return redirect()
            ->route('admin.events.sponsor-requests.index', $event)
            ->with('success', __('Sponsor request rejected successfully.'));
    }

    private function assertBelongsToEvent(Event $event, SponsorRequest $sponsorRequest): void
    {
        if ($sponsorRequest->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
