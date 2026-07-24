{{-- resources/views/landing/partials/workshops-teaser.blade.php --}}
@if($event->workshops->isNotEmpty())
    <section id="workshops" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-gold">{{ __('Workshops') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12 max-w-xl">{{ __('Choose your own workshops.') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            @foreach($event->workshops->take(3) as $workshop)
                <div class="bg-white/5 border border-white/10 rounded-2xl p-7 flex flex-col gap-4">
                    <h3 class="font-display text-lg font-bold">{{ app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en }}</h3>
                    <p class="text-sm text-gray-400 leading-relaxed flex-1">{{ app()->getLocale() === 'ar' ? $workshop->description_ar : $workshop->description_en }}</p>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ trans_choice(':count seat|:count seats', $workshop->capacity, ['count' => $workshop->capacity]) }}</p>
                    <a href="{{ route('workshops.show', [$event, $workshop]) }}" class="text-sm font-bold text-ccs-teal-light hover:underline">{{ __('View Workshop') }}</a>
                </div>
            @endforeach
        </div>
        <a href="{{ route('workshops.index', $event) }}" class="inline-block px-7 py-3.5 rounded-lg border border-white/35 text-sm font-bold hover:bg-white hover:text-ccs-black">{{ __('See All Workshops') }}</a>
    </section>
@endif
