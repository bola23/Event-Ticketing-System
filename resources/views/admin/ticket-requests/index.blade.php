{{-- resources/views/admin/ticket-requests/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <x-admin.page-header :title="__('Ticket Requests').' — '.$event->name_en" />

    @if(session('success'))
        <div class="mb-4 rounded border border-ccs-teal-light/40 bg-ccs-teal-light/10 px-4 py-3 text-sm text-ccs-teal-light" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-400/40 bg-red-400/10 px-4 py-3 text-sm text-red-300" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex gap-2 text-sm">
        @foreach(['pending', 'approved', 'rejected', 'payment_pending', 'all'] as $option)
            <a href="{{ route('admin.events.ticket-requests.index', $event) }}?status={{ $option }}"
               class="px-3 py-1.5 rounded {{ $status === $option ? 'bg-ccs-red text-white' : 'border border-gray-600 text-gray-300' }}">
                {{ ucfirst(str_replace('_', ' ', $option)) }}
            </a>
        @endforeach
    </div>

    @if($tickets->isEmpty())
        <x-admin.empty-state :message="__('No ticket requests yet.')" />
    @else
        <x-admin.table>
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">{{ __('Name') }}</th>
                    <th class="py-2 px-3">{{ __('Email') }}</th>
                    <th class="py-2 px-3">{{ __('Ticket Type') }}</th>
                    <th class="py-2 px-3">{{ __('Status') }}</th>
                    <th class="py-2 px-3">{{ __('Answers') }}</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    <tr class="border-b border-gray-800 align-top">
                        <td class="py-2 px-3">{{ $ticket->name }}</td>
                        <td class="py-2 px-3">{{ $ticket->email }}</td>
                        <td class="py-2 px-3">{{ $ticket->ticketType->name_en }}</td>
                        <td class="py-2 px-3">{{ ucfirst(str_replace('_', ' ', $ticket->status->value)) }}</td>
                        <td class="py-2 px-3">
                            @foreach($ticket->answers as $answer)
                                <div class="text-xs text-gray-400">
                                    {{ $answer->field->label_en }}:
                                    @if($answer->file_path)
                                        <a href="{{ route('admin.events.ticket-requests.answers.download', [$event, $ticket, $answer]) }}" class="text-ccs-teal-light hover:underline">{{ __('Download') }}</a>
                                    @else
                                        {{ $answer->value }}
                                    @endif
                                </div>
                            @endforeach
                        </td>
                        <td class="py-2 px-3 text-right">
                            @if($ticket->status === \App\Enums\TicketStatus::Pending)
                                <form method="POST" action="{{ route('admin.events.ticket-requests.update-status', [$event, $ticket, 'approved']) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <x-admin.button type="submit">{{ __('Approve') }}</x-admin.button>
                                </form>
                                <form method="POST" action="{{ route('admin.events.ticket-requests.update-status', [$event, $ticket, 'rejected']) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
                                    @csrf @method('PATCH')
                                    <x-admin.button type="submit" variant="danger" class="ml-2">{{ __('Reject') }}</x-admin.button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @endif
@endsection
