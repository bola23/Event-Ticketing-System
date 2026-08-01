{{-- resources/views/ticket-requests/create.blade.php --}}
@extends('layouts.app')

@section('title', __('Request a Ticket'))

@section('content')
    <div class="container mx-auto px-4 py-5 max-w-xl">
        <h1 class="font-display text-2xl font-bold mb-6">{{ __('Request Your Ticket') }}</h1>

        @if(session('ticket_request_success'))
            <p class="text-sm font-bold text-ccs-teal-light mb-4">
                {{ __('Request received! Your reference number is :number.', ['number' => session('ticket_request_success')]) }}
            </p>
        @endif

        <form method="POST" action="{{ route('ticket-requests.store', $event) }}" enctype="multipart/form-data" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="ticket_type_id" class="block text-sm text-gray-300 mb-1">{{ __('Ticket Type') }}</label>
                <select id="ticket_type_id" name="ticket_type_id" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                    @foreach($ticketTypes as $ticketType)
                        <option value="{{ $ticketType->id }}" @selected($ticketType->id === $selectedTicketTypeId)>
                            {{ app()->getLocale() === 'ar' ? $ticketType->name_ar : $ticketType->name_en }} — {{ $ticketType->price }} {{ $ticketType->currency }}
                        </option>
                    @endforeach
                </select>
                @error('ticket_type_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-sm text-gray-300 mb-1">{{ __('Name') }}</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm text-gray-300 mb-1">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm text-gray-300 mb-1">{{ __('Phone') }}</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                @error('phone') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            @foreach($requestFields as $field)
                @php $inputKey = 'field_'.$field->id; @endphp
                <div>
                    <label class="block text-sm text-gray-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? $field->label_ar : $field->label_en }}
                        @if($field->is_required)<span class="text-red-400">*</span>@endif
                    </label>

                    @if($field->type === \App\Enums\TicketRequestFieldType::Instagram)
                        <input type="text" name="{{ $inputKey }}" value="{{ old($inputKey) }}" placeholder="@username" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                        @error($inputKey) <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    @elseif($field->type === \App\Enums\TicketRequestFieldType::Portfolio)
                        <div x-data="{ mode: '{{ old($inputKey.'_mode', 'url') }}' }" class="flex flex-col gap-3">
                            <div class="flex gap-4 text-sm">
                                <label class="flex items-center gap-2"><input type="radio" name="{{ $inputKey }}_mode" value="url" x-model="mode"> {{ __('URL') }}</label>
                                <label class="flex items-center gap-2"><input type="radio" name="{{ $inputKey }}_mode" value="pdf" x-model="mode"> {{ __('PDF') }}</label>
                            </div>
                            <input x-show="mode === 'url'" type="url" name="{{ $inputKey }}_url" value="{{ old($inputKey.'_url') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                            <div x-show="mode === 'pdf'" x-cloak>
                                <x-file-dropzone :name="$inputKey.'_file'" accept=".pdf" />
                            </div>
                        </div>
                        @error($inputKey.'_mode') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        @error($inputKey.'_url') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        @error($inputKey.'_file') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    @elseif($field->type === \App\Enums\TicketRequestFieldType::Cv)
                        <x-file-dropzone :name="$inputKey" accept=".pdf,.doc,.docx" />
                        @error($inputKey) <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    @endif
                </div>
            @endforeach

            <button type="submit" class="px-6 py-3 rounded bg-ccs-red hover:bg-ccs-maroon text-white font-bold">{{ __('Submit Request') }}</button>
        </form>
    </div>
@endsection
