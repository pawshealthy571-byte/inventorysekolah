@extends('layouts.auth')

@section('title', 'Login | Inventaris Sekolah')
@section('page_title', 'Masuk ke Dashboard')
@section('page_subtitle', 'Gunakan akun Anda untuk membuka dashboard inventaris sekolah dan melanjutkan pekerjaan operasional.')
@section('panel_title', 'Login')
@section('panel_subtitle', 'Masukkan email dan password akun yang sudah terdaftar.')

@section('header_actions')
    <div class="auth-switcher">
        <a class="auth-switcher-link active" href="{{ route('login') }}">Login</a>
        <a class="auth-switcher-link" href="{{ route('register') }}">Register</a>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="email">Email</label>
            <input class="auth-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@sekolah.sch.id" required autofocus>
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">Password</label>
            <input class="auth-input" id="password" name="password" type="password" placeholder="Masukkan password" required>
        </div>

        <div class="auth-inline-row">
            <label class="auth-checkbox" for="remember">
                <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span>Ingat saya di perangkat ini</span>
            </label>
            <span class="auth-helper-text">Akses cepat ke ringkasan stok dan mutasi.</span>
        </div>

        <div class="auth-actions">
            <button class="btn btn-primary auth-submit" type="submit">Masuk ke Dashboard</button>
        </div>

        <p class="auth-form-note">
            Belum punya akun?
            <a href="{{ route('register') }}">Buat akun baru</a>
        </p>
    </form>
@endsection
