{{-- resources/views/admin/landing-page-content/edit.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Landing Page Content') }} — {{ $event->name_en }}</h1>
        <form method="POST" action="{{ route('admin.events.content.update', $event) }}">
            @csrf
            @method('PUT')

            <h5 class="mt-4">{{ __('Hero Headline') }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><input name="hero_headline_ar" value="{{ old('hero_headline_ar', $values['hero_headline_ar']) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl" placeholder="{{ __('Arabic') }}"></div>
                <div><input name="hero_headline_en" value="{{ old('hero_headline_en', $values['hero_headline_en']) }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" placeholder="{{ __('English') }}"></div>
            </div>

            <h5 class="mt-4">{{ __('About Body') }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><textarea name="about_body_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl" placeholder="{{ __('Arabic') }}">{{ old('about_body_ar', $values['about_body_ar']) }}</textarea></div>
                <div><textarea name="about_body_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" placeholder="{{ __('English') }}">{{ old('about_body_en', $values['about_body_en']) }}</textarea></div>
            </div>

            <h5 class="mt-4">{{ __('Location Intro') }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><textarea name="location_intro_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl" placeholder="{{ __('Arabic') }}">{{ old('location_intro_ar', $values['location_intro_ar']) }}</textarea></div>
                <div><textarea name="location_intro_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" placeholder="{{ __('English') }}">{{ old('location_intro_en', $values['location_intro_en']) }}</textarea></div>
            </div>

            <h5 class="mt-4">{{ __('Awards Teaser Blurb') }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><textarea name="awards_teaser_blurb_ar" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" dir="rtl" placeholder="{{ __('Arabic') }}">{{ old('awards_teaser_blurb_ar', $values['awards_teaser_blurb_ar']) }}</textarea></div>
                <div><textarea name="awards_teaser_blurb_en" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2" placeholder="{{ __('English') }}">{{ old('awards_teaser_blurb_en', $values['awards_teaser_blurb_en']) }}</textarea></div>
            </div>

            <button type="submit" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-4">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
