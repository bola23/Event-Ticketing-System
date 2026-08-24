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
            <svg viewBox="0 0 520 520" class="w-full h-auto text-hub-purple-light/80" fill="none" stroke="currentColor" stroke-width="1.25">
                {{-- isometric wireframe structure — the page's recurring blueprint motif --}}
                <g class="hub-draw-line" style="--hub-line-length: 2400">
                    <path d="M260 90 L380 155 L260 220 L140 155 Z" />
                    <path d="M140 155 L140 335 L260 400 L260 220" />
                    <path d="M380 155 L380 335 L260 400" />
                    <path d="M260 220 L260 400" stroke-dasharray="4 6" stroke-width="1" />
                    <path d="M200 122 L200 302 L260 335" stroke-width="1" opacity="0.55" />
                </g>
                <g fill="currentColor" stroke="none">
                    <circle cx="260" cy="90" r="3.5" />
                    <circle cx="380" cy="155" r="3.5" />
                    <circle cx="260" cy="220" r="3.5" />
                    <circle cx="140" cy="155" r="3.5" />
                    <circle cx="140" cy="335" r="3.5" />
                    <circle cx="260" cy="400" r="3.5" />
                    <circle cx="380" cy="335" r="3.5" />
                </g>
                {{-- dimension line annotation --}}
                <g opacity="0.6" stroke-width="1">
                    <path d="M140 430 L260 430" />
                    <path d="M140 424 L140 436" />
                    <path d="M260 424 L260 436" />
                </g>
                <text x="200" y="452" text-anchor="middle" fill="currentColor" stroke="none" font-size="11" letter-spacing="1" font-family="Inter, sans-serif" opacity="0.7">4.20 M</text>
                {{-- corner register mark --}}
                <g opacity="0.5" stroke-width="1">
                    <path d="M40 40 L40 70 M40 40 L70 40" />
                    <path d="M480 480 L480 450 M480 480 L450 480" />
                </g>
            </svg>
        </div>
    </div>

    <a href="#about" class="absolute bottom-8 inset-x-0 mx-auto w-fit flex flex-col items-center gap-2 text-xs font-semibold tracking-widest uppercase text-gray-400 hover:text-white transition-colors" aria-label="{{ __('Scroll to explore') }}">
        <span>{{ __('Scroll') }}</span>
        <span class="w-px h-8 bg-gradient-to-b from-hub-purple-light to-transparent"></span>
    </a>
</section>
