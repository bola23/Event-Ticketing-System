{{-- resources/views/admin/agenda-items/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Agenda Items') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.agenda-items.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Agenda Item') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Day') }}</th><th class="py-2 px-3">{{ __('Time') }}</th><th class="py-2 px-3">{{ __('Title') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($items as $item)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $item->day_date->toDateString() }}</td>
                        <td class="py-2 px-3">{{ $item->start_time->format('H:i') }}–{{ $item->end_time->format('H:i') }}</td>
                        <td class="py-2 px-3">{{ $item->title_en }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.agenda-items.edit', [$event, $item]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.agenda-items.destroy', [$event, $item]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
