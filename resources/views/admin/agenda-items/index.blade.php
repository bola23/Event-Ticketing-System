{{-- resources/views/admin/agenda-items/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Agenda Items').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.agenda-items.create', $event) }}">{{ __('New Agenda Item') }}</x-admin.button>
    </x-admin.page-header>

    @if($items->isEmpty())
        <x-admin.empty-state :message="__('No agenda items yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Day') }}</th>
                    <th class="py-2 px-3">{{ __('Time') }}</th>
                    <th class="py-2 px-3">{{ __('Title') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $item->day_date->toDateString() }}</td>
                        <td class="py-2 px-3">{{ $item->start_time->format('H:i') }}–{{ $item->end_time->format('H:i') }}</td>
                        <td class="py-2 px-3">{{ $item->title_en }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.agenda-items.edit', [$event, $item]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.agenda-items.destroy', [$event, $item]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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
