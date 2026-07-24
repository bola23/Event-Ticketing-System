{{-- resources/views/landing/partials/gallery.blade.php --}}
@if($event->galleryPhotos->isNotEmpty())
    <section id="gallery" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-coral">{{ __('Gallery') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-10">{{ __('Last year, in frames.') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($event->galleryPhotos as $photo)
                <div class="aspect-square rounded-xl overflow-hidden border border-white/10 bg-white/5">
                    <img src="{{ $photo->image_path }}" alt="{{ app()->getLocale() === 'ar' ? $photo->caption_ar : $photo->caption_en }}" class="w-full h-full object-cover">
                </div>
            @endforeach
        </div>
    </section>
@endif
