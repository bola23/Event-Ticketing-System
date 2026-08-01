{{-- resources/views/emails/ticket-requests/rejected.blade.php --}}
<p>{{ __('Hi') }} {{ $ticket->name }},</p>
<p>
    {{ __('Thank you for your interest in') }}
    {{ app()->getLocale() === 'ar' ? $ticket->event->name_ar : $ticket->event->name_en }}.
    {{ __("Unfortunately, we're unable to approve your ticket request at this time.") }}
</p>
<p>{{ __('Reference') }}: {{ $ticket->ticket_number }}</p>
