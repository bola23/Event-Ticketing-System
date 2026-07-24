{{-- resources/views/landing/partials/nav.blade.php --}}
<header x-data="{ open: false }" class="fixed top-0 inset-x-0 z-50 flex items-center justify-between gap-4 px-5 md:px-16 py-5 bg-ccs-black/80 backdrop-blur border-b border-white/10">
    <a href="#hero" class="font-display font-extrabold text-xl shrink-0">CCS <span class="text-ccs-coral">{{ $event->start_date->format('Y') }}</span></a>

    <nav class="hidden lg:flex items-center gap-6 text-sm font-semibold text-gray-300">
        <a href="#about" class="hover:text-white">{{ __('About') }}</a>
        <a href="#agenda-teaser" class="hover:text-white">{{ __('Agenda') }}</a>
        <a href="#speakers" class="hover:text-white">{{ __('Speakers') }}</a>
        <a href="#workshops" class="hover:text-white">{{ __('Workshops') }}</a>
        <a href="#awards" class="hover:text-white">{{ __('Awards') }}</a>
        <a href="#tickets" class="hover:text-white">{{ __('Tickets') }}</a>
        <a href="#partners" class="hover:text-white">{{ __('Sponsors') }}</a>
        <a href="#faq" class="hover:text-white">{{ __('FAQ') }}</a>
    </nav>

    <div class="flex items-center gap-3 shrink-0">
        <a href="#tickets" class="px-5 py-2.5 rounded-md bg-gradient-to-br from-ccs-red to-ccs-maroon text-sm font-bold whitespace-nowrap">{{ __('Request Ticket') }}</a>
        <button type="button" aria-label="{{ __('Menu') }}" class="lg:hidden w-11 h-11 rounded-md border border-white/20" @click="open = !open">&#9776;</button>
    </div>

    <div x-show="open" x-cloak x-transition class="absolute top-full inset-x-0 bg-ccs-black border-b border-white/10 flex flex-col px-5 pb-6 lg:hidden">
        <a href="#about" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('About') }}</a>
        <a href="#agenda-teaser" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Agenda') }}</a>
        <a href="#speakers" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Speakers') }}</a>
        <a href="#workshops" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Workshops') }}</a>
        <a href="#awards" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Awards') }}</a>
        <a href="#tickets" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Tickets') }}</a>
        <a href="#partners" class="py-3.5 border-b border-white/10 font-semibold" @click="open = false">{{ __('Sponsors') }}</a>
        <a href="#faq" class="py-3.5 font-semibold" @click="open = false">{{ __('FAQ') }}</a>
    </div>
</header>
