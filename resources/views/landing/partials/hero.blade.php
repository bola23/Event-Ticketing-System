{{-- resources/views/landing/partials/hero.blade.php --}}
@php
    $headline = $event->contentFor(\App\Enums\LandingPageSection::Hero, 'headline');
    $headlineText = app()->getLocale() === 'ar'
        ? ($headline?->value_ar ?: $event->name_ar)
        : ($headline?->value_en ?: $event->name_en);
    $venueName = app()->getLocale() === 'ar' ? $event->venue_name_ar : $event->venue_name_en;
    $tagline = app()->getLocale() === 'ar' ? $event->tagline_ar : $event->tagline_en;
@endphp
<section id="hero" class="ccs-hero scroll-mt-24 relative min-h-screen flex flex-col justify-center items-center text-center overflow-hidden px-[clamp(20px,6vw,80px)] pt-[140px] pb-[100px]">
    <div class="relative max-w-5xl flex flex-col items-center ccs-fade-up">
        <p class="text-sm font-bold tracking-[0.14em] uppercase text-ccs-coral mb-5">
            {{ $event->start_date->format('M j') }}&ndash;{{ $event->end_date->format('j, Y') }}
            @if($venueName) &middot; {{ $venueName }} @endif
        </p>
        <h1 class="font-display text-[clamp(2.75rem,9vw,6rem)] font-extrabold leading-[0.98] tracking-tight mb-7">{{ $headlineText }}</h1>
        @if($tagline)
            <p class="text-lg md:text-2xl text-gray-300 max-w-xl leading-relaxed mb-11">{{ $tagline }}</p>
        @endif
        <div class="flex flex-wrap justify-center gap-4 mb-16">
            <a href="#tickets" class="px-8 py-4 rounded-lg ccs-btn-red text-base font-bold">{{ __('Request Your Ticket') }}</a>
            <a href="{{ route('agenda.show', $event) }}" class="px-8 py-4 rounded-lg border border-white/35 text-base font-bold">{{ __('Explore Event') }}</a>
        </div>

        <div class="flex flex-wrap justify-center gap-3 md:gap-7" x-data="{
                now: Date.now(),
                target: new Date('{{ $event->start_date->toDateString() }}').getTime(),
                get diff() { return Math.max(0, this.target - this.now); },
                get d() { return String(Math.floor(this.diff / 86400000)).padStart(2, '0'); },
                get h() { return String(Math.floor(this.diff / 3600000) % 24).padStart(2, '0'); },
                get m() { return String(Math.floor(this.diff / 60000) % 60).padStart(2, '0'); },
                get s() { return String(Math.floor(this.diff / 1000) % 60).padStart(2, '0'); },
            }" x-init="setInterval(() => now = Date.now(), 1000)">
            <div class="flex flex-col items-center px-6 py-4 bg-white/5 border border-white/10 rounded-xl min-w-[88px]">
                <span class="text-3xl md:text-4xl font-extrabold tabular-nums" x-text="d"></span>
                <span class="text-xs uppercase tracking-wide text-gray-400 mt-1.5">{{ __('Days') }}</span>
            </div>
            <div class="flex flex-col items-center px-6 py-4 bg-white/5 border border-white/10 rounded-xl min-w-[88px]">
                <span class="text-3xl md:text-4xl font-extrabold tabular-nums" x-text="h"></span>
                <span class="text-xs uppercase tracking-wide text-gray-400 mt-1.5">{{ __('Hours') }}</span>
            </div>
            <div class="flex flex-col items-center px-6 py-4 bg-white/5 border border-white/10 rounded-xl min-w-[88px]">
                <span class="text-3xl md:text-4xl font-extrabold tabular-nums" x-text="m"></span>
                <span class="text-xs uppercase tracking-wide text-gray-400 mt-1.5">{{ __('Min') }}</span>
            </div>
            <div class="flex flex-col items-center px-6 py-4 bg-white/5 border border-white/10 rounded-xl min-w-[88px]">
                <span class="text-3xl md:text-4xl font-extrabold tabular-nums" x-text="s"></span>
                <span class="text-xs uppercase tracking-wide text-gray-400 mt-1.5">{{ __('Sec') }}</span>
            </div>
        </div>
    </div>
</section>
