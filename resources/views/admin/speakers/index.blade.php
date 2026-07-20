{{-- resources/views/admin/speakers/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Speakers') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.speakers.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Speaker') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($speakers as $speaker)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $speaker->name_en }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.speakers.edit', [$event, $speaker]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.speakers.destroy', [$event, $speaker]) }}" class="inline">
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
