{{-- resources/views/admin/workshops/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $workshop->exists ? __('Edit Workshop') : __('New Workshop') }}</h1>
        <form method="POST" action="{{ $workshop->exists ? route('admin.events.workshops.update', [$event, $workshop]) : route('admin.events.workshops.store', $event) }}">
            @csrf
            @if($workshop->exists) @method('PUT') @endif

            <label>{{ __('Slug') }}</label>
            <input name="slug" value="{{ old('slug', $workshop->slug) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
            @error('slug') <p class="text-red-500">{{ $message }}</p> @enderror

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Name (Arabic)') }}</label>
                    <input name="name_ar" value="{{ old('name_ar', $workshop->name_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('name_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Name (English)') }}</label>
                    <input name="name_en" value="{{ old('name_en', $workshop->name_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('name_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Description (Arabic)') }}</label>
                    <textarea name="description_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">{{ old('description_ar', $workshop->description_ar) }}</textarea>
                </div>
                <div>
                    <label>{{ __('Description (English)') }}</label>
                    <textarea name="description_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">{{ old('description_en', $workshop->description_en) }}</textarea>
                </div>
            </div>

            <label>{{ __('Speaker') }}</label>
            <select name="speaker_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                <option value="">{{ __('None') }}</option>
                @foreach($speakers as $speaker)
                    <option value="{{ $speaker->id }}" @selected(old('speaker_id', $workshop->speaker_id) === $speaker->id)>{{ $speaker->name_en }}</option>
                @endforeach
            </select>

            <label>{{ __('Capacity') }}</label>
            <input type="number" name="capacity" value="{{ old('capacity', $workshop->capacity ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
            @error('capacity') <p class="text-red-500">{{ $message }}</p> @enderror

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $workshop->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
