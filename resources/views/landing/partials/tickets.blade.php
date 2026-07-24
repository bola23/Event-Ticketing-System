{{-- resources/views/landing/partials/tickets.blade.php --}}
@if($event->ticketTypes->isNotEmpty())
    <section id="tickets" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-red">{{ __('Ticket Request') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12 max-w-2xl">{{ __("There's no checkout. There's a review.") }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($event->ticketTypes->where('is_active', true) as $ticketType)
                @php
                    $slotCount = $ticketType->workshop_slot_count;
                    $slotLabel = is_null($slotCount)
                        ? __('Unlimited workshops')
                        : ($slotCount === 0 ? __('No workshops included') : trans_choice(':count workshop included|:count workshops included', $slotCount, ['count' => $slotCount]));
                @endphp
                <div class="bg-white/5 border border-white/10 rounded-2xl p-8 flex flex-col gap-5">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-3">{{ app()->getLocale() === 'ar' ? $ticketType->name_ar : $ticketType->name_en }}</div>
                        <div class="text-3xl font-extrabold">{{ $ticketType->price }} {{ $ticketType->currency }}</div>
                    </div>
                    <div class="text-sm font-bold text-ccs-gold">{{ $slotLabel }}</div>
                    <p class="text-sm text-gray-400 leading-relaxed flex-1">{{ app()->getLocale() === 'ar' ? $ticketType->description_ar : $ticketType->description_en }}</p>
                    <a href="{{ route('ticket-requests.create', $event) }}?type={{ $ticketType->id }}" class="text-center px-5 py-3.5 rounded-lg bg-gradient-to-br from-ccs-red to-ccs-maroon text-sm font-bold">
                        {{ __('Request This Ticket') }}
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif
