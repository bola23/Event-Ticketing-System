{{-- resources/views/admin/sponsors/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $sponsor->exists ? __('Edit Sponsor') : __('New Sponsor') }}</h1>
        <form method="POST" action="{{ $sponsor->exists ? route('admin.events.sponsors.update', [$event, $sponsor]) : route('admin.events.sponsors.store', $event) }}">
            @csrf
            @if($sponsor->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $sponsor->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $sponsor->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <label>{{ __('Tier') }}</label>
            <select name="tier" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @foreach(['platinum', 'gold', 'silver', 'bronze'] as $tier)
                    <option value="{{ $tier }}" @selected(old('tier', $sponsor->tier) === $tier)>{{ ucfirst($tier) }}</option>
                @endforeach
            </select>
            @error('tier') <p class="text-red-500">{{ $message }}</p> @enderror

            <label>{{ __('Website URL') }}</label>
            <input name="website_url" value="{{ old('website_url', $sponsor->website_url) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $sponsor->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
