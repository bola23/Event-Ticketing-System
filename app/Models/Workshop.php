<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkshopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'speaker_id', 'slug', 'name_ar', 'name_en',
        'description_ar', 'description_en', 'capacity', 'sort_order',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    protected static function newFactory(): WorkshopFactory
    {
        return WorkshopFactory::new();
    }
}
