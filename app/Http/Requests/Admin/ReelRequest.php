<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Reel;
use Illuminate\Foundation\Http\FormRequest;

class ReelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $reel = $this->route('reel');
        $videoAlreadyStored = $reel instanceof Reel && $reel->exists;

        return [
            // A reel is nothing without its clip, so require one on create but let an edit keep
            // whatever is already stored when no replacement is chosen.
            'video' => [$videoAlreadyStored ? 'nullable' : 'required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:9216'],
            'poster' => ['nullable', 'image', 'max:4096'],
            'caption_ar' => ['nullable', 'string', 'max:255'],
            'caption_en' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'video' => __('Video'),
            'poster' => __('Poster Image'),
        ];
    }
}
