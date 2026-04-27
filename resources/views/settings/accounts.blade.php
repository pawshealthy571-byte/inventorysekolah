@extends('layouts.app')

@section('title', 'Manajemen Akun - ' . ($appName ?? 'Sekolah Permata Harapan'))
@section('page_title', 'Manajemen Akun')
@section('page_subtitle')
    Kelola akun pengguna yang dapat mengakses sistem inventaris sekolah.
@endsection

@section('page_actions')
    <a class="btn btn-secondary" href="#tambah-akun">Tambah Akun Baru</a>
@endsection

@section('content')
@include('settings._nav')

    @php
        $totalManagedCount = $allManagedUsers->count();
        $filteredCount = $managedUsers->count();
        $managedAdminCount = $allManagedUsers->where('role', \App\Models\User::ROLE_ADMIN)->count();
        $managedUserCount = $allManagedUsers->where('role', \App\Models\User::ROLE_USER)->count();
    @endphp

    <div class="table-panel fade-in-up" id="tambah-akun" style="margin-bottom: 24px;">
        <div class="table-panel-header">
            <h3 class="panel-title">Buat Akun Baru</h3>
        </div>
        <div class="auth-panel-body">
            <form method="POST" action="{{ route('settings.accounts.store') }}" class="auth-form">
                @csrf
                <div class="auth-grid-2">
                    <div class="auth-field">
                        <label class="auth-label" for="managed_name">Nama Lengkap</label>
                        <input class="auth-input" id="managed_name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required>
                    </div>
                    <div class="auth-field">
                        <label class="auth-label" for="managed_email">Alamat Email</label>
                        <input class="auth-input" id="managed_email" name="email" type="email" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div class="auth-grid-2">
                    <div class="auth-field">
                        <label class="auth-label" for="managed_role">Peran / Role</label>
                        <select class="auth-input" id="managed_role" name="role" required>
                            @foreach ($manageableRoles as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>{{ $roleLabels[$role] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="auth-field">
                        <label class="auth-label" for="managed_password">Password Awal</label>
                        <input class="auth-input" id="managed_password" name="password" type="password" required>
                    </div>
                </div>
                <div class="auth-field">
                    <label class="auth-label" for="managed_password_confirmation">Konfirmasi Password</label>
                    <input class="auth-input" id="managed_password_confirmation" name="password_confirmation" type="password" required>
                </div>
                <div class="auth-actions" style="margin-top: 10px;">
                    <button class="btn btn-primary" type="submit">Buat Akun</button>
                </div>
            </form>
        </div>
    </div>

    <div class="stats-container fade-in-up" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-label">Total Akun</div>
            <div class="stat-value">{{ number_format($totalManagedCount, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Admin</div>
            <div class="stat-value">{{ number_format($managedAdminCount, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pengguna</div>
            <div class="stat-value">{{ number_format($managedUserCount, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="table-panel fade-in-up">
        <div class="table-panel-header">
            <h3 class="panel-title">Daftar Akun Terdaftar</h3>
            <form method="GET" action="{{ route('settings.accounts') }}" style="display: flex; gap: 8px;">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="auth-input" placeholder="Cari nama/email..." style="padding: 6px 12px; font-size: 0.85rem; width: 200px;">
                <button type="submit" class="btn btn-secondary" style="padding: 6px 12px;">Filter</button>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama & Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($managedUsers as $managedAccount)
                        @php($isEditing = $selectedManagedUser && $selectedManagedUser->id === $managedAccount->id)
                        <tr class="{{ $isEditing ? 'active' : '' }}">
                            <td>
                                <div class="item-cell">
                                    <div class="item-icon">{{ $managedAccount->initials() }}</div>
                                    <div class="item-info">
                                        <strong>{{ $managedAccount->name }}</strong>
                                        <span>{{ $managedAccount->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $managedAccount->roleLabel() }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $isEditing ? 'badge-danger' : 'badge-success' }}">
                                    {{ $isEditing ? 'Sedang Diedit' : 'Aktif' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a class="btn btn-secondary" href="{{ route('settings.accounts', ['edit' => $managedAccount->id]) }}#editor-akun">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($selectedManagedUser)
        <div class="table-panel fade-in-up" id="editor-akun" style="margin-top: 24px; border-color: var(--primary-border);">
            <div class="table-panel-header" style="background: var(--primary-soft);">
                <h3 class="panel-title">Edit Akun: {{ $selectedManagedUser->name }}</h3>
                <a href="{{ route('settings.accounts') }}" class="btn btn-secondary">Batal</a>
            </div>
            <div class="auth-panel-body">
                <form method="POST" action="{{ route('settings.accounts.update', $selectedManagedUser) }}" class="auth-form">
                    @csrf
                    @method('PUT')
                    <div class="auth-grid-2">
                        <div class="auth-field">
                            <label class="auth-label">Nama</label>
                            <input class="auth-input" name="name" type="text" value="{{ $selectedManagedUser->name }}" required>
                        </div>
                        <div class="auth-field">
                            <label class="auth-label">Email</label>
                            <input class="auth-input" name="email" type="email" value="{{ $selectedManagedUser->email }}" required>
                        </div>
                    </div>
                    <div class="auth-grid-2">
                        <div class="auth-field">
                            <label class="auth-label">Role</label>
                            <select class="auth-input" name="role" required>
                                @foreach ($manageableRoles as $role)
                                    <option value="{{ $role }}" @selected($selectedManagedUser->role === $role)>{{ $roleLabels[$role] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="auth-field">
                            <label class="auth-label">Reset Password (Opsional)</label>
                            <input class="auth-input" name="password" type="password" placeholder="Kosongkan jika tidak diubah">
                        </div>
                    </div>
                    <div class="auth-actions" style="margin-top: 20px;">
                        <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
