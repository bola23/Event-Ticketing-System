{{-- resources/views/landing/partials/faq.blade.php --}}
@if($event->faqs->isNotEmpty())
    <section id="faq" class="ccs-section max-w-3xl">
        <div class="ccs-eyebrow text-ccs-red">{{ __('FAQs') }}</div>
        <h2 class="font-display text-3xl md:text-5xl font-extrabold mb-10">{{ __('Good questions.') }}</h2>
        <div class="flex flex-col">
            @foreach($event->faqs as $faq)
                <div x-data="{ open: false }" class="border-b border-white/10">
                    <button type="button" class="w-full flex justify-between items-center gap-5 py-6 text-left font-display font-bold text-lg" @click="open = !open">
                        <span>{{ app()->getLocale() === 'ar' ? $faq->question_ar : $faq->question_en }}</span>
                        <span class="text-2xl text-gray-500" x-text="open ? '&#8211;' : '+'"></span>
                    </button>
                    <p x-show="open" x-cloak class="pb-6 text-gray-400 leading-relaxed max-w-xl">{{ app()->getLocale() === 'ar' ? $faq->answer_ar : $faq->answer_en }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endif
