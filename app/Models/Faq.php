<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'question_ar', 'question_en', 'answer_ar', 'answer_en', 'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): FaqFactory
    {
        return FaqFactory::new();
    }
}
