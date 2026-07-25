<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandingPageContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hero_headline_ar' => ['nullable', 'string'],
            'hero_headline_en' => ['nullable', 'string'],
            'about_body_ar' => ['nullable', 'string'],
            'about_body_en' => ['nullable', 'string'],
            'location_intro_ar' => ['nullable', 'string'],
            'location_intro_en' => ['nullable', 'string'],
            'awards_teaser_blurb_ar' => ['nullable', 'string'],
            'awards_teaser_blurb_en' => ['nullable', 'string'],
            'stats_attendees_count_ar' => ['nullable', 'string', 'max:50'],
            'stats_attendees_count_en' => ['nullable', 'string', 'max:50'],
            'stats_countries_count_ar' => ['nullable', 'string', 'max:50'],
            'stats_countries_count_en' => ['nullable', 'string', 'max:50'],
            'visible_sections' => ['nullable', 'array'],
            'visible_sections.*' => [Rule::in(Event::TOGGLEABLE_SECTIONS)],
        ];
    }
}
