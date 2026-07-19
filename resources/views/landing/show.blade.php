@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en)

@section('content')
    @include('landing.partials.hero', ['event' => $event])
    @include('landing.partials.about', ['event' => $event])
    @include('landing.partials.speakers', ['event' => $event])
    @include('landing.partials.partners', ['event' => $event])
    @include('landing.partials.faq', ['event' => $event])
    @include('landing.partials.location', ['event' => $event])
@endsection
