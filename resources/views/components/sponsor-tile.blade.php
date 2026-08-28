{{-- resources/views/components/sponsor-tile.blade.php --}}
@props(['url' => null])

<div {{ $attributes->merge(['class' => 'group flex h-24 items-center justify-center rounded-lg border border-white/10 bg-white/5 px-5 py-4 transition-colors hover:border-ccs-coral/50']) }} data-sponsor-logo>
    {{ $slot }}
</div>
