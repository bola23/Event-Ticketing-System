<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }
}
