@extends('layouts.auth')

@section('title', 'Register | Inventaris Sekolah')
@section('page_title', 'Buat Akun Baru')
@section('page_subtitle', 'Daftarkan akun baru agar tim bisa langsung masuk ke dashboard inventaris dengan tampilan yang sama rapi.')
@section('panel_title', 'Register')
@section('panel_subtitle', 'Lengkapi data dasar pengguna untuk mulai memakai sistem.')

@section('header_actions')
    <div class="auth-switcher">
        <a class="auth-switcher-link" href="{{ route('login') }}">Login</a>
        <a class="auth-switcher-link active" href="{{ route('register') }}">Register</a>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="auth-form">
        @csrf

        <div class="auth-grid-2">
            <div class="auth-field">
                <label class="auth-label" for="name">Nama</label>
                <input class="auth-input" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Nama lengkap" required autofocus>
            </div>

            <div class="auth-field">
                <label class="auth-label" for="email">Email</label>
                <input class="auth-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@sekolah.sch.id" required>
            </div>
        </div>

        <div class="auth-grid-2">
            <div class="auth-field">
                <label class="auth-label" for="password">Password</label>
                <input class="auth-input" id="password" name="password" type="password" placeholder="Minimal 8 karakter" required>
            </div>

            <div class="auth-field">
                <label class="auth-label" for="password_confirmation">Konfirmasi Password</label>
                <input class="auth-input" id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password" required>
            </div>
        </div>

        <div class="auth-actions">
            <button class="btn btn-primary auth-submit" type="submit">Buat Akun</button>
        </div>

        <p class="auth-form-note">
            Sudah punya akun?
            <a href="{{ route('login') }}">Masuk di sini</a>
        </p>
    </form>
@endsection
