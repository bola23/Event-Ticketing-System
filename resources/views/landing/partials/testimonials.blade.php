{{-- resources/views/landing/partials/testimonials.blade.php --}}
@if($event->testimonials->isNotEmpty() && $event->isSectionVisible('testimonials'))
    <section id="testimonials" class="ccs-section scroll-mt-24">
        <div class="ccs-eyebrow text-ccs-gold" data-reveal>{{ __('Testimonials') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-12" data-reveal>{{ __('What past attendees say.') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-7">
            @foreach($event->testimonials as $testimonial)
                <div data-reveal data-reveal-delay="{{ min($loop->iteration, 5) }}">
                    <div class="h-full bg-white/5 border border-white/10 rounded-2xl p-8 flex flex-col gap-6 transition-transform duration-300 hover:-translate-y-1">
                        <p class="text-lg leading-relaxed font-medium">&ldquo;{{ app()->getLocale() === 'ar' ? $testimonial->quote_ar : $testimonial->quote_en }}&rdquo;</p>
                        <div>
                            <div class="font-bold text-sm">{{ app()->getLocale() === 'ar' ? $testimonial->name_ar : $testimonial->name_en }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ app()->getLocale() === 'ar' ? $testimonial->title_ar : $testimonial->title_en }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
