{{-- resources/views/admin/events/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Events') }}</h1>
        <a href="{{ route('admin.events.create') }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Event') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3">{{ __('Status') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($events as $event)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $event->name_en }}</td>
                        <td class="py-2 px-3">{{ $event->status->value }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.edit', $event) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline">
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
