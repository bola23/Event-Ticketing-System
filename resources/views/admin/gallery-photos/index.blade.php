{{-- resources/views/admin/gallery-photos/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Gallery Photos').' — '.$event->name_en">
        <x-admin.button href="{{ route('admin.events.gallery-photos.create', $event) }}">{{ __('New Photo') }}</x-admin.button>
    </x-admin.page-header>

    @if($galleryPhotos->isEmpty())
        <x-admin.empty-state :message="__('No gallery photos yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Photo') }}</th>
                    <th class="py-2 px-3">{{ __('Caption') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($galleryPhotos as $photo)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3"><img src="{{ $photo->image_path }}" class="h-12 w-12 object-cover rounded" alt=""></td>
                        <td class="py-2 px-3">{{ $photo->caption_en }}</td>
                        <td class="py-2 px-3 text-right">
                            <a href="{{ route('admin.events.gallery-photos.edit', [$event, $photo]) }}" class="text-ccs-teal-light hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.gallery-photos.destroy', [$event, $photo]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
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
