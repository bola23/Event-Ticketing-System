<p>{{ __('Hi') }} {{ $ticket->name }},</p>
<p>
    {{ __('Your ticket request for') }}
    {{ app()->getLocale() === 'ar' ? $ticket->event->name_ar : $ticket->event->name_en }}
    {{ __('has been approved.') }}
</p>
<p>{{ __('Reference') }}: {{ $ticket->ticket_number }}</p>
<p>{{ __('Your ticket is currently awaiting payment. Further instructions will follow.') }}</p>