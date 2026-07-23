{{-- resources/views/admin/sponsors/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$sponsor->exists ? __('Edit Sponsor') : __('New Sponsor')" />

    <form method="POST" action="{{ $sponsor->exists ? route('admin.events.sponsors.update', [$event, $sponsor]) : route('admin.events.sponsors.store', $event) }}">
        @csrf
        @if($sponsor->exists) @method('PUT') @endif

        <x-admin.bilingual-field name="name" label="{{ __('Name') }}" :value-ar="old('name_ar', $sponsor->name_ar)" :value-en="old('name_en', $sponsor->name_en)" />

        <x-admin.field type="select" name="tier" label="{{ __('Tier') }}">
            @foreach(['platinum', 'gold', 'silver', 'bronze'] as $tier)
                <option value="{{ $tier }}" @selected(old('tier', $sponsor->tier) === $tier)>{{ ucfirst($tier) }}</option>
            @endforeach
        </x-admin.field>

        <x-admin.field name="website_url" label="{{ __('Website URL') }}" :value="old('website_url', $sponsor->website_url)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $sponsor->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
