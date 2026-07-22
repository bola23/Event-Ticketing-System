{{-- resources/views/admin/auth/login.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="ccs-hero min-h-screen flex flex-col items-center justify-center gap-8 px-4">
    <div class="text-center text-white">
        <div class="flex items-center justify-center gap-2 mb-2">
            <span class="ccs-flag-accent" style="width:36px;height:48px;"></span>
            <span class="font-display text-4xl font-bold">CCS</span>
        </div>
        <p class="uppercase text-xs tracking-widest opacity-80">{{ __('Content Creators Summit') }}</p>
    </div>

    <div class="bg-ccs-black border border-gray-800 rounded-lg p-8 w-full max-w-sm">
        <h1 class="font-display text-xl font-bold mb-6 text-white">{{ __('Admin Login') }}</h1>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <x-admin.field type="email" name="email" label="{{ __('Email') }}" :value="old('email')" required />
            <x-admin.field type="password" name="password" label="{{ __('Password') }}" required />
            <x-admin.button type="submit" class="w-full">{{ __('Log in') }}</x-admin.button>
        </form>
    </div>
</div>
@endsection
