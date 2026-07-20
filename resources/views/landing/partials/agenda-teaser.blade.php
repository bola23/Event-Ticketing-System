{{-- resources/views/landing/partials/agenda-teaser.blade.php --}}
@if($event->agendaItems->isNotEmpty())
    <section id="agenda-teaser" class="container mx-auto px-4 py-5">
        <h2>{{ __('Agenda') }}</h2>
        <a href="{{ route('agenda.show', $event) }}" class="border border-white text-white px-4 py-2 rounded hover:bg-white hover:text-ccs-black">{{ __('View Full Agenda') }}</a>
    </section>
@endif
