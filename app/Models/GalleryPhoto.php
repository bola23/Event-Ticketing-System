<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GalleryPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'image_path', 'caption_ar', 'caption_en', 'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): GalleryPhotoFactory
    {
        return GalleryPhotoFactory::new();
    }
}
