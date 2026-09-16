<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SpeakerRequestStatus;
use App\Models\Concerns\ResolvesStoredMedia;
use Database\Factories\SpeakerRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeakerRequest extends Model
{
    use HasFactory;
    use ResolvesStoredMedia;

    protected $attributes = [
        'status' => SpeakerRequestStatus::Pending,
    ];

    protected $fillable = [
        'event_id', 'name_ar', 'name_en', 'title_ar', 'title_en', 'bio_ar', 'bio_en',
        'photo_path', 'email', 'phone', 'message', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => SpeakerRequestStatus::class,
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function photoUrl(): ?string
    {
        return $this->storedMediaUrl($this->photo_path);
    }

    protected static function newFactory(): SpeakerRequestFactory
    {
        return SpeakerRequestFactory::new();
    }
}
