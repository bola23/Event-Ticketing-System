{{-- resources/views/workshops/index.blade.php --}}
@extends('layouts.app')

@section('title', __('Workshops'))

@section('content')
    <div class="container mx-auto px-4 py-5">
        <h1>{{ __('Workshops') }}</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($workshops as $workshop)
                <div class="mb-4">
                    <div class="bg-gray-900 rounded-lg shadow overflow-hidden text-white h-full">
                        <div class="p-4">
                            <h5 class="font-semibold">{{ app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en }}</h5>
                            <p class="text-gray-400 text-sm">{{ Str::limit(app()->getLocale() === 'ar' ? $workshop->description_ar : $workshop->description_en, 100) }}</p>
                            <a href="{{ route('workshops.show', [$event, $workshop]) }}" class="border border-white text-white px-3 py-1.5 rounded text-sm hover:bg-white hover:text-ccs-black">{{ __('Details') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
