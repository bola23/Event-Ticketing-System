<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SponsorRequestStatus;
use App\Models\Concerns\ResolvesStoredMedia;
use Database\Factories\SponsorRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorRequest extends Model
{
    use HasFactory;
    use ResolvesStoredMedia;

    protected $attributes = [
        'status' => SponsorRequestStatus::Pending,
    ];

    protected $fillable = [
        'event_id', 'name_ar', 'name_en', 'contact_name', 'email', 'phone',
        'logo_path', 'website_url', 'instagram_url', 'facebook_url', 'message', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => SponsorRequestStatus::class,
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function logoUrl(): ?string
    {
        return $this->storedMediaUrl($this->logo_path);
    }

    protected static function newFactory(): SponsorRequestFactory
    {
        return SponsorRequestFactory::new();
    }
}
