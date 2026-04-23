@extends('layouts.app')

@section('title', 'Akun Management')
@section('page_title', 'Akun Management')
@section('page_subtitle', 'Kelola akun yang bisa dibuat dan diubah oleh {{ strtolower($user->roleLabel()) }} lewat tampilan tabel yang lebih rapi.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('profile.show') }}">Profil Saya</a>
    <a class="button" href="#tambah-akun">Tambah Akun</a>
@endsection

@section('content')
    @php
        $totalManagedCount = $allManagedUsers->count();
        $filteredCount = $managedUsers->count();
        $managedAdminCount = $allManagedUsers->where('role', \App\Models\User::ROLE_ADMIN)->count();
        $managedUserCount = $allManagedUsers->where('role', \App\Models\User::ROLE_USER)->count();
    @endphp

    <section class="panel section-card" id="tambah-akun" style="margin-bottom: 18px;">
        <div class="section-header">
            <div>
                <div class="muted">Tambah akun</div>
                <h3 class="section-title">Buat Akun Baru</h3>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.accounts.store') }}">
            @csrf

            <div class="form-grid">
                <div class="field">
                    <label for="managed_name">Nama</label>
                    <input class="input" id="managed_name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required>
                </div>

                <div class="field">
                    <label for="managed_email">Email</label>
                    <input class="input" id="managed_email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="field">
                    <label for="managed_role">Role</label>
                    <select class="select" id="managed_role" name="role" required>
                        @foreach ($manageableRoles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>{{ $roleLabels[$role] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="managed_password">Password Awal</label>
                    <input class="input" id="managed_password" name="password" type="password" required>
                </div>

                <div class="field-wide">
                    <label for="managed_password_confirmation">Konfirmasi Password Awal</label>
                    <input class="input" id="managed_password_confirmation" name="password_confirmation" type="password" required>
                    <small>Akun akan langsung aktif setelah dibuat.</small>
                </div>
            </div>

            <div class="button-row" style="margin-top: 18px;">
                <button class="button" type="submit">Simpan Akun</button>
            </div>
        </form>
    </section>

    <section class="panel section-card" style="margin-bottom: 18px;">
        <div class="section-header">
            <div>
                <div class="muted">Filter pencarian</div>
                <h3 class="section-title">Cari dan Kelompokkan Akun</h3>
            </div>
        </div>

        <form class="filter-form" method="GET" action="{{ route('profile.accounts.show') }}">
            <div class="field">
                <label for="q">Nama atau Email</label>
                <input class="input" id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: nicolas atau admin@sekolah.test">
            </div>

            <div class="field">
                <label for="role">Role</label>
                <select class="select" id="role" name="role">
                    <option value="">Semua role</option>
                    @foreach ($manageableRoles as $role)
                        <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ $roleLabels[$role] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="button-row">
                <button class="button" type="submit">Terapkan</button>
                <a class="button-ghost" href="{{ route('profile.accounts.show') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="stats-grid" style="margin-bottom: 18px;">
        <article class="panel stat-card">
            <span class="muted">Akun tampil</span>
            <strong>{{ number_format($filteredCount, 0, ',', '.') }}</strong>
            <p>Jumlah akun setelah filter diterapkan.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Total akun</span>
            <strong>{{ number_format($totalManagedCount, 0, ',', '.') }}</strong>
            <p>Total seluruh akun yang bisa Anda kelola.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Role admin</span>
            <strong>{{ number_format($managedAdminCount, 0, ',', '.') }}</strong>
            <p>Jumlah akun admin pada ruang kelola aktif.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Role pengguna</span>
            <strong>{{ number_format($managedUserCount, 0, ',', '.') }}</strong>
            <p>Jumlah akun pengguna biasa yang tersedia.</p>
        </article>
    </section>

    <section class="panel section-card" style="margin-bottom: 18px;">
        <div class="section-header">
            <div>
                <div class="muted">Data akun</div>
                <h3 class="section-title">Tabel Akun</h3>
            </div>
            <span class="badge badge-accent">{{ number_format($filteredCount, 0, ',', '.') }} akun</span>
        </div>

        @if ($managedUsers->isEmpty())
            <div class="empty-state">Tidak ada akun yang cocok dengan filter saat ini.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Akun</th>
                            <th>Role</th>
                            <th>Dibuat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($managedUsers as $managedAccount)
                            <tr>
                                <td>
                                    <strong>{{ $managedAccount->name }}</strong>
                                    <div class="meta">
                                        <span>{{ $managedAccount->email }}</span>
                                        @if ($managedAccount->id === $user->id)
                                            <span>Akun Anda</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-accent">{{ $managedAccount->roleLabel() }}</span>
                                </td>
                                <td>
                                    <div>{{ $managedAccount->created_at?->format('d/m/Y') ?? '-' }}</div>
                                    <div class="meta" style="margin-top: 4px;">
                                        <span>{{ $managedAccount->created_at?->format('H:i') ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-success">Aktif</span>
                                </td>
                                <td>
                                    <div class="inline-actions">
                                        <a
                                            class="button-secondary"
                                            href="{{ route('profile.accounts.show', array_filter([
                                                'q' => $filters['q'] ?? null,
                                                'role' => $filters['role'] ?? null,
                                                'edit' => $managedAccount->id,
                                            ])) }}"
                                        >
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($selectedManagedUser)
        <section class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Editor akun</div>
                    <h3 class="section-title">Edit {{ $selectedManagedUser->name }}</h3>
                </div>
                <span class="badge badge-warning">{{ $selectedManagedUser->roleLabel() }}</span>
            </div>

            <form method="POST" action="{{ route('profile.accounts.update', $selectedManagedUser) }}">
                @csrf
                @method('PATCH')

                <div class="form-grid">
                    <div class="field">
                        <label for="managed-account-name-{{ $selectedManagedUser->id }}">Nama</label>
                        <input class="input" id="managed-account-name-{{ $selectedManagedUser->id }}" name="name" type="text" value="{{ $selectedManagedUser->name }}" required>
                    </div>

                    <div class="field">
                        <label for="managed-account-email-{{ $selectedManagedUser->id }}">Email</label>
                        <input class="input" id="managed-account-email-{{ $selectedManagedUser->id }}" name="email" type="email" value="{{ $selectedManagedUser->email }}" required>
                    </div>

                    <div class="field">
                        <label for="managed-account-role-{{ $selectedManagedUser->id }}">Role</label>
                        <select class="select" id="managed-account-role-{{ $selectedManagedUser->id }}" name="role" required>
                            @foreach ($manageableRoles as $role)
                                <option value="{{ $role }}" @selected($selectedManagedUser->role === $role)>{{ $roleLabels[$role] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="managed-account-password-{{ $selectedManagedUser->id }}">Reset Password</label>
                        <input class="input" id="managed-account-password-{{ $selectedManagedUser->id }}" name="password" type="password" placeholder="Kosongkan jika tidak diubah">
                    </div>

                    <div class="field-wide">
                        <label for="managed-account-password-confirmation-{{ $selectedManagedUser->id }}">Konfirmasi Password Reset</label>
                        <input class="input" id="managed-account-password-confirmation-{{ $selectedManagedUser->id }}" name="password_confirmation" type="password">
                        <small>Password hanya akan diganti jika kolom reset diisi.</small>
                    </div>
                </div>

                <div class="button-row" style="margin-top: 18px;">
                    <button class="button" type="submit">Simpan Perubahan</button>
                    <a class="button-ghost" href="{{ route('profile.accounts.show', array_filter([
                        'q' => $filters['q'] ?? null,
                        'role' => $filters['role'] ?? null,
                    ])) }}">Tutup Editor</a>
                </div>
            </form>
        </section>
    @endif
@endsection
