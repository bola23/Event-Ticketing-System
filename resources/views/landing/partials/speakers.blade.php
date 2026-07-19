{{-- resources/views/landing/partials/speakers.blade.php --}}
@if($event->speakers->isNotEmpty())
    <section id="speakers" class="container mx-auto px-4 py-5">
        <h2>{{ __('Our Speakers') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach($event->speakers as $speaker)
                <div class="mb-4" x-data="{ open: false }">
                    <img src="{{ $speaker->photo_path ?? '/images/placeholder-speaker.png' }}" class="w-full h-auto rounded" alt="">
                    <h5 class="mt-2 font-semibold">{{ app()->getLocale() === 'ar' ? $speaker->name_ar : $speaker->name_en }}</h5>
                    <p class="text-gray-400">{{ app()->getLocale() === 'ar' ? $speaker->title_ar : $speaker->title_en }}</p>
                    <button type="button" class="border border-white text-white px-3 py-1.5 rounded text-sm hover:bg-white hover:text-ccs-black" @click="open = !open">{{ __('Bio') }}</button>
                    <p x-show="open" x-cloak>{{ app()->getLocale() === 'ar' ? $speaker->bio_ar : $speaker->bio_en }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endif
