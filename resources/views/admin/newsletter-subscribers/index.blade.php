{{-- resources/views/admin/newsletter-subscribers/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Newsletter Subscribers').' — '.$event->name_en" />

    @if($newsletterSubscribers->isEmpty())
        <x-admin.empty-state :message="__('No subscribers yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Email') }}</th>
                    <th class="py-2 px-3">{{ __('Subscribed') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($newsletterSubscribers as $subscriber)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $subscriber->email }}</td>
                        <td class="py-2 px-3">{{ $subscriber->created_at->format('M j, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @endif
@endsection
