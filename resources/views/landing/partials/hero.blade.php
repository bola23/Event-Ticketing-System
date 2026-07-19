{{-- resources/views/landing/partials/hero.blade.php --}}
<section id="hero" class="ccs-hero flex flex-col items-center justify-center text-center text-white" style="min-height: 70vh;">
    <p class="uppercase text-sm">CCS &middot; {{ $event->name_ar }}</p>
    <h1 class="text-5xl font-bold">{{ app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en }}</h1>
    <p class="text-lg" x-data="{ now: Date.now(), target: new Date('{{ $event->start_date->toDateString() }}').getTime() }"
       x-init="setInterval(() => now = Date.now(), 1000)">
        <span x-text="Math.max(0, Math.floor((target - now) / 86400000))"></span>
        {{ __('days to go') }}
    </p>
    <p>{{ app()->getLocale() === 'ar' ? $event->venue_name_ar : $event->venue_name_en }}</p>
    <a href="#tickets" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mt-3">{{ __('Request Your Ticket') }}</a>
</section>
