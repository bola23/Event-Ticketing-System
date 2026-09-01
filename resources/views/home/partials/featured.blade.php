{{-- resources/views/home/partials/featured.blade.php --}}
{{-- Not tied to a specific event (Creators Hub has none published yet — see HomeController) —
     this sets expectations for the kind of experience Creators Hub events will deliver. --}}
<section id="experience" class="relative overflow-hidden" style="background: linear-gradient(135deg, var(--color-hub-purple), var(--color-hub-dark) 75%);">
    <div class="hub-section grid grid-cols-1 lg:grid-cols-[1fr_1fr] gap-16 items-center">
        <div data-reveal>
            <p class="hub-eyebrow text-hub-lavender/70 mb-6">{{ __('The Creators Hub Experience') }}</p>
            <h2 class="font-display text-[clamp(2rem,4.5vw,3.5rem)] font-extrabold leading-[1.05] tracking-tight mb-6">
                {{ __('Curated gatherings, not trade-show sprawl.') }}
            </h2>
            <p class="text-lg text-hub-lavender/85 leading-relaxed max-w-xl">
                {{ __('Every Creators Hub event is built around real conversation — smaller rooms, sharper programming, and enough time to actually meet the people in them.') }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-px bg-white/15 rounded-2xl overflow-hidden border border-white/15" data-reveal data-reveal-delay="1">
            @foreach ([
                ['label' => __('Format'), 'value' => __('Talks, tours & workshops')],
                ['label' => __('Audience'), 'value' => __('Industry professionals only')],
                ['label' => __('Focus'), 'value' => __('Interior design & construction')],
                ['label' => __('Access'), 'value' => __('No login required')],
            ] as $detail)
                <div class="bg-hub-purple p-6 md:p-8">
                    <p class="hub-eyebrow text-hub-lavender/60 mb-2">{{ $detail['label'] }}</p>
                    <p class="font-display text-lg font-bold">{{ $detail['value'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
