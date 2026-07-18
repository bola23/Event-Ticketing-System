<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CCS')</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
