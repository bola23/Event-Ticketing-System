{{-- resources/views/agenda/show.blade.php --}}
@extends('layouts.app')

@section('title', __('Agenda'))

@section('content')
    <div class="container mx-auto px-4 py-5">
        <h1>{{ __('Agenda') }}</h1>
        @foreach($itemsByDay as $day => $items)
            <h4 class="mt-4">{{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('l, F j') }}</h4>
            <ul class="divide-y divide-gray-700 mb-4">
                @foreach($items as $item)
                    <li class="py-3 flex justify-between text-white">
                        <span>{{ $item->start_time->format('H:i') }}–{{ $item->end_time->format('H:i') }}</span>
                        <span>{{ app()->getLocale() === 'ar' ? $item->title_ar : $item->title_en }}</span>
                        <span class="inline-block px-2 py-1 text-xs rounded bg-gray-700 text-white">{{ $item->type->value }}</span>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
@endsection
