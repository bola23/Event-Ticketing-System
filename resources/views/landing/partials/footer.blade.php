{{-- resources/views/landing/partials/footer.blade.php --}}
@php
    $heroHeadline = $event->contentFor(\App\Enums\LandingPageSection::Hero, 'headline');
@endphp
<footer class="ccs-section pt-24">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-16">
        <div>
            <div class="font-display font-extrabold text-xl mb-4">CCS <span class="text-ccs-coral">{{ $event->start_date->format('Y') }}</span></div>
            @unless($heroHeadline)
                <p class="text-sm text-gray-500 max-w-[220px]">{{ app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en }}</p>
            @endunless
        </div>
        <div class="flex flex-col gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">{{ __('Event') }}</span>
            <a href="#about" class="text-sm text-gray-400 hover:text-white">{{ __('About') }}</a>
            <a href="#agenda-teaser" class="text-sm text-gray-400 hover:text-white">{{ __('Agenda') }}</a>
            <a href="#speakers" class="text-sm text-gray-400 hover:text-white">{{ __('Speakers') }}</a>
            <a href="#workshops" class="text-sm text-gray-400 hover:text-white">{{ __('Workshops') }}</a>
        </div>
        <div class="flex flex-col gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">{{ __('Program') }}</span>
            <a href="#awards" class="text-sm text-gray-400 hover:text-white">{{ __('Awards') }}</a>
            <a href="#partners" class="text-sm text-gray-400 hover:text-white">{{ __('Sponsors') }}</a>
            <a href="#tickets" class="text-sm text-gray-400 hover:text-white">{{ __('Tickets') }}</a>
            <a href="#faq" class="text-sm text-gray-400 hover:text-white">{{ __('FAQs') }}</a>
        </div>
        <div class="flex flex-col gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">{{ __('Connect') }}</span>
            <a href="#" class="text-sm text-gray-400 hover:text-white">Instagram</a>
            <a href="#" class="text-sm text-gray-400 hover:text-white">LinkedIn</a>
            <a href="#" class="text-sm text-gray-400 hover:text-white">YouTube</a>
        </div>
    </div>
    <div class="flex flex-wrap justify-between items-center gap-4 pt-8 border-t border-white/10 text-xs text-gray-500">
        <span>&copy; {{ $event->start_date->format('Y') }}@unless($heroHeadline) {{ app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en }}.@endunless {{ __('All rights reserved.') }}</span>
    </div>
</footer>
