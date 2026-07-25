{{-- resources/views/landing/partials/partners.blade.php --}}
@if($event->sponsors->isNotEmpty() && $event->isSectionVisible('partners'))
    <section id="partners" class="ccs-section scroll-mt-24">
        <div class="ccs-eyebrow text-ccs-teal-light" data-reveal>{{ __('Sponsors & Partners') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12" data-reveal>{{ __('Backed by the industry.') }}</h2>
        @php
            $tierLabels = [
                'platinum' => __('Platinum Sponsors'),
                'gold' => __('Gold Sponsors'),
                'silver' => __('Silver Sponsors'),
                'community' => __('Community Partners'),
            ];
        @endphp
        @foreach($event->sponsors->groupBy('tier') as $tier => $sponsors)
            <div class="mb-10" data-reveal>
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-4">{{ $tierLabels[$tier] ?? ucfirst($tier) }}</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($sponsors as $sponsor)
                        <img src="{{ $sponsor->logo_path ?? '/images/placeholder-logo.png' }}" alt="{{ app()->getLocale() === 'ar' ? $sponsor->name_ar : $sponsor->name_en }}" class="h-20 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 object-contain transition-opacity hover:opacity-80" loading="lazy">
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endif
