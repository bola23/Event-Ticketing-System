{{-- resources/views/admin/landing-page-content/edit.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Landing Page Content').' — '.$event->name_en" />

    <form method="POST" action="{{ route('admin.events.content.update', $event) }}">
        @csrf
        @method('PUT')

        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('Hero Headline') }}</h2>
        <x-admin.bilingual-field name="hero_headline" :value-ar="old('hero_headline_ar', $values['hero_headline_ar'])" :value-en="old('hero_headline_en', $values['hero_headline_en'])" />

        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('About Body') }}</h2>
        <x-admin.bilingual-field type="textarea" name="about_body" :value-ar="old('about_body_ar', $values['about_body_ar'])" :value-en="old('about_body_en', $values['about_body_en'])" />

        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('Location Intro') }}</h2>
        <x-admin.bilingual-field type="textarea" name="location_intro" :value-ar="old('location_intro_ar', $values['location_intro_ar'])" :value-en="old('location_intro_en', $values['location_intro_en'])" />

        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('Awards Teaser Blurb') }}</h2>
        <x-admin.bilingual-field type="textarea" name="awards_teaser_blurb" :value-ar="old('awards_teaser_blurb_ar', $values['awards_teaser_blurb_ar'])" :value-en="old('awards_teaser_blurb_en', $values['awards_teaser_blurb_en'])" />

        <h2 class="font-display text-lg font-bold mt-6 mb-2">{{ __('Stats') }}</h2>
        <x-admin.bilingual-field name="stats_attendees_count" label="{{ __('Attendees') }}" :value-ar="old('stats_attendees_count_ar', $values['stats_attendees_count_ar'])" :value-en="old('stats_attendees_count_en', $values['stats_attendees_count_en'])" />
        <x-admin.bilingual-field name="stats_countries_count" label="{{ __('Countries') }}" :value-ar="old('stats_countries_count_ar', $values['stats_countries_count_ar'])" :value-en="old('stats_countries_count_en', $values['stats_countries_count_en'])" />

        <x-admin.button type="submit" class="mt-4">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
