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
                        <p id="logo-warning" style="color: #ef4444; display: none; margin-top: 4px; font-size: 0.85rem; font-weight: 600;">⚠️ Ukuran file terlalu besar! Maksimal 2MB.</p>
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

@push('scripts')
<script>
    document.getElementById('app_logo').addEventListener('change', function() {
        const file = this.files[0];
        const warning = document.getElementById('logo-warning');
        if (file && file.size > 2 * 1024 * 1024) {
            warning.style.display = 'block';
            alert('Peringatan: Ukuran logo yang Anda pilih (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB) melebihi batas maksimal 2MB. Silakan pilih file yang lebih kecil.');
            this.value = ''; // Reset the input
            warning.style.display = 'none';
        } else {
            warning.style.display = 'none';
        }
    });
</script>
@endpush
