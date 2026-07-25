{{-- resources/views/landing/partials/speakers.blade.php --}}
@if($event->speakers->isNotEmpty() && $event->isSectionVisible('speakers'))
    <section id="speakers" class="ccs-section scroll-mt-24">
        <div class="ccs-eyebrow text-ccs-coral" data-reveal>{{ __('Featured Speakers') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12" data-reveal>{{ __('Voices shaping the industry.') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($event->speakers as $speaker)
                <div class="group relative aspect-3/4 rounded-2xl border border-white/10 overflow-hidden select-none" data-reveal data-reveal-delay="{{ min($loop->iteration, 5) }}">
                    <img src="{{ $speaker->photo_path ?? '/images/placeholder-speaker.png' }}" alt="{{ app()->getLocale() === 'ar' ? $speaker->name_ar : $speaker->name_en }}" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" loading="lazy">
                    <div class="absolute inset-0 bg-ccs-black/95 p-8 flex flex-col justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-500 ease-out">
                        <h3 class="font-display font-bold text-xl mb-2">{{ app()->getLocale() === 'ar' ? $speaker->name_ar : $speaker->name_en }}</h3>
                        <p class="text-ccs-coral font-bold text-sm uppercase tracking-wide mb-5">{{ app()->getLocale() === 'ar' ? $speaker->title_ar : $speaker->title_en }}</p>
                        <div class="w-10 h-px bg-white/20 mb-5"></div>
                        <p class="text-sm text-gray-400 leading-relaxed">{{ app()->getLocale() === 'ar' ? $speaker->bio_ar : $speaker->bio_en }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
