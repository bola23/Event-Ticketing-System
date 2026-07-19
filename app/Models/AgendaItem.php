<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AgendaItemType;
use Database\Factories\AgendaItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'speaker_id', 'workshop_id', 'day_date', 'start_time', 'end_time',
        'title_ar', 'title_en', 'type', 'sort_order',
    ];

    protected $casts = [
        'day_date' => 'date',
        'type' => AgendaItemType::class,
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    protected static function newFactory(): AgendaItemFactory
    {
        return AgendaItemFactory::new();
    }
}
