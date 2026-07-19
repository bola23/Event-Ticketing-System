{{-- resources/views/workshops/show.blade.php --}}
@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en)

@section('content')
    <div class="container mx-auto px-4 py-5">
        <a href="{{ route('workshops.index', $event) }}">&larr; {{ __('All Workshops') }}</a>
        <h1>{{ app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en }}</h1>
        <p>{{ app()->getLocale() === 'ar' ? $workshop->description_ar : $workshop->description_en }}</p>
        <p><strong>{{ __('Capacity') }}:</strong> {{ $workshop->capacity }}</p>
        @if($workshop->speaker)
            <p><strong>{{ __('Speaker') }}:</strong> {{ app()->getLocale() === 'ar' ? $workshop->speaker->name_ar : $workshop->speaker->name_en }}</p>
        @endif
    </div>
@endsection
