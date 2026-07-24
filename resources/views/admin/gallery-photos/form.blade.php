{{-- resources/views/admin/gallery-photos/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$galleryPhoto->exists ? __('Edit Photo') : __('New Photo')" />

    <form method="POST" action="{{ $galleryPhoto->exists ? route('admin.events.gallery-photos.update', [$event, $galleryPhoto]) : route('admin.events.gallery-photos.store', $event) }}">
        @csrf
        @if($galleryPhoto->exists) @method('PUT') @endif

        <x-admin.field name="image_path" label="{{ __('Image Path / URL') }}" :value="old('image_path', $galleryPhoto->image_path)" required />
        <x-admin.bilingual-field name="caption" label="{{ __('Caption') }}" :value-ar="old('caption_ar', $galleryPhoto->caption_ar)" :value-en="old('caption_en', $galleryPhoto->caption_en)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $galleryPhoto->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
