{{-- resources/views/components/nice-select.blade.php --}}
@props(['name', 'model', 'options' => [], 'id' => null])

<div x-data="{ open: false }" class="relative">
    <button
        type="button"
        @click="open = !open"
        @click.outside="open = false"
        @keydown.escape="open = false"
        @if($id) id="{{ $id }}" @endif
        class="w-full flex items-center justify-between gap-2 border border-gray-600 bg-gray-900 text-white rounded px-3 py-2 text-left"
        :aria-expanded="open"
        aria-haspopup="listbox"
    >
        <span x-text="({{ json_encode($options, JSON_UNESCAPED_UNICODE) }}.find((option) => String(option.value) === String({{ $model }})) || {}).label ?? ''"></span>
        <svg class="w-4 h-4 shrink-0 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>

    <ul
        x-show="open"
        x-cloak
        x-transition
        role="listbox"
        class="absolute z-50 mt-1 w-full max-h-60 overflow-y-auto bg-gray-900 border border-gray-600 rounded shadow-lg"
    >
        <template x-for="option in {{ json_encode($options, JSON_UNESCAPED_UNICODE) }}" :key="option.value">
            <li
                role="option"
                @click="{{ $model }} = option.value; open = false"
                class="px-3 py-2 cursor-pointer hover:bg-white/10 transition-colors"
                :class="String(option.value) === String({{ $model }}) ? 'bg-white/10 font-bold' : ''"
                :aria-selected="String(option.value) === String({{ $model }})"
                x-text="option.label"
            ></li>
        </template>
    </ul>

    <input type="hidden" name="{{ $name }}" :value="{{ $model }}">
</div>
