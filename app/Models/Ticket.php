<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'ticket_type_id', 'name', 'email', 'phone', 'ticket_number',
        'status', 'ticket_id', 'workshop_booking_key', 'is_paid', 'payment_method', 'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'is_paid' => 'boolean',
            'checked_in_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TicketRequestAnswer::class);
    }

    protected static function newFactory(): TicketFactory
    {
        return TicketFactory::new();
    }
}
