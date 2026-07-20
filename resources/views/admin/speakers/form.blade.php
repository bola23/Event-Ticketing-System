{{-- resources/views/admin/speakers/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $speaker->exists ? __('Edit Speaker') : __('New Speaker') }}</h1>
        <form method="POST" action="{{ $speaker->exists ? route('admin.events.speakers.update', [$event, $speaker]) : route('admin.events.speakers.store', $event) }}">
            @csrf
            @if($speaker->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $speaker->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $speaker->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Title (Arabic)') }}</label>
                    <input name="title_ar" value="{{ old('title_ar', $speaker->title_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                </div>
                <div>
                    <label>{{ __('Title (English)') }}</label>
                    <input name="title_en" value="{{ old('title_en', $speaker->title_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Bio (Arabic)') }}</label>
                    <textarea name="bio_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">{{ old('bio_ar', $speaker->bio_ar) }}</textarea>
                </div>
                <div>
                    <label>{{ __('Bio (English)') }}</label>
                    <textarea name="bio_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">{{ old('bio_en', $speaker->bio_en) }}</textarea>
                </div>
            </div>

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $speaker->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
