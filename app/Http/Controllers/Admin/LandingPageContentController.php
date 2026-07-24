<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LandingPageSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingPageContentRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingPageContentController extends Controller
{
    /** @var array<string, array{section: LandingPageSection, field_key: string}> */
    private const FIELDS = [
        'hero_headline' => ['section' => LandingPageSection::Hero, 'field_key' => 'headline'],
        'about_body' => ['section' => LandingPageSection::About, 'field_key' => 'body'],
        'location_intro' => ['section' => LandingPageSection::Location, 'field_key' => 'intro'],
        'awards_teaser_blurb' => ['section' => LandingPageSection::AwardsTeaser, 'field_key' => 'blurb'],
        'stats_attendees_count' => ['section' => LandingPageSection::Stats, 'field_key' => 'attendees_count'],
        'stats_countries_count' => ['section' => LandingPageSection::Stats, 'field_key' => 'countries_count'],
    ];

    public function edit(Event $event): View
    {
        $values = [];
        foreach (self::FIELDS as $prefix => $target) {
            $content = $event->contentFor($target['section'], $target['field_key']);
            $values[$prefix.'_ar'] = $content?->value_ar;
            $values[$prefix.'_en'] = $content?->value_en;
        }

        return view('admin.landing-page-content.edit', ['event' => $event, 'values' => $values]);
    }

    public function update(LandingPageContentRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();

        foreach (self::FIELDS as $prefix => $target) {
            $event->landingPageContent()->updateOrCreate(
                ['section' => $target['section'], 'field_key' => $target['field_key']],
                ['value_ar' => $data[$prefix.'_ar'] ?? null, 'value_en' => $data[$prefix.'_en'] ?? null],
            );
        }

        return redirect()->route('admin.events.content.edit', $event);
    }
}
