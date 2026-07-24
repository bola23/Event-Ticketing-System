@extends('layouts.app')

@php
    $heroHeadline = $event->contentFor(\App\Enums\LandingPageSection::Hero, 'headline');
    $pageTitle = app()->getLocale() === 'ar'
        ? ($heroHeadline?->value_ar ?: $event->name_ar)
        : ($heroHeadline?->value_en ?: $event->name_en);
@endphp

@section('title', $pageTitle)

@section('content')
    @include('landing.partials.nav', ['event' => $event])
    @include('landing.partials.hero', ['event' => $event])
    @include('landing.partials.about', ['event' => $event])
    @include('landing.partials.speakers', ['event' => $event])
    @include('landing.partials.workshops-teaser', ['event' => $event])
    @include('landing.partials.agenda-teaser', ['event' => $event])
    @include('landing.partials.tickets', ['event' => $event])
    @include('landing.partials.awards-teaser', ['event' => $event])
    @include('landing.partials.partners', ['event' => $event])
    @include('landing.partials.faq', ['event' => $event])
    @include('landing.partials.location', ['event' => $event])
    @include('landing.partials.footer', ['event' => $event])
@endsection
