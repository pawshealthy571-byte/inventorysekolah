@extends('layouts.app')

@section('title', 'Manajemen Akses - ' . ($appName ?? 'Sekolah Permata Harapan'))
@section('page_title', 'Manajemen Akses')
@section('page_subtitle', 'Atur hak akses setiap peran (role) dalam menggunakan fitur aplikasi.')

@section('content')
@include('settings._nav')

<div class="table-panel fade-in-up">
    <div class="table-panel-header">
        <div>
            <h3 class="panel-title">Matriks Hak Akses</h3>
            <p class="panel-subtitle">Tentukan fitur mana saja yang dapat diakses oleh Admin dan Pengguna.</p>
        </div>
    </div>

    <div class="auth-panel-body">
        <form method="POST" action="{{ route('settings.access.update') }}">
            @csrf
            @method('PUT')

            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fitur / Modul</th>
                            @foreach ($accessRoles as $role)
                                <th style="text-align: center;">{{ $roleLabels[$role] }}</th>
                            @endforeach
                            <th style="text-align: center;">Superadmin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissionDefinitions as $permission => $definition)
                            <tr>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <strong style="color: var(--text-main);">{{ $definition['label'] }}</strong>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $definition['description'] }}</span>
                                    </div>
                                </td>
                                @foreach ($accessRoles as $role)
                                    <td style="text-align: center;">
                                        <label class="auth-checkbox" style="justify-content: center;">
                                            <input
                                                type="checkbox"
                                                name="permissions[{{ $role }}][]"
                                                value="{{ $permission }}"
                                                @checked($rolePermissionMatrix[$role][$permission] ?? false)
                                            >
                                        </label>
                                    </td>
                                @endforeach
                                <td style="text-align: center;">
                                    <span class="badge badge-success" style="opacity: 0.7;">Selalu Aktif</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="auth-actions" style="margin-top: 24px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button class="btn btn-primary" type="submit">Simpan Matriks Akses</button>
            </div>
        </form>
    </div>
</div>
@endsection
