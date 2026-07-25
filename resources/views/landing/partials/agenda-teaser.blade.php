{{-- resources/views/landing/partials/agenda-teaser.blade.php --}}
@if($event->agendaItems->isNotEmpty() && $event->isSectionVisible('agenda-teaser'))
    @php $days = $event->agendaItems->groupBy(fn ($item) => $item->day_date->toDateString())->values(); @endphp
    <section id="agenda-teaser" class="ccs-section scroll-mt-24" x-data="{ day: 0 }">
        <div class="ccs-eyebrow text-ccs-teal-light" data-reveal>{{ __('Agenda') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-10" data-reveal>{{ __('Three days, deliberately paced.') }}</h2>

        <div class="flex gap-3 mb-10 flex-wrap">
            @foreach($days as $index => $sessions)
                <button type="button" @click="day = {{ $index }}" :class="day === {{ $index }} ? 'bg-ccs-red border-ccs-red' : 'border-white/10'" class="px-6 py-3.5 rounded-lg border text-sm font-bold text-gray-300 transition-colors duration-300">
                    {{ __('Day :n', ['n' => $index + 1]) }} &middot; {{ $sessions->first()->day_date->format('M j') }}
                </button>
            @endforeach
        </div>

        @foreach($days as $index => $sessions)
            <div x-show="day === {{ $index }}" x-cloak x-transition class="flex flex-col">
                @foreach($sessions as $item)
                    <div class="grid grid-cols-[80px_1fr_auto] md:grid-cols-[110px_1fr_auto] gap-4 md:gap-6 items-center py-6 border-b border-white/10">
                        <span class="text-sm font-bold text-gray-400 tabular-nums">{{ $item->start_time->format('H:i') }}</span>
                        <div>
                            <div class="font-display font-bold text-lg mb-1">{{ app()->getLocale() === 'ar' ? $item->title_ar : $item->title_en }}</div>
                            @if($item->speaker)
                                <div class="text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? $item->speaker->name_ar : $item->speaker->name_en }}</div>
                            @endif
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wide text-ccs-coral border border-ccs-coral/40 rounded-md px-3 py-1.5 whitespace-nowrap">{{ __(ucfirst($item->type->value)) }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach

        <a href="{{ route('agenda.show', $event) }}" class="inline-block mt-10 px-7 py-3.5 rounded-lg border border-white/35 text-sm font-bold hover:bg-white hover:text-ccs-black transition-colors">{{ __('View Full Agenda') }}</a>
    </section>
@endif
