{{-- resources/views/landing/partials/nav.blade.php --}}
@php $sectionBase = ($onLandingPage ?? false) ? '' : route('landing.show', $event); @endphp
<header x-data="{ open: false }" class="fixed top-0 inset-x-0 z-50 flex items-center justify-between gap-4 px-5 md:px-16 py-5 bg-ccs-black/80 backdrop-blur border-b border-white/10">
    <a href="{{ $sectionBase }}#hero" class="font-display font-extrabold text-xl shrink-0">CCS <span class="text-ccs-coral">{{ $event->start_date->format('Y') }}</span></a>

    <nav class="hidden lg:flex items-center gap-6 text-sm font-semibold text-gray-300">
        <a href="{{ $sectionBase }}#about" class="hover:text-white transition-colors">{{ __('About') }}</a>
        <a href="{{ route('agenda.show', $event) }}" class="hover:text-white transition-colors">{{ __('Agenda') }}</a>
        <a href="{{ $sectionBase }}#speakers" class="hover:text-white transition-colors">{{ __('Speakers') }}</a>
        <a href="{{ $sectionBase }}#workshops" class="hover:text-white transition-colors">{{ __('Workshops') }}</a>
        <a href="{{ $sectionBase }}#tickets" class="hover:text-white transition-colors">{{ __('Tickets') }}</a>
        <a href="{{ $sectionBase }}#awards" class="hover:text-white transition-colors">{{ __('Awards') }}</a>
        <a href="{{ $sectionBase }}#partners" class="hover:text-white transition-colors">{{ __('Sponsors') }}</a>
        <a href="{{ $sectionBase }}#faq" class="hover:text-white transition-colors">{{ __('FAQs') }}</a>
    </nav>

    <div class="flex items-center gap-3 shrink-0">
        <div class="hidden sm:flex items-center text-xs font-bold border border-white/20 rounded-md overflow-hidden">
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="px-2.5 py-1.5 transition-colors {{ app()->getLocale() === 'en' ? 'bg-white text-ccs-black' : 'text-gray-400 hover:text-white' }}">EN</a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" class="px-2.5 py-1.5 transition-colors {{ app()->getLocale() === 'ar' ? 'bg-white text-ccs-black' : 'text-gray-400 hover:text-white' }}">AR</a>
        </div>
        <a href="{{ $sectionBase }}#tickets" class="px-5 py-2.5 rounded-md ccs-btn-red text-sm font-bold whitespace-nowrap transition-transform duration-200 hover:scale-[1.03]">{{ __('Request Ticket') }}</a>
        <button type="button" aria-label="{{ __('Menu') }}" class="lg:hidden w-11 h-11 rounded-md border border-white/20 transition-colors hover:bg-white/10" @click="open = !open">&#9776;</button>
    </div>

    <div x-show="open" x-cloak x-transition class="absolute top-full inset-x-0 bg-ccs-black border-b border-white/10 flex flex-col px-5 pb-6 lg:hidden">
        <a href="{{ $sectionBase }}#about" class="py-3.5 border-b border-white/10 font-semibold transition-colors hover:text-ccs-teal-light" @click="open = false">{{ __('About') }}</a>
        <a href="{{ route('agenda.show', $event) }}" class="py-3.5 border-b border-white/10 font-semibold transition-colors hover:text-ccs-teal-light" @click="open = false">{{ __('Agenda') }}</a>
        <a href="{{ $sectionBase }}#speakers" class="py-3.5 border-b border-white/10 font-semibold transition-colors hover:text-ccs-teal-light" @click="open = false">{{ __('Speakers') }}</a>
        <a href="{{ $sectionBase }}#workshops" class="py-3.5 border-b border-white/10 font-semibold transition-colors hover:text-ccs-teal-light" @click="open = false">{{ __('Workshops') }}</a>
        <a href="{{ $sectionBase }}#tickets" class="py-3.5 border-b border-white/10 font-semibold transition-colors hover:text-ccs-teal-light" @click="open = false">{{ __('Tickets') }}</a>
        <a href="{{ $sectionBase }}#awards" class="py-3.5 border-b border-white/10 font-semibold transition-colors hover:text-ccs-teal-light" @click="open = false">{{ __('Awards') }}</a>
        <a href="{{ $sectionBase }}#partners" class="py-3.5 border-b border-white/10 font-semibold transition-colors hover:text-ccs-teal-light" @click="open = false">{{ __('Sponsors') }}</a>
        <a href="{{ $sectionBase }}#faq" class="py-3.5 font-semibold transition-colors hover:text-ccs-teal-light" @click="open = false">{{ __('FAQs') }}</a>
        <div class="sm:hidden flex items-center gap-1 text-xs font-bold border border-white/20 rounded-md overflow-hidden mt-4 w-fit">
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="px-3 py-2 transition-colors {{ app()->getLocale() === 'en' ? 'bg-white text-ccs-black' : 'text-gray-400 hover:text-white' }}">EN</a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" class="px-3 py-2 transition-colors {{ app()->getLocale() === 'ar' ? 'bg-white text-ccs-black' : 'text-gray-400 hover:text-white' }}">AR</a>
        </div>
    </div>
</header>
