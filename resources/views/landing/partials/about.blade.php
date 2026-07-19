{{-- resources/views/landing/partials/about.blade.php --}}
@php $about = $event->contentFor(\App\Enums\LandingPageSection::About, 'body'); @endphp
@if($about)
    <section id="about" class="container mx-auto px-4 py-5">
        <h2>{{ __('About the Event') }}</h2>
        <p>{{ app()->getLocale() === 'ar' ? $about->value_ar : $about->value_en }}</p>
    </section>
@endif
