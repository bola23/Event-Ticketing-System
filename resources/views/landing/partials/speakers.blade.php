{{-- resources/views/landing/partials/speakers.blade.php --}}
@if($event->speakers->isNotEmpty())
    <section id="speakers" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-coral">{{ __('Featured Speakers') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12">{{ __('Voices shaping the industry.') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-7">
            @foreach($event->speakers as $speaker)
                <div x-data="{ open: false }" class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    <img src="{{ $speaker->photo_path ?? '/images/placeholder-speaker.png' }}" class="w-full aspect-square object-cover" alt="{{ app()->getLocale() === 'ar' ? $speaker->name_ar : $speaker->name_en }}">
                    <div class="p-5">
                        <h3 class="font-display font-bold text-lg mb-1">{{ app()->getLocale() === 'ar' ? $speaker->name_ar : $speaker->name_en }}</h3>
                        <p class="text-sm text-gray-400 mb-3">{{ app()->getLocale() === 'ar' ? $speaker->title_ar : $speaker->title_en }}</p>
                        <button type="button" class="text-sm font-bold border border-white/25 rounded-md px-3 py-1.5 hover:bg-white hover:text-ccs-black" @click="open = !open">{{ __('Bio') }}</button>
                        <p x-show="open" x-cloak class="text-sm text-gray-400 leading-relaxed mt-3">{{ app()->getLocale() === 'ar' ? $speaker->bio_ar : $speaker->bio_en }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
