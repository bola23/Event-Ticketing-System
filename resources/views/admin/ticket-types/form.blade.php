{{-- resources/views/admin/ticket-types/form.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="$ticketType->exists ? __('Edit Ticket Type') : __('New Ticket Type')" />

    <form method="POST" action="{{ $ticketType->exists ? route('admin.events.ticket-types.update', [$event, $ticketType]) : route('admin.events.ticket-types.store', $event) }}">
        @csrf
        @if($ticketType->exists) @method('PUT') @endif

        <x-admin.bilingual-field name="name" label="{{ __('Name') }}" :value-ar="old('name_ar', $ticketType->name_ar)" :value-en="old('name_en', $ticketType->name_en)" />
        <x-admin.bilingual-field type="textarea" name="description" label="{{ __('Description') }}" :value-ar="old('description_ar', $ticketType->description_ar)" :value-en="old('description_en', $ticketType->description_en)" />
        <x-admin.bilingual-field
            type="textarea"
            name="features"
            label="{{ __('Features (one per line)') }}"
            :value-ar="old('features_ar', $ticketType->exists ? $ticketType->features->pluck('text_ar')->implode(\"\n\") : null)"
            :value-en="old('features_en', $ticketType->exists ? $ticketType->features->pluck('text_en')->implode(\"\n\") : null)"
        />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-admin.field type="number" name="price" label="{{ __('Price') }}" :value="old('price', $ticketType->price)" />
            <x-admin.field name="currency" label="{{ __('Currency') }}" :value="old('currency', $ticketType->currency ?? 'EGP')" />
            <x-admin.field type="number" name="workshop_slot_count" label="{{ __('Workshop Slots (blank = unlimited)') }}" :value="old('workshop_slot_count', $ticketType->workshop_slot_count)" />
        </div>

        <x-admin.field type="checkbox" name="is_active" label="{{ __('Active') }}" :checked="old('is_active', $ticketType->is_active ?? true)" />
        <x-admin.field type="number" name="sort_order" label="{{ __('Sort Order') }}" :value="old('sort_order', $ticketType->sort_order ?? 0)" />

        <x-admin.button type="submit">{{ __('Save') }}</x-admin.button>
    </form>
@endsection
