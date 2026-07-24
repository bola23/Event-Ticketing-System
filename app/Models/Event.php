<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\LandingPageSection;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function speakers(): HasMany
    {
        return $this->hasMany(Speaker::class)->orderBy('sort_order');
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class)->orderBy('sort_order');
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class)->orderBy('sort_order');
    }

    public function workshops(): HasMany
    {
        return $this->hasMany(Workshop::class)->orderBy('sort_order');
    }

    public function agendaItems(): HasMany
    {
        return $this->hasMany(AgendaItem::class)->orderBy('day_date')->orderBy('start_time');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order');
    }

    public function galleryPhotos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->orderBy('sort_order');
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class)->latest();
    }

    public function landingPageContent(): HasMany
    {
        return $this->hasMany(LandingPageContent::class);
    }

    public function contentFor(LandingPageSection $section, string $fieldKey): ?LandingPageContent
    {
        return $this->landingPageContent
            ->first(fn (LandingPageContent $content) => $content->section === $section && $content->field_key === $fieldKey);
    }

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }
}
