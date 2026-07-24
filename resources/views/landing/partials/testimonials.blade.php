{{-- resources/views/landing/partials/testimonials.blade.php --}}
@if($event->testimonials->isNotEmpty())
    <section id="testimonials" class="ccs-section">
        <div class="ccs-eyebrow text-ccs-gold">{{ __('Testimonials') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12">{{ __('What past attendees say.') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-7">
            @foreach($event->testimonials as $testimonial)
                <div class="bg-white/5 border border-white/10 rounded-2xl p-8 flex flex-col gap-6">
                    <p class="text-lg leading-relaxed font-medium">&ldquo;{{ app()->getLocale() === 'ar' ? $testimonial->quote_ar : $testimonial->quote_en }}&rdquo;</p>
                    <div>
                        <div class="font-bold text-sm">{{ app()->getLocale() === 'ar' ? $testimonial->name_ar : $testimonial->name_en }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ app()->getLocale() === 'ar' ? $testimonial->title_ar : $testimonial->title_en }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
