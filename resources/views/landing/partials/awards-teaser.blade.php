{{-- resources/views/landing/partials/awards-teaser.blade.php --}}
<section id="awards" class="ccs-section">
    <div class="ccs-eyebrow text-ccs-coral">{{ __('CCS Awards') }}</div>
    <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-6">{{ __("Honoring the year's defining work.") }}</h2>
    @php $blurb = $event->contentFor(\App\Enums\LandingPageSection::AwardsTeaser, 'blurb'); @endphp
    @if($blurb)
        <p class="text-lg text-gray-300 max-w-2xl leading-relaxed mb-8">{{ app()->getLocale() === 'ar' ? $blurb->value_ar : $blurb->value_en }}</p>
    @endif
    <a href="{{ route('awards.show', $event) }}" class="inline-block px-7 py-3.5 rounded-lg border border-white/35 text-sm font-bold hover:bg-white hover:text-ccs-black">{{ __('Learn More') }}</a>
</section>
