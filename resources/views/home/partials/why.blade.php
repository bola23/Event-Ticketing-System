{{-- resources/views/home/partials/why.blade.php --}}
<section id="why" class="scroll-mt-24 hub-section bg-hub-lavender text-hub-dark">
    <p class="hub-eyebrow text-hub-purple mb-4" data-reveal>{{ __('Why Creators Hub') }}</p>
    <h2 class="font-display text-[clamp(2rem,4.5vw,3.25rem)] font-extrabold leading-[1.05] tracking-tight max-w-2xl mb-14" data-reveal>
        {{ __('Built around what the industry actually needs.') }}
    </h2>

    <div class="flex flex-col border-t border-hub-dark/15">
        @foreach ([
            ['title' => __('Connect'), 'body' => __('Meet the people and businesses shaping the industry — face to face, at events built for real conversation.')],
            ['title' => __('Discover'), 'body' => __('See new ideas, materials, and technologies before they reach the wider market.')],
            ['title' => __('Learn'), 'body' => __('Hear directly from the architects, designers, and builders setting the standard.')],
            ['title' => __('Collaborate'), 'body' => __('Turn conversations into projects, partnerships, and work that outlasts the event.')],
        ] as $pillar)
            <div class="group grid grid-cols-1 md:grid-cols-[1fr_auto_1.2fr] items-center gap-4 md:gap-10 py-8 md:py-10 border-b border-hub-dark/15" data-reveal>
                <h3 class="font-display text-3xl md:text-5xl font-extrabold tracking-tight transition-colors group-hover:text-hub-purple">{{ $pillar['title'] }}</h3>
                <span class="hidden md:block w-10 h-px bg-hub-dark/20" aria-hidden="true"></span>
                <p class="text-base md:text-lg text-hub-dark/70 leading-relaxed max-w-md">{{ $pillar['body'] }}</p>
            </div>
        @endforeach
    </div>
</section>
