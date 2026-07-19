{{-- resources/views/landing/partials/faq.blade.php --}}
@if($event->faqs->isNotEmpty())
    <section id="faq" class="container mx-auto px-4 py-5">
        <h2>{{ __('FAQ') }}</h2>
        <div class="divide-y divide-gray-700">
            @foreach($event->faqs as $faq)
                <div x-data="{ open: false }">
                    <button type="button" class="w-full text-left py-3 font-semibold" @click="open = !open">
                        {{ app()->getLocale() === 'ar' ? $faq->question_ar : $faq->question_en }}
                    </button>
                    <div x-show="open" x-cloak class="pb-3 text-gray-400">
                        {{ app()->getLocale() === 'ar' ? $faq->answer_ar : $faq->answer_en }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
