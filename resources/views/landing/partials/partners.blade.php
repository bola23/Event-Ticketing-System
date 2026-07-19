{{-- resources/views/landing/partials/partners.blade.php --}}
@if($event->sponsors->isNotEmpty())
    <section id="partners" class="container mx-auto px-4 py-5">
        <h2>{{ __('Partners') }}</h2>
        @foreach($event->sponsors->groupBy('tier') as $tier => $sponsors)
            <h6 class="uppercase text-gray-400">{{ $tier }}</h6>
            <div class="flex flex-wrap gap-4 mb-4">
                @foreach($sponsors as $sponsor)
                    <img src="{{ $sponsor->logo_path ?? '/images/placeholder-logo.png' }}" alt="{{ $sponsor->name_en }}" style="height:48px;">
                @endforeach
            </div>
        @endforeach
    </section>
@endif
