{{-- resources/views/admin/faqs/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ $faq->exists ? __('Edit FAQ') : __('New FAQ') }}</h1>
        <form method="POST" action="{{ $faq->exists ? route('admin.events.faqs.update', [$event, $faq]) : route('admin.events.faqs.store', $event) }}">
            @csrf
            @if($faq->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Question (Arabic)') }}</label>
                    <input name="question_ar" value="{{ old('question_ar', $faq->question_ar) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">
                    @error('question_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Question (English)') }}</label>
                    <input name="question_en" value="{{ old('question_en', $faq->question_en) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @error('question_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>{{ __('Answer (Arabic)') }}</label>
                    <textarea name="answer_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl">{{ old('answer_ar', $faq->answer_ar) }}</textarea>
                    @error('answer_ar') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>{{ __('Answer (English)') }}</label>
                    <textarea name="answer_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">{{ old('answer_en', $faq->answer_en) }}</textarea>
                    @error('answer_en') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <label>{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
