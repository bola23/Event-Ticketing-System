{{-- resources/views/home/partials/hero.blade.php --}}
<section id="hero" class="relative min-h-screen flex items-center overflow-hidden px-5 md:px-16 pt-32 pb-20 hub-blueprint-grid">
    <div class="absolute inset-0 bg-gradient-to-b from-hub-dark via-hub-dark/95 to-hub-dark" aria-hidden="true"></div>
    <div class="absolute -top-40 -inset-x-20 h-[520px] rounded-full opacity-25 blur-3xl" style="background: radial-gradient(closest-side, var(--color-hub-purple), transparent);" aria-hidden="true"></div>

    <div class="relative w-full max-w-[1520px] mx-auto grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-16 items-center">
        <div class="hub-fade-up">
            <p class="hub-eyebrow text-hub-purple-light mb-6">{{ __('Interior Design × Construction × Events') }}</p>
            <h1 class="font-display text-[clamp(2.75rem,6.5vw,5.5rem)] font-extrabold leading-[0.98] tracking-tight mb-8">
                {{ __('Where design meets construction.') }}
            </h1>
            <p class="text-lg md:text-xl text-gray-300 leading-relaxed max-w-xl mb-10">
                {{ __('Creators Hub brings together designers, architects, contractors, and brands through events, connections, and opportunities that shape the built environment.') }}
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('events.index') }}" class="px-8 py-4 rounded-lg hub-btn-primary text-base font-bold transition-transform duration-200 hover:scale-[1.03]">{{ __('Explore Events') }}</a>
                <a href="#about" class="px-8 py-4 rounded-lg border border-white/25 text-base font-bold transition-colors hover:bg-white/5">{{ __('Discover Creators Hub') }}</a>
            </div>
        </div>

        <div class="relative hidden lg:block hub-fade-up" style="animation-delay: 0.15s" aria-hidden="true">
            <svg viewBox="0 0 520 520" class="absolute inset-0 w-full h-full text-hub-purple-light/50" fill="none" stroke="currentColor" stroke-width="1">
                {{-- dimension line annotation — quiet supporting texture around the real mark --}}
                <g opacity="0.6">
                    <path d="M60 460 L220 460" />
                    <path d="M60 454 L60 466" />
                    <path d="M220 454 L220 466" />
                </g>
                <text x="140" y="482" text-anchor="middle" fill="currentColor" stroke="none" font-size="11" letter-spacing="1" font-family="Inter, sans-serif" opacity="0.7">4.20 M</text>
                {{-- corner register marks --}}
                <g opacity="0.5">
                    <path d="M30 30 L30 60 M30 30 L60 30" />
                    <path d="M490 490 L490 460 M490 490 L460 490" />
                </g>
            </svg>
            <img
                src="{{ asset('images/creators-hub/mark-secondary.png') }}"
                alt=""
                class="relative w-full max-w-[380px] mx-auto drop-shadow-[0_30px_60px_rgba(60,52,137,0.45)]"
            >
        </div>
    </div>

    <a href="#about" class="absolute bottom-8 inset-x-0 mx-auto w-fit flex flex-col items-center gap-2 text-xs font-semibold tracking-widest uppercase text-gray-400 hover:text-white transition-colors" aria-label="{{ __('Scroll to explore') }}">
        <span>{{ __('Scroll') }}</span>
        <span class="w-px h-8 bg-gradient-to-b from-hub-purple-light to-transparent"></span>
    </a>
</section>
