<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LandingPageSection;
use Database\Factories\LandingPageContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageContent extends Model
{
    use HasFactory;

    protected $table = 'landing_page_content';

    protected $fillable = [
        'event_id', 'section', 'field_key', 'value_ar', 'value_en',
    ];

    protected $casts = [
        'section' => LandingPageSection::class,
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory(): LandingPageContentFactory
    {
        return LandingPageContentFactory::new();
    }
}
