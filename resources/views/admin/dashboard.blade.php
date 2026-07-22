{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Dashboard')" />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-900 rounded-lg p-6">
            <p class="font-display text-3xl font-bold">{{ $totalEvents }}</p>
            <p class="text-gray-400 text-sm mt-1">{{ __('Total Events') }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-6">
            <p class="font-display text-3xl font-bold text-ccs-teal-light">{{ $publishedEvents }}</p>
            <p class="text-gray-400 text-sm mt-1">{{ __('Published') }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-6">
            <p class="font-display text-3xl font-bold text-gray-400">{{ $draftEvents }}</p>
            <p class="text-gray-400 text-sm mt-1">{{ __('Draft') }}</p>
        </div>
    </div>

    <div class="mt-6">
        <x-admin.button href="{{ route('admin.events.index') }}">{{ __('View Events') }}</x-admin.button>
    </div>
@endsection
