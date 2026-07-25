{{-- resources/views/landing/partials/about.blade.php --}}
@php $about = $event->contentFor(\App\Enums\LandingPageSection::About, 'body'); @endphp
@if($about && $event->isSectionVisible('about'))
    <section id="about" class="ccs-section scroll-mt-24 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center pt-32">
        <div data-reveal>
            <div class="ccs-eyebrow text-ccs-teal-light">{{ __('About the Event') }}</div>
            <p class="text-lg md:text-xl text-gray-300 leading-relaxed max-w-xl">{{ app()->getLocale() === 'ar' ? $about->value_ar : $about->value_en }}</p>
        </div>
        <div class="aspect-[4/5] rounded-2xl border border-white/10 bg-white/5" aria-hidden="true" data-reveal data-reveal-delay="1"></div>
    </section>
@endif
