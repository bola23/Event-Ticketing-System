<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name_ar', 'name_en', 'tagline_ar', 'tagline_en',
        'start_date', 'end_date',
        'venue_name_ar', 'venue_name_en', 'venue_address_ar', 'venue_address_en',
        'map_embed_url', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => EventStatus::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }
}
