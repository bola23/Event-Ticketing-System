{{-- resources/views/landing/partials/location.blade.php --}}
@php $intro = $event->contentFor(\App\Enums\LandingPageSection::Location, 'intro'); @endphp
@if($intro || $event->venue_address_en)
    <section id="location" class="ccs-section grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <div>
            <div class="ccs-eyebrow text-ccs-teal-light">{{ __('Venue') }}</div>
            <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-6">{{ app()->getLocale() === 'ar' ? $event->venue_name_ar : $event->venue_name_en }}</h2>
            @if($intro)
                <p class="text-gray-300 leading-relaxed mb-4">{{ app()->getLocale() === 'ar' ? $intro->value_ar : $intro->value_en }}</p>
            @endif
            <p class="text-gray-400 leading-relaxed">{{ app()->getLocale() === 'ar' ? $event->venue_address_ar : $event->venue_address_en }}</p>
        </div>
        @if($event->map_embed_url)
            <iframe src="{{ $event->map_embed_url }}" class="w-full aspect-video rounded-2xl border border-white/10" style="border:0;" loading="lazy"></iframe>
        @else
            <div class="aspect-video rounded-2xl border border-white/10 bg-white/5" aria-hidden="true"></div>
        @endif
    </section>
@endif
