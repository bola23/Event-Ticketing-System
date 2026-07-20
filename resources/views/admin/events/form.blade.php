{{-- resources/views/admin/events/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $event->exists ? __('Edit Event') : __('New Event') }}</h1>
        <form method="POST" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}">
            @csrf
            @if($event->exists) @method('PUT') @endif

            <label>{{ __('Slug') }}</label>
            <input name="slug" value="{{ old('slug', $event->slug) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
            @error('slug') <p class="text-red-500">{{ $message }}</p> @enderror

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $event->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $event->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Tagline (Arabic)') }}</label>
                    <input name="tagline_ar" value="{{ old('tagline_ar', $event->tagline_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                </div>
                <div>
                    <label>{{ __('Tagline (English)') }}</label>
                    <input name="tagline_en" value="{{ old('tagline_en', $event->tagline_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" value="{{ old('start_date', optional($event->start_date)->toDateString()) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('start_date') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('End Date') }}</label>
                    <input type="date" name="end_date" value="{{ old('end_date', optional($event->end_date)->toDateString()) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('end_date') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Venue Name (Arabic)') }}</label>
                    <input name="venue_name_ar" value="{{ old('venue_name_ar', $event->venue_name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                </div>
                <div>
                    <label>{{ __('Venue Name (English)') }}</label>
                    <input name="venue_name_en" value="{{ old('venue_name_en', $event->venue_name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Venue Address (Arabic)') }}</label>
                    <input name="venue_address_ar" value="{{ old('venue_address_ar', $event->venue_address_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                </div>
                <div>
                    <label>{{ __('Venue Address (English)') }}</label>
                    <input name="venue_address_en" value="{{ old('venue_address_en', $event->venue_address_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <label>{{ __('Map Embed URL') }}</label>
            <input name="map_embed_url" value="{{ old('map_embed_url', $event->map_embed_url) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <label>{{ __('Status') }}</label>
            <select name="status" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                <option value="draft" @selected(old('status', $event->status?->value) === 'draft')>{{ __('Draft') }}</option>
                <option value="published" @selected(old('status', $event->status?->value) === 'published')>{{ __('Published') }}</option>
            </select>

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
