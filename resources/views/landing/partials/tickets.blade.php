{{-- resources/views/landing/partials/tickets.blade.php --}}
@if($event->ticketTypes->isNotEmpty())
    <section id="tickets" class="container mx-auto px-4 py-5">
        <h2>{{ __('Tickets') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach($event->ticketTypes->where('is_active', true) as $ticketType)
                <div class="mb-4">
                    <div class="bg-gray-900 rounded-lg shadow overflow-hidden text-white h-full">
                        <div class="p-4">
                            <h5>{{ app()->getLocale() === 'ar' ? $ticketType->name_ar : $ticketType->name_en }}</h5>
                            <p>{{ $ticketType->price }} {{ $ticketType->currency }}</p>
                            <a href="{{ route('ticket-requests.create', $event) }}?type={{ $ticketType->id }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-3 py-1.5 rounded text-sm">
                                {{ __('Request This Ticket') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
