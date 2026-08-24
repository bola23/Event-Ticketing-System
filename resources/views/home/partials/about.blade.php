{{-- resources/views/home/partials/about.blade.php --}}
<section id="about" class="scroll-mt-24 hub-section bg-hub-lavender text-hub-dark grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
    <div data-reveal>
        <p class="hub-eyebrow text-hub-purple mb-6">{{ __('About Creators Hub') }}</p>
        <h2 class="font-display text-[clamp(2rem,4.5vw,3.25rem)] font-extrabold leading-[1.05] tracking-tight mb-6">
            {{ __('A hub for the people shaping spaces.') }}
        </h2>
        <p class="font-display text-2xl md:text-3xl font-bold leading-tight text-hub-purple">
            {{ __('Designers. Builders. Brands. Creators.') }}<br>{{ __('One connected ecosystem.') }}
        </p>
    </div>

    <div data-reveal data-reveal-delay="1" class="flex flex-col gap-8">
        <p class="text-lg leading-relaxed text-hub-dark/80 max-w-xl">
            {{ __('Creators Hub connects the people and organizations behind interior design and construction — the architects, contractors, and developers who shape the built environment, and the suppliers, brands, and creative communities who work alongside them.') }}
        </p>
        <ul class="flex flex-wrap items-center gap-x-4 gap-y-3 pt-4 border-t border-hub-purple/20 text-sm font-bold uppercase tracking-wide text-hub-purple">
            @foreach ([
                __('Designers'), __('Architects'), __('Contractors'), __('Developers'),
                __('Suppliers'), __('Brands'), __('Industry Professionals'),
            ] as $index => $role)
                @if($index > 0)<li aria-hidden="true" class="text-hub-purple/30">/</li>@endif
                <li>{{ $role }}</li>
            @endforeach
        </ul>
    </div>
</section>
