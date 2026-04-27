<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Autentikasi Inventaris Sekolah')</title>
        <link rel="stylesheet" href="{{ asset('css/dashboard-premium.css') }}">
    </head>
    <body class="auth-screen">
        @php
            $appName = \App\Models\Setting::getValue('app_name', 'Sekolah Permata Harapan');
            $appSubtitle = \App\Models\Setting::getValue('app_subtitle', 'Sekolah Inventaris');
            $appLogo = \App\Models\Setting::getValue('app_logo');
            $logoUrl = $appLogo ? asset('storage/' . $appLogo) : asset('images/logo.png');
        @endphp
        <main class="auth-shell">
            <section class="table-panel auth-card">
                <div class="auth-card-head">
                    <div class="auth-card-intro">
                        <div class="auth-brand">
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="auth-logo">
                            <span class="logo-subtitle">{{ $appSubtitle }}</span>
                        </div>
                        <h1 class="auth-page-title">@yield('page_title', 'Akses Inventaris')</h1>
                        <p class="auth-page-subtitle">@yield('page_subtitle', 'Masuk atau buat akun untuk mengelola stok, lokasi penyimpanan, dan mutasi barang dalam satu dashboard.')</p>
                    </div>
                    <div class="header-actions">
                        @yield('header_actions')
                    </div>
                </div>

                <div class="auth-panel-body">
                    <div class="auth-panel-copy">
                        <h2 class="panel-title">@yield('panel_title', 'Autentikasi')</h2>
                        <p class="panel-subtitle">@yield('panel_subtitle', 'Isi form berikut untuk melanjutkan.')</p>
                    </div>

                    @if (session('status'))
                        <div class="auth-alert auth-alert-success">
                            <strong>Status</strong>
                            <div>{{ session('status') }}</div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="auth-alert auth-alert-danger">
                            <strong>Periksa input berikut:</strong>
                            <ul style="margin: 10px 0 0; padding-left: 18px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </section>
        </main>
    </body>
</html>
