{{-- resources/views/admin/request-form-fields/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Request Form').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.request-form-fields.create', $event) }}">{{ __('New Request Field') }}</x-admin.button>
    </x-admin.page-header>

    @if($requestFields->isEmpty())
        <x-admin.empty-state :message="__('No request fields yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Type') }}</th>
                    <th class="py-2 px-3">{{ __('Label') }}</th>
                    <th class="py-2 px-3">{{ __('Required') }}</th>
                    <th class="py-2 px-3">{{ __('Sort Order') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($requestFields as $requestField)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ ucfirst($requestField->type->value) }}</td>
                        <td class="py-2 px-3">{{ $requestField->label_en }}</td>
                        <td class="py-2 px-3">{{ $requestField->is_required ? __('Yes') : __('No') }}</td>
                        <td class="py-2 px-3">{{ $requestField->sort_order }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.request-form-fields.edit', [$event, $requestField]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.request-form-fields.destroy', [$event, $requestField]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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
