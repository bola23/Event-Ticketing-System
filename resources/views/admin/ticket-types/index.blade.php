{{-- resources/views/admin/ticket-types/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Ticket Types') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.ticket-types.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Ticket Type') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3">{{ __('Price') }}</th><th class="py-2 px-3">{{ __('Workshop Slots') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($ticketTypes as $ticketType)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $ticketType->name_en }}</td>
                        <td class="py-2 px-3">{{ $ticketType->price }} {{ $ticketType->currency }}</td>
                        <td class="py-2 px-3">{{ $ticketType->workshop_slot_count ?? __('Unlimited') }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.ticket-types.edit', [$event, $ticketType]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.ticket-types.destroy', [$event, $ticketType]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
