{{-- resources/views/landing/partials/footer.blade.php --}}
@php $sectionBase = ($onLandingPage ?? false) ? '' : route('landing.show', $event); @endphp
@if($event->isSectionVisible('newsletter'))
<footer id="newsletter" class="scroll-mt-24 w-full px-[clamp(20px,6vw,80px)] pb-[100px] pt-24 border-t border-white/10" style="background: linear-gradient(160deg, var(--color-ccs-maroon), var(--color-ccs-black));">
    <div class="text-center pb-16 mb-16 border-b border-white/10">
        <h2 class="font-display text-2xl md:text-4xl font-extrabold mb-4" data-reveal>{{ __('Stay in the loop.') }}</h2>
        <p class="text-gray-300 mb-8" data-reveal>{{ __('Speaker announcements, agenda updates, and workshop drops — no spam.') }}</p>
        @if(session('newsletter_success'))
            <p class="text-sm font-bold text-ccs-teal-light mb-4">{{ __("You're subscribed — thanks!") }}</p>
        @endif
        <form method="POST" action="{{ route('newsletter.store', $event) }}" class="flex flex-wrap gap-3 justify-center max-w-md mx-auto" data-reveal data-reveal-delay="1">
            @csrf
            <label for="newsletter-email" class="sr-only">{{ __('Email') }}</label>
            <input id="newsletter-email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('you@company.com') }}" class="flex-1 min-w-[220px] px-4 py-4 bg-white/10 border border-white/10 rounded-lg text-white placeholder-gray-500 transition-colors focus:border-white focus:outline-none">
            <button type="submit" class="px-7 py-4 rounded-lg bg-white text-ccs-black font-bold transition-transform duration-200 hover:scale-[1.03]">{{ __('Subscribe') }}</button>
            @error('email') <p class="text-red-300 text-sm mt-3 w-full">{{ $message }}</p> @enderror
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-16">
        <div>
            <div class="font-display font-extrabold text-xl mb-4">CCS <span class="text-ccs-coral">{{ $event->start_date->format('Y') }}</span></div>
            <p class="text-sm text-gray-400 max-w-[220px]">{{ app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en }}</p>
        </div>
        <div class="flex flex-col gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-1">{{ __('Event') }}</span>
            <a href="{{ $sectionBase }}#about" class="text-sm text-gray-300 hover:text-white transition-colors">{{ __('About') }}</a>
            <a href="{{ route('agenda.show', $event) }}" class="text-sm text-gray-300 hover:text-white transition-colors">{{ __('Agenda') }}</a>
            <a href="{{ $sectionBase }}#speakers" class="text-sm text-gray-300 hover:text-white transition-colors">{{ __('Speakers') }}</a>
            <a href="{{ $sectionBase }}#workshops" class="text-sm text-gray-300 hover:text-white transition-colors">{{ __('Workshops') }}</a>
        </div>
        <div class="flex flex-col gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-1">{{ __('Program') }}</span>
            <a href="{{ $sectionBase }}#awards" class="text-sm text-gray-300 hover:text-white transition-colors">{{ __('Awards') }}</a>
            <a href="{{ $sectionBase }}#partners" class="text-sm text-gray-300 hover:text-white transition-colors">{{ __('Sponsors') }}</a>
            <a href="{{ $sectionBase }}#tickets" class="text-sm text-gray-300 hover:text-white transition-colors">{{ __('Tickets') }}</a>
            <a href="{{ $sectionBase }}#faq" class="text-sm text-gray-300 hover:text-white transition-colors">{{ __('FAQs') }}</a>
        </div>
        <div class="flex flex-col gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-1">{{ __('Connect') }}</span>
            <a href="#" class="text-sm text-gray-300 hover:text-white transition-colors">Instagram</a>
            <a href="#" class="text-sm text-gray-300 hover:text-white transition-colors">LinkedIn</a>
            <a href="#" class="text-sm text-gray-300 hover:text-white transition-colors">YouTube</a>
        </div>
    </div>
    <div class="flex flex-wrap justify-between items-center gap-4 pt-8 border-t border-white/10 text-xs text-gray-400">
        <span>&copy; {{ $event->start_date->format('Y') }} {{ app()->getLocale() === 'ar' ? $event->name_ar : $event->name_en }}. {{ __('All rights reserved.') }}</span>
    </div>
</footer>
@endif
