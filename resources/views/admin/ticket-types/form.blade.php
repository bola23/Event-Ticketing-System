{{-- resources/views/admin/ticket-types/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $ticketType->exists ? __('Edit Ticket Type') : __('New Ticket Type') }}</h1>
        <form method="POST" action="{{ $ticketType->exists ? route('admin.events.ticket-types.update', [$event, $ticketType]) : route('admin.events.ticket-types.store', $event) }}">
            @csrf
            @if($ticketType->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $ticketType->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $ticketType->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Description (Arabic)') }}</label>
                    <textarea name="description_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">{{ old('description_ar', $ticketType->description_ar) }}</textarea>
                </div>
                <div>
                    <label>{{ __('Description (English)') }}</label>
                    <textarea name="description_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">{{ old('description_en', $ticketType->description_en) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label>{{ __('Price') }}</label>
                    <input type="number" name="price" value="{{ old('price', $ticketType->price) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('price') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Currency') }}</label>
                    <input name="currency" value="{{ old('currency', $ticketType->currency ?? 'SAR') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" maxlength="3">
                </div>
                <div>
                    <label>{{ __('Workshop Slots (blank = unlimited)') }}</label>
                    <input type="number" name="workshop_slot_count" value="{{ old('workshop_slot_count', $ticketType->workshop_slot_count) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded" id="is_active" @checked(old('is_active', $ticketType->is_active ?? true))>
                <label for="is_active">{{ __('Active') }}</label>
            </div>

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $ticketType->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
