<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SponsorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'tier' => ['required', Rule::in(['platinum', 'gold', 'silver', 'bronze'])],
            'website_url' => ['nullable', 'url'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
