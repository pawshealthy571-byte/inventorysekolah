@extends('layouts.app')

@section('title', 'Akses Management')
@section('page_title', 'Akses Management')
@section('page_subtitle', 'Atur modul apa saja yang boleh dipakai setiap role dari halaman khusus superadmin.')

@section('page_actions')
    <a class="btn btn-secondary" href="{{ route('profile.show') }}">Profil Saya</a>
@endsection

@section('content')
    <section class="profile-grid">
        <article class="table-panel profile-panel-full">
            <div class="table-panel-header">
                <div>
                    <h3 class="panel-title">Setting Superadmin</h3>
                    <p class="panel-subtitle">Akses management untuk menentukan modul apa saja yang bisa dipakai setiap role.</p>
                </div>
                <span class="badge badge-success">Akses Management</span>
            </div>

            <div class="profile-panel-body profile-panel-stack">
                <div class="profile-section-copy">
                    <h4>Matrix Akses Role</h4>
                    <p>Superadmin selalu memiliki akses penuh. Perubahan di bawah ini diterapkan untuk role `Pengguna` dan `Admin`.</p>
                </div>

                <form method="POST" action="{{ route('profile.access.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="table-wrapper">
                        <table class="table profile-access-table">
                            <thead>
                                <tr>
                                    <th>Modul</th>
                                    @foreach ($accessRoles as $role)
                                        <th>{{ $roleLabels[$role] }}</th>
                                    @endforeach
                                    <th>Superadmin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissionDefinitions as $permission => $definition)
                                    <tr>
                                        <td>
                                            <div class="profile-access-copy">
                                                <strong>{{ $definition['label'] }}</strong>
                                                <span>{{ $definition['description'] }}</span>
                                            </div>
                                        </td>
                                        @foreach ($accessRoles as $role)
                                            <td>
                                                <label class="profile-access-toggle">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[{{ $role }}][]"
                                                        value="{{ $permission }}"
                                                        @checked($rolePermissionMatrix[$role][$permission] ?? false)
                                                    >
                                                    <span>Izinkan</span>
                                                </label>
                                            </td>
                                        @endforeach
                                        <td>
                                            <span class="badge badge-success">Selalu aktif</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="button-row profile-panel-actions">
                        <button class="btn btn-primary" type="submit">Simpan Hak Akses</button>
                    </div>
                </form>
            </div>
        </article>
    </section>
@endsection
