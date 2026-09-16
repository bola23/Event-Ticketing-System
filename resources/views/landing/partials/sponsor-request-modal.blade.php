{{-- resources/views/landing/partials/sponsor-request-modal.blade.php --}}
<div
    x-data
    x-show="$store.sponsorRequest.open"
    x-cloak
    x-init="if (@js($errors->any() && old('_form') === 'sponsor-request')) { $store.sponsorRequest.open = true; }"
    @keydown.escape.window="$store.sponsorRequest.open = false"
    class="fixed inset-0 z-100"
>
    <div
        class="fixed inset-0 bg-black/70"
        @click="$store.sponsorRequest.open = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <div class="fixed inset-0 overflow-y-auto p-4" @click.self="$store.sponsorRequest.open = false">
        <div class="flex min-h-full items-center justify-center" @click.self="$store.sponsorRequest.open = false">
            <div
                class="relative bg-ccs-black border border-white/10 rounded-2xl max-w-xl w-full p-6 md:p-8 my-8"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-display text-xl font-bold">{{ __('Become a Sponsor') }}</h2>
                    <button type="button" @click="$store.sponsorRequest.open = false" class="text-gray-400 hover:text-white text-2xl leading-none transition-colors" aria-label="{{ __('Close') }}">&times;</button>
                </div>

                @if(session('sponsor_request_success'))
                    <p class="text-sm font-bold text-ccs-teal-light mb-4">{{ __("Thanks — we'll review your request and be in touch soon.") }}</p>
                @endif

                <form method="POST" action="{{ route('sponsor-requests.store', $event) }}" enctype="multipart/form-data" class="flex flex-col gap-4">
                    @csrf
                    <input type="hidden" name="_form" value="sponsor-request">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="sponsor-name-en" class="block text-sm text-gray-300 mb-1">{{ __('Company Name (English)') }}</label>
                            <input id="sponsor-name-en" type="text" name="name_en" value="{{ old('name_en') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                            @error('name_en') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="sponsor-name-ar" class="block text-sm text-gray-300 mb-1">{{ __('Company Name (Arabic)') }}</label>
                            <input id="sponsor-name-ar" type="text" name="name_ar" dir="rtl" value="{{ old('name_ar') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                            @error('name_ar') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="sponsor-contact-name" class="block text-sm text-gray-300 mb-1">{{ __('Contact Name') }}</label>
                        <input id="sponsor-contact-name" type="text" name="contact_name" value="{{ old('contact_name') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                        @error('contact_name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="sponsor-email" class="block text-sm text-gray-300 mb-1">{{ __('Email') }}</label>
                            <input id="sponsor-email" type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                            @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="sponsor-phone" class="block text-sm text-gray-300 mb-1">{{ __('Phone') }}</label>
                            <input id="sponsor-phone" type="tel" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                            @error('phone') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <x-file-dropzone name="logo" accept="image/*" :label="__('Logo')" />
                    @error('logo') <p class="text-red-400 text-sm -mt-2">{{ $message }}</p> @enderror

                    <div>
                        <label for="sponsor-website" class="block text-sm text-gray-300 mb-1">{{ __('Website URL') }} <span class="text-gray-500">({{ __('optional') }})</span></label>
                        <input id="sponsor-website" type="url" name="website_url" value="{{ old('website_url') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                        @error('website_url') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="sponsor-instagram" class="block text-sm text-gray-300 mb-1">{{ __('Instagram URL') }} <span class="text-gray-500">({{ __('optional') }})</span></label>
                            <input id="sponsor-instagram" type="url" name="instagram_url" value="{{ old('instagram_url') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                            @error('instagram_url') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="sponsor-facebook" class="block text-sm text-gray-300 mb-1">{{ __('Facebook URL') }} <span class="text-gray-500">({{ __('optional') }})</span></label>
                            <input id="sponsor-facebook" type="url" name="facebook_url" value="{{ old('facebook_url') }}" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">
                            @error('facebook_url') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="sponsor-message" class="block text-sm text-gray-300 mb-1">{{ __('Message') }} <span class="text-gray-500">({{ __('optional') }})</span></label>
                        <textarea id="sponsor-message" name="message" rows="3" class="w-full border border-gray-600 bg-gray-900 text-white rounded px-3 py-2">{{ old('message') }}</textarea>
                        @error('message') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="px-6 py-3 rounded bg-ccs-red hover:bg-ccs-maroon text-white font-bold transition-opacity">{{ __('Submit Request') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
