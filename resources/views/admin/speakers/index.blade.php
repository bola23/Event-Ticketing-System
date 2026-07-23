{{-- resources/views/admin/speakers/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Speakers').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.speakers.create', $event) }}">{{ __('New Speaker') }}</x-admin.button>
    </x-admin.page-header>

    @if($speakers->isEmpty())
        <x-admin.empty-state :message="__('No speakers yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Name') }}</th><th class="py-2 px-3"></th></tr>
            </thead>
            <tbody>
                @foreach($speakers as $speaker)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $speaker->name_en }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.speakers.edit', [$event, $speaker]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.speakers.destroy', [$event, $speaker]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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
