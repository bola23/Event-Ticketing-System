<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AgendaItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendaItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'speaker_id' => ['nullable', Rule::exists('speakers', 'id')->where('event_id', $this->route('event')->id)],
            'workshop_id' => ['nullable', Rule::exists('workshops', 'id')->where('event_id', $this->route('event')->id)],
            'day_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_column(AgendaItemType::cases(), 'value'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
