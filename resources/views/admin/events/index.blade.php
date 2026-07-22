{{-- resources/views/admin/events/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Events')">
        <x-admin.button href="{{ route('admin.events.create') }}">{{ __('New Event') }}</x-admin.button>
    </x-admin.page-header>

    @if($events->isEmpty())
        <x-admin.empty-state :message="__('No events yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Name') }}</th>
                    <th class="py-2 px-3">{{ __('Status') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $event->name_en }}</td>
                        <td class="py-2 px-3">{{ $event->status->value }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.edit', $event) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
                                @csrf @method('DELETE')
                                <x-admin.button type="submit" variant="danger" class="ml-2">{{ __('Delete') }}</x-admin.button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @endif
@endsection
