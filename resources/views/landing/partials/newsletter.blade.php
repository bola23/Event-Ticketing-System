{{-- resources/views/landing/partials/newsletter.blade.php --}}
<section id="newsletter" class="ccs-section text-center pt-24 pb-24 border-t border-b border-white/10" style="background: linear-gradient(160deg, var(--color-ccs-maroon), var(--color-ccs-black));">
    <h2 class="font-display text-2xl md:text-4xl font-extrabold mb-4">{{ __('Stay in the loop.') }}</h2>
    <p class="text-gray-400 mb-8">{{ __('Speaker announcements, agenda updates, and workshop drops — no spam.') }}</p>
    @if(session('newsletter_success'))
        <p class="text-sm font-bold text-ccs-teal-light mb-4">{{ __("You're subscribed — thanks!") }}</p>
    @endif
    <form method="POST" action="{{ route('newsletter.store', $event) }}" class="flex flex-wrap gap-3 justify-center max-w-md mx-auto">
        @csrf
        <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('you@company.com') }}" class="flex-1 min-w-[220px] px-4 py-4 bg-white/10 border border-white/10 rounded-lg text-white placeholder-gray-500">
        <button type="submit" class="px-7 py-4 rounded-lg bg-white text-ccs-black font-bold">{{ __('Subscribe') }}</button>
    </form>
    @error('email') <p class="text-red-300 text-sm mt-3">{{ $message }}</p> @enderror
</section>
