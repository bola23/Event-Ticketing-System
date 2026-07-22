{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('content')
    <h1>Admin Dashboard</h1>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit">Log out</button>
    </form>
@endsection
