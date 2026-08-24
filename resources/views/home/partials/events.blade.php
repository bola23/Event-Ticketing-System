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

    @if(!$featuredEvent)
        <div class="relative overflow-hidden rounded-2xl border border-white/10 hub-blueprint-grid px-8 py-20 md:px-16 md:py-28 text-center" data-reveal data-reveal-delay="1">
            <div class="absolute inset-0 bg-gradient-to-b from-hub-dark/60 via-hub-dark/85 to-hub-dark" aria-hidden="true"></div>
            <div class="relative flex flex-col items-center gap-5">
                <span class="hub-eyebrow text-hub-purple-light">{{ __('Coming Soon') }}</span>
                <p class="font-display text-2xl md:text-4xl font-bold max-w-2xl">{{ __('Our first gathering is in the works.') }}</p>
                <p class="text-gray-400 max-w-lg">{{ __("Get in touch to hear about it first — dates, format, and who's speaking, before anyone else.") }}</p>
                <a href="#contact" class="mt-3 px-7 py-3.5 rounded-lg border border-white/25 text-sm font-bold transition-colors hover:bg-white/5">{{ __('Get in Touch') }}</a>
            </div>
        </div>
    @else
        <a href="{{ route('landing.show', $featuredEvent) }}" class="group relative block overflow-hidden rounded-2xl border border-white/10 hub-blueprint-grid" data-reveal data-reveal-delay="1" style="background-color: var(--color-hub-purple);">
            <div class="absolute inset-0 bg-gradient-to-t from-hub-dark/80 via-hub-dark/10 to-transparent" aria-hidden="true"></div>
            <div class="relative px-8 py-16 md:px-16 md:py-24">
                <span class="hub-eyebrow text-hub-lavender/70 mb-4 block">{{ $featuredEvent->start_date->format('M j') }}&ndash;{{ $featuredEvent->end_date->format('j, Y') }}</span>
                <h3 class="font-display text-3xl md:text-5xl font-extrabold leading-[1.05] tracking-tight mb-4 transition-colors group-hover:text-hub-lavender">
                    {{ app()->getLocale() === 'ar' ? $featuredEvent->name_ar : $featuredEvent->name_en }}
                </h3>
                @if($featuredEvent->venue_name_en)
                    <p class="text-hub-lavender/80 mb-2">{{ app()->getLocale() === 'ar' ? $featuredEvent->venue_name_ar : $featuredEvent->venue_name_en }}</p>
                @endif
                @if($featuredEvent->tagline_en)
                    <p class="text-hub-lavender/70 max-w-xl mb-8">{{ app()->getLocale() === 'ar' ? $featuredEvent->tagline_ar : $featuredEvent->tagline_en }}</p>
                @endif
                <span class="inline-flex px-6 py-3 rounded-lg bg-white text-hub-dark text-sm font-bold">{{ __('View Event') }}</span>
            </div>
        </a>

        @if($otherEvents->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                @foreach($otherEvents as $event)
                    <a href="{{ route('landing.show', $event) }}" class="group block rounded-2xl border border-white/10 overflow-hidden hover:border-hub-purple-light/40 transition-colors" data-reveal>
                        <div class="hub-blueprint-grid aspect-[16/9] flex items-end p-6" style="background-color: var(--color-hub-purple);">
                            <span class="hub-eyebrow text-hub-lavender/70">{{ $event->start_date->format('M j') }}&ndash;{{ $event->end_date->format('j, Y') }}</span>
                        </div>
                        <div class="p-6">
                            <h3 class="font-display text-xl font-bold mb-2 transition-colors group-hover:text-hub-purple-light">
                                {{ app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en }}
                            </h3>
                            @if($event->venue_name_en)
                                <p class="text-sm text-gray-400">{{ app()->getLocale() === 'ar' ? $event->venue_name_ar : $event->venue_name_en }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</section>
