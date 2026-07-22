<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('CCS Admin'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ccs-black text-white flex min-h-screen">
    @include('admin.partials.sidebar')
    <main class="flex-1 p-8 overflow-x-hidden">
        @yield('content')
    </main>
</body>
</html>
