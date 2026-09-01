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
                'bronze' => __('Bronze Sponsors'),
                'community' => __('Community Partners'),
            ];
        @endphp

        <div data-sponsor-grid>
            @foreach($event->sponsors->groupBy('tier') as $tier => $sponsors)
                <div class="mb-10">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-4">{{ $tierLabels[$tier] ?? ucfirst($tier) }}</div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach($sponsors as $sponsor)
                            @php
                                $sponsorName = app()->getLocale() === 'ar' ? $sponsor->name_ar : $sponsor->name_en;
                                $logo = $sponsor->logoUrl();
                            @endphp

                            <x-dynamic-component
                                :component="$sponsor->website_url ? 'sponsor-link' : 'sponsor-tile'"
                                :url="$sponsor->website_url"
                            >
                                @if($logo)
                                    <img
                                        src="{{ $logo }}"
                                        alt="{{ $sponsorName }}"
                                        class="max-h-12 w-auto object-contain transition duration-300 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @else
                                    {{-- No logo uploaded yet: the name stands in so the grid stays even. --}}
                                    <span class="font-display text-sm font-bold text-gray-300 text-center px-2">{{ $sponsorName }}</span>
                                @endif
                            </x-dynamic-component>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
