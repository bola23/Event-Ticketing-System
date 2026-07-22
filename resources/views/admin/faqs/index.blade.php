{{-- resources/views/admin/faqs/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4">
        <h1>{{ __('FAQs') }} — {{ $event->name_en }}</h1>
        <a href="{{ route('admin.events.faqs.create', $event) }}" class="bg-ccs-red hover:bg-ccs-maroon text-white px-4 py-2 rounded mb-3">{{ __('New FAQ') }}</a>
        <table class="w-full text-left text-white">
            <thead><tr class="border-b border-gray-700"><th class="py-2 px-3">{{ __('Question') }}</th><th class="py-2 px-3"></th></tr></thead>
            <tbody>
                @foreach($faqs as $faq)
                    <tr class="border-b border-gray-700">
                        <td class="py-2 px-3">{{ $faq->question_en }}</td>
                        <td class="py-2 px-3">
                            <a href="{{ route('admin.events.faqs.edit', [$event, $faq]) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.events.faqs.destroy', [$event, $faq]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}')">
                                @csrf @method('DELETE')
                                <button type="submit">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
