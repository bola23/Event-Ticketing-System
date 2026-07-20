{{-- resources/views/landing/partials/awards-teaser.blade.php --}}
<section id="awards" class="container mx-auto px-4 py-5">
    <h2>{{ __('Awards') }}</h2>
    @php $blurb = $event->contentFor(\App\Enums\LandingPageSection::AwardsTeaser, 'blurb'); @endphp
    @if($blurb)
        <p>{{ app()->getLocale() === 'ar' ? $blurb->value_ar : $blurb->value_en }}</p>
    @endif
    <a href="{{ route('awards.show', $event) }}" class="border border-white text-white px-4 py-2 rounded hover:bg-white hover:text-ccs-black">{{ __('Learn More') }}</a>
</section>
