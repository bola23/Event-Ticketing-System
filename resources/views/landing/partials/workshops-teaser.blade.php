{{-- resources/views/landing/partials/workshops-teaser.blade.php --}}
@if($event->workshops->isNotEmpty())
    <section id="workshops" class="container mx-auto px-4 py-5">
        <h2>{{ __('Workshops') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($event->workshops->take(3) as $workshop)
                <div class="mb-3">
                    <div class="bg-gray-900 rounded-lg shadow overflow-hidden text-white h-full">
                        <div class="p-4">
                            <h5>{{ app()->getLocale() === 'ar' ? $workshop->name_ar : $workshop->name_en }}</h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <a href="{{ route('workshops.index', $event) }}" class="border border-white text-white px-4 py-2 rounded hover:bg-white hover:text-ccs-black">{{ __('See All Workshops') }}</a>
    </section>
@endif
