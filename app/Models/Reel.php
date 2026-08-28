<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ResolvesStoredMedia;
use Database\Factories\ReelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reel extends Model
{
    use HasFactory, ResolvesStoredMedia;

    protected $fillable = [
        'event_id', 'video_path', 'poster_path', 'caption_ar', 'caption_en', 'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function videoUrl(): ?string
    {
        return $this->storedMediaUrl($this->video_path);
    }

    public function posterUrl(): ?string
    {
        return $this->storedMediaUrl($this->poster_path);
    }

    public function caption(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->caption_ar : $this->caption_en;
    }

    protected static function newFactory(): ReelFactory
    {
        return ReelFactory::new();
    }
}
