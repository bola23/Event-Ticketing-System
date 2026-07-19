{{-- resources/views/admin/auth/login.blade.php --}}
@extends('layouts.app')

@section('content')
    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>

        @error('email')
            <p>{{ $message }}</p>
        @enderror

        <button type="submit">Log in</button>
    </form>
@endsection
