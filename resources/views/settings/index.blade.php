@extends('layouts.app')

@section('title', 'Pengaturan Website - ' . ($appName ?? 'Sekolah Permata Harapan'))
@section('page_title', 'Pengaturan Website')
@section('page_subtitle', 'Kelola identitas visual dan informasi dasar aplikasi inventaris sekolah.')

@section('content')
@include('settings._nav')

<div class="table-panel fade-in-up">
    <div class="table-panel-header">
        <h3 class="panel-title">Identitas Aplikasi</h3>
    </div>
    
    <div class="auth-panel-body">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="auth-form">
            @csrf
            @method('PUT')

            <div class="auth-grid-2">
                <div class="auth-field">
                    <label class="auth-label" for="app_name">Nama Website</label>
                    <input type="text" name="app_name" id="app_name" class="auth-input" value="{{ old('app_name', $appName) }}" required>
                    <p class="auth-helper-text">Nama utama yang muncul di judul halaman dan brand.</p>
                </div>
                
                <div class="auth-field">
                    <label class="auth-label" for="app_subtitle">Slogan / Subtitle</label>
                    <input type="text" name="app_subtitle" id="app_subtitle" class="auth-input" value="{{ old('app_subtitle', $appSubtitle) }}">
                    <p class="auth-helper-text">Teks kecil di bawah logo.</p>
                </div>
            </div>

            <div class="auth-field" style="margin-top: 10px;">
                <label class="auth-label" for="app_logo">Logo Website</label>
                <div style="display: flex; align-items: flex-start; gap: 20px; flex-wrap: wrap;">
                    <div style="background: var(--bg-base); padding: 15px; border-radius: var(--radius-md); border: 1px dashed var(--border);">
                        @if($logoPath)
                            <img src="{{ asset('storage/' . $logoPath) }}" alt="Current Logo" style="max-height: 80px; width: auto; display: block;">
                        @else
                            <img src="{{ asset('images/logo.png') }}" alt="Default Logo" style="max-height: 80px; width: auto; display: block; opacity: 0.6;">
                        @endif
                    </div>
                    <div style="flex: 1; min-width: 250px;">
                        <input type="file" name="app_logo" id="app_logo" class="auth-input">
                        <p class="auth-helper-text" style="margin-top: 8px;">Format: PNG, JPG, JPEG. Ukuran maks: 2MB. Disarankan gambar dengan latar belakang transparan.</p>
                    </div>
                </div>
            </div>

            <div class="auth-actions" style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
