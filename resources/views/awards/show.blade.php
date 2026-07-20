{{-- resources/views/awards/show.blade.php --}}
@extends('layouts.app')

@section('title', __('Awards'))

@section('content')
    <div class="container mx-auto px-4 py-5 text-center">
        <h1>{{ __('Awards') }}</h1>
        @if($blurb)
            <p>{{ app()->getLocale() === 'ar' ? $blurb->value_ar : $blurb->value_en }}</p>
        @endif
        <p class="text-gray-400">{{ __('Voting details coming soon.') }}</p>
    </div>
@endsection
