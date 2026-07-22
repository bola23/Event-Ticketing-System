@props([
    'href' => null,
    'type' => 'submit',
    'variant' => 'primary',
])

@php
$variantClasses = match($variant) {
    'primary' => 'bg-ccs-red hover:bg-ccs-maroon text-white',
    'secondary' => 'border border-gray-600 text-white hover:bg-gray-900',
    'danger' => 'text-red-500 hover:underline',
    default => 'bg-ccs-red hover:bg-ccs-maroon text-white',
};
$baseClasses = $variant === 'danger' ? $variantClasses : "$variantClasses px-4 py-2 rounded";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses . ' inline-block']) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>{{ $slot }}</button>
@endif
