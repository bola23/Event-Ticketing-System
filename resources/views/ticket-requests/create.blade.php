@extends('layouts.app')

@section('title', __('Request a Ticket'))

@section('content')
    <div class="container mx-auto px-4 py-5">
        <h1>{{ __('Request Your Ticket') }}</h1>
        {{-- Submission handling is a future spec; this form has no action wired up yet. --}}
        <form>
            <label for="ticket_type_id">{{ __('Ticket Type') }}</label>
            <select id="ticket_type_id" name="ticket_type_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @foreach($ticketTypes as $ticketType)
                    <option value="{{ $ticketType->id }}" @selected($ticketType->id === $selectedTicketTypeId)>
                        {{ app()->getLocale() === 'ar' ? $ticketType->name_ar : $ticketType->name_en }} — {{ $ticketType->price }} {{ $ticketType->currency }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endsection
