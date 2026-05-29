@extends('layouts.auth')

@section('title', 'Lupa Password | Inventaris Sekolah')
@section('page_title', 'Lupa Password')
@section('page_subtitle', 'Masukkan email Anda untuk menerima tautan pengaturan ulang password.')
@section('panel_title', 'Reset Password')
@section('panel_subtitle', 'Tautan akan dikirimkan ke email yang terdaftar.')

@section('header_actions')
    <div class="auth-switcher">
        <a class="auth-switcher-link" href="{{ route('login') }}">Login</a>
        <a class="auth-switcher-link" href="{{ route('register') }}">Register</a>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="email">Email</label>
            <input class="auth-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@sekolah.sch.id" required autofocus>
        </div>

        <div class="auth-actions">
            <button class="btn btn-primary auth-submit" type="submit">Kirim Tautan Reset</button>
        </div>

        <p class="auth-form-note">
            Ingat password Anda?
            <a href="{{ route('login') }}">Kembali ke Login</a>
        </p>
    </form>
@endsection
