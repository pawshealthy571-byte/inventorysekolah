<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Gudang Sekolah')</title>
        <link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
    </head>
    <body>
        <div class="site-shell">
            @include('partials.header')
            @include('partials.menu')

            <main class="site-main">
                <section class="page-hero panel">
                    <div class="page-hero__main">
                        <span class="eyebrow">@yield('eyebrow', 'Operasional Gudang')</span>
                        <h1 class="page-title">@yield('page_title', 'Gudang Sekolah')</h1>
                        <p class="page-copy">@yield('page_subtitle', 'Pantau inventaris sekolah dengan tampilan yang lebih rapi dan operasional.')</p>
                    </div>
                    <aside class="page-hero__aside">
                        <div class="hero-panel">
                            <span class="hero-panel__label">Struktur Layout</span>
                            <strong>Header, Menu, Footer</strong>
                            <p>Semua halaman inventaris memakai partial Blade yang sama supaya tampilan konsisten.</p>
                        </div>
                        <div class="topbar-actions">
                            @yield('page_actions')
                        </div>
                    </aside>
                </section>

                @if (session('status'))
                    <div class="flash">
                        <strong>Status</strong>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="error-box">
                        <strong>Periksa input berikut:</strong>
                        <ul style="margin: 10px 0 0; padding-left: 18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            @include('partials.footer')
        </div>
    </body>
</html>
