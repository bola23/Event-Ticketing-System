{{-- resources/views/admin/workshops/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('Workshops') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.workshops.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New Workshop') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3">{{ __('Capacity') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($workshops as $workshop)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $workshop->name_en }}</td>
                        <td class="py-2 px-3">{{ $workshop->capacity }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.workshops.edit', [$event, $workshop]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.workshops.destroy', [$event, $workshop]) }}" class="inline">
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
