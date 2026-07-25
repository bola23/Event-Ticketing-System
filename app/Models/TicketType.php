<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TicketTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name_ar', 'name_en', 'description_ar', 'description_en',
        'price', 'currency', 'workshop_slot_count', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(TicketTypeFeature::class)->orderBy('sort_order');
    }

    protected static function newFactory(): TicketTypeFactory
    {
        return TicketTypeFactory::new();
    }
}
