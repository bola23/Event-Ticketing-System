{{-- resources/views/landing/partials/partners.blade.php --}}
@if($event->sponsors->isNotEmpty())
    <section id="partners" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-teal-light">{{ __('Sponsors & Partners') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12">{{ __('Backed by the industry.') }}</h2>
        @foreach($event->sponsors->groupBy('tier') as $tier => $sponsors)
            <div class="mb-10">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-4">{{ ucfirst($tier) }}</div>
                <div class="flex flex-wrap gap-4">
                    @foreach($sponsors as $sponsor)
                        <img src="{{ $sponsor->logo_path ?? '/images/placeholder-logo.png' }}" alt="{{ app()->getLocale() === 'ar' ? $sponsor->name_ar : $sponsor->name_en }}" class="h-12 rounded-lg border border-white/10 bg-white/5 px-4 py-2 object-contain">
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endif
