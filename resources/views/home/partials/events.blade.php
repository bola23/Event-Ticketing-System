{{-- resources/views/home/partials/events.blade.php --}}
<section id="events" class="scroll-mt-24 hub-section">
    <div class="flex flex-wrap items-end justify-between gap-6 mb-14" data-reveal>
        <div>
            <p class="hub-eyebrow text-hub-purple-light mb-4">{{ __('Events') }}</p>
            <h2 class="font-display text-[clamp(2rem,4.5vw,3.25rem)] font-extrabold leading-[1.05] tracking-tight max-w-2xl">
                {{ __('Events that bring the industry together.') }}
            </h2>
        </div>
        <a href="{{ route('events.index') }}" class="shrink-0 text-sm font-bold text-hub-purple-light hover:text-white transition-colors whitespace-nowrap">{{ __('View All Events') }} &rarr;</a>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-white/10 hub-blueprint-grid px-8 py-20 md:px-16 md:py-28 text-center" data-reveal data-reveal-delay="1">
        <div class="absolute inset-0 bg-gradient-to-b from-hub-dark/60 via-hub-dark/85 to-hub-dark" aria-hidden="true"></div>
        <div class="relative flex flex-col items-center gap-5">
            <span class="hub-eyebrow text-hub-purple-light">{{ __('Coming Soon') }}</span>
            <p class="font-display text-2xl md:text-4xl font-bold max-w-2xl">{{ __('Our first gathering is in the works.') }}</p>
            <p class="text-gray-400 max-w-lg">{{ __("Get in touch to hear about it first — dates, format, and who's speaking, before anyone else.") }}</p>
            <a href="#contact" class="mt-3 px-7 py-3.5 rounded-lg border border-white/25 text-sm font-bold transition-colors hover:bg-white/5">{{ __('Get in Touch') }}</a>
        </div>
    </div>
</section>
