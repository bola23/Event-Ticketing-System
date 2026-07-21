{{-- resources/views/admin/agenda-items/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $item->exists ? __('Edit Agenda Item') : __('New Agenda Item') }}</h1>
        <form method="POST" action="{{ $item->exists ? route('admin.events.agenda-items.update', [$event, $item]) : route('admin.events.agenda-items.store', $event) }}">
            @csrf
            @if($item->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label>{{ __('Day') }}</label>
                    <input type="date" name="day_date" value="{{ old('day_date', optional($item->day_date)->toDateString()) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('day_date') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Start Time') }}</label>
                    <input type="time" name="start_time" value="{{ old('start_time', optional($item->start_time)->format('H:i')) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('start_time') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('End Time') }}</label>
                    <input type="time" name="end_time" value="{{ old('end_time', optional($item->end_time)->format('H:i')) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('end_time') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Title (Arabic)') }}</label>
                    <input name="title_ar" value="{{ old('title_ar', $item->title_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('title_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Title (English)') }}</label>
                    <input name="title_en" value="{{ old('title_en', $item->title_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('title_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <label>{{ __('Type') }}</label>
            <select name="type" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @foreach($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $item->type?->value) === $type->value)>{{ ucfirst($type->value) }}</option>
                @endforeach
            </select>

            <label>{{ __('Speaker') }}</label>
            <select name="speaker_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                <option value="">{{ __('None') }}</option>
                @foreach($speakers as $speaker)
                    <option value="{{ $speaker->id }}" @selected(old('speaker_id', $item->speaker_id) === $speaker->id)>{{ $speaker->name_en }}</option>
                @endforeach
            </select>

            <label>{{ __('Workshop') }}</label>
            <select name="workshop_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                <option value="">{{ __('None') }}</option>
                @foreach($workshops as $workshop)
                    <option value="{{ $workshop->id }}" @selected(old('workshop_id', $item->workshop_id) === $workshop->id)>{{ $workshop->name_en }}</option>
                @endforeach
            </select>

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
