{{-- resources/views/admin/contact-messages/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Contact Messages').' — '.$event->name_en" />

    @if($contactMessages->isEmpty())
        <x-admin.empty-state :message="__('No messages yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Name') }}</th>
                    <th class="py-2 px-3">{{ __('Email') }}</th>
                    <th class="py-2 px-3">{{ __('Message') }}</th>
                    <th class="py-2 px-3">{{ __('Received') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contactMessages as $message)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">{{ $message->name }}</td>
                        <td class="py-2 px-3">{{ $message->email }}</td>
                        <td class="py-2 px-3">{{ \Illuminate\Support\Str::limit($message->message, 80) }}</td>
                        <td class="py-2 px-3">{{ $message->created_at->format('M j, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @endif
@endsection
