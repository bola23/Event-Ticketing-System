{{-- resources/views/landing/partials/tickets.blade.php --}}
@if($event->ticketTypes->isNotEmpty() && $event->isSectionVisible('tickets'))
    <section id="tickets" class="ccs-section scroll-mt-24">
        <div class="max-w-[680px] mb-16">
            <div class="ccs-eyebrow text-ccs-gold" data-reveal>{{ __('Ticket Request') }}</div>
            <h2 class="font-display text-[clamp(32px,3.6vw,45px)] font-extrabold mb-[18px]" data-reveal>{{ __("There's no checkout. There's a review.") }}</h2>
            <p class="text-[17px] text-gray-400 leading-[1.6]" data-reveal>{{ __('CCS is invitation-considerate, not first-come. Submit a request; our team reviews it before any payment happens.') }}</p>
        </div>

        <div class="relative grid grid-cols-6 gap-2 mb-[90px]" data-reveal>
            <div class="absolute top-[23px] left-[8%] right-[8%] h-px bg-white/10 z-0"></div>
            @foreach([
                ['label' => __('Request Ticket'), 'state' => 'active'],
                ['label' => __('Admin Review'), 'state' => 'pending'],
                ['label' => __('Approval'), 'state' => 'pending'],
                ['label' => __('Payment Link'), 'state' => 'pending'],
                ['label' => __('Ticket Generated'), 'state' => 'pending'],
                ['label' => __('QR Code Issued'), 'state' => 'done'],
            ] as $index => $step)
                <div class="relative z-10 flex flex-col items-center text-center gap-3.5">
                    <div class="w-[46px] h-[46px] shrink-0 rounded-full flex items-center justify-center font-extrabold {{ $step['state'] === 'active' ? 'bg-[#241014] border border-white/10 text-white' : ($step['state'] === 'done' ? 'bg-ccs-teal-light text-ccs-black' : 'bg-ccs-maroon border border-white/10 text-white') }}">
                        {{ $index + 1 }}
                    </div>
                    <span class="text-[13px] font-semibold text-gray-400">{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($event->ticketTypes->where('is_active', true) as $ticketType)
                @php
                    $slotCount = $ticketType->workshop_slot_count;
                    $slotLabel = is_null($slotCount)
                        ? __('Unlimited workshops')
                        : ($slotCount === 0 ? __('No workshops included') : trans_choice(':count workshop included|:count workshops included', $slotCount, ['count' => $slotCount]));
                @endphp
                <div data-reveal data-reveal-delay="{{ min($loop->iteration, 5) }}">
                    <div class="h-full bg-white/[0.03] border border-white/10 rounded-2xl py-9 px-[30px] flex flex-col gap-[22px] transition-transform duration-300 hover:-translate-y-1">
                        <div>
                            <div class="text-[13px] font-bold uppercase tracking-[0.1em] text-gray-500 mb-[14px]">{{ app()->getLocale() === 'ar' ? $ticketType->name_ar : $ticketType->name_en }}</div>
                            <div class="text-[38px] font-extrabold">{{ $ticketType->price }} {{ $ticketType->currency }}</div>
                        </div>
                        <div class="text-sm font-bold text-ccs-gold">{{ $slotLabel }}</div>
                        @if($ticketType->features->isNotEmpty())
                            <div class="flex flex-col gap-3 flex-1">
                                @foreach($ticketType->features as $feature)
                                    <div class="flex gap-2.5 items-start text-sm text-gray-400">
                                        <span class="text-ccs-teal-light shrink-0">&mdash;</span>
                                        <span>{{ app()->getLocale() === 'ar' ? $feature->text_ar : $feature->text_en }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <a href="{{ route('ticket-requests.create', $event) }}?type={{ $ticketType->id }}" class="text-center p-[14px] rounded-lg ccs-btn-red text-sm font-bold transition-transform duration-200 hover:scale-[1.03]">
                            {{ __('Request This Ticket') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
