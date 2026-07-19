{{-- resources/views/landing/partials/location.blade.php --}}
@php $intro = $event->contentFor(\App\Enums\LandingPageSection::Location, 'intro'); @endphp
@if($intro || $event->venue_address_en)
    <section id="location" class="container mx-auto px-4 py-5">
        <h2>{{ __('Location') }}</h2>
        @if($intro)
            <p>{{ app()->getLocale() === 'ar' ? $intro->value_ar : $intro->value_en }}</p>
        @endif
        <p>{{ app()->getLocale() === 'ar' ? $event->venue_address_ar : $event->venue_address_en }}</p>
        @if($event->map_embed_url)
            <iframe src="{{ $event->map_embed_url }}" width="100%" height="300" style="border:0;" loading="lazy"></iframe>
        @endif
    </section>
@endif
