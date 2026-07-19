@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en)

@section('content')
    @include('landing.partials.hero', ['event' => $event])
    @include('landing.partials.about', ['event' => $event])
@endsection
