{{-- resources/views/landing/partials/contact.blade.php --}}
<section id="contact" class="ccs-section grid grid-cols-1 lg:grid-cols-2 gap-16">
    <div>
        <div class="ccs-eyebrow text-ccs-coral">{{ __('Contact') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-8">{{ __('Questions before you request?') }}</h2>
    </div>
    <form method="POST" action="{{ route('contact.store', $event) }}" class="flex flex-col gap-4">
        @csrf
        @if(session('contact_success'))
            <p class="text-sm font-bold text-ccs-teal-light">{{ __("Thanks — we'll be in touch soon.") }}</p>
        @endif
        <div>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('Name') }}" class="w-full px-4 py-4 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500">
            @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}" class="w-full px-4 py-4 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500">
            @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <textarea name="message" rows="4" placeholder="{{ __('Message') }}" class="w-full px-4 py-4 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500">{{ old('message') }}</textarea>
            @error('message') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="px-6 py-4 rounded-lg bg-gradient-to-br from-ccs-red to-ccs-maroon font-bold">{{ __('Send Message') }}</button>
    </form>
</section>
