@props([
    'type' => 'text',
    'name',
    'label' => null,
    'value' => null,
    'dir' => null,
    'placeholder' => null,
    'checked' => false,
    'required' => false,
])

@php $inputClasses = 'w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-ccs-red'; @endphp

@if($type === 'checkbox')
    <div class="flex items-center gap-2 mb-4">
        <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" class="rounded border-gray-600 bg-gray-900" @checked($checked)>
        @if($label)
            <label for="{{ $name }}" class="text-sm text-gray-300">{{ $label }}</label>
        @endif
    </div>
@else
    <div class="mb-4">
        @if($label)
            <label for="{{ $name }}" class="block text-sm text-gray-300 mb-1">{{ $label }}</label>
        @endif

        @if($type === 'textarea')
            <textarea name="{{ $name }}" id="{{ $name }}"
                @if($dir) dir="{{ $dir }}" @endif
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($required) required @endif
                class="{{ $inputClasses }}">{{ $value }}</textarea>
        @elseif($type === 'select')
            <select name="{{ $name }}" id="{{ $name }}" @if($required) required @endif class="{{ $inputClasses }}">
                {{ $slot }}
            </select>
        @else
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}"
                @if($dir) dir="{{ $dir }}" @endif
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($required) required @endif
                class="{{ $inputClasses }}">
        @endif

        @error($name)
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
@endif
