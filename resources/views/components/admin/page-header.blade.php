{{-- resources/views/components/admin/page-header.blade.php --}}
@props(['title'])

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <span class="ccs-flag-accent" style="width:8px;height:28px;"></span>
        <h1 class="font-display text-2xl font-bold">{{ $title }}</h1>
    </div>
    @if($slot->isNotEmpty())
        <div>{{ $slot }}</div>
    @endif
</div>
