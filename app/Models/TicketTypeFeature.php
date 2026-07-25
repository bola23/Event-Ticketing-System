<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TicketTypeFeatureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketTypeFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_type_id', 'text_ar', 'text_en', 'sort_order',
    ];

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    protected static function newFactory(): TicketTypeFeatureFactory
    {
        return TicketTypeFeatureFactory::new();
    }
}
