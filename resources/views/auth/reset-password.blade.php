@extends('layouts.auth')

@section('title', 'Atur Ulang Password | Inventaris Sekolah')
@section('page_title', 'Atur Ulang Password')
@section('page_subtitle', 'Silakan masukkan password baru Anda di bawah ini.')
@section('panel_title', 'Password Baru')
@section('panel_subtitle', 'Pastikan password Anda kuat dan mudah diingat.')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-field">
            <label class="auth-label" for="email">Email</label>
            <input class="auth-input" id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required readonly>
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">Password Baru</label>
            <input class="auth-input" id="password" name="password" type="password" placeholder="Masukkan password baru" required autofocus>
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password_confirmation">Konfirmasi Password Baru</label>
            <input class="auth-input" id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password baru" required>
        </div>

        <div class="auth-actions">
            <button class="btn btn-primary auth-submit" type="submit">Atur Ulang Password</button>
        </div>
    </form>
@endsection
