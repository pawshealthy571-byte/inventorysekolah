<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Sekolah Permata Harapan')</title>
        <link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
        <link rel="stylesheet" href="{{ asset('css/dashboard-premium.css') }}">
    </head>
    <body>
        <div class="app-container">
            <!-- Sidebar Navigation -->
            <aside class="sidebar">
                @php($currentUser = auth()->user())
                <div class="sidebar-header">
                    <div class="brand-text">Permata Harapan<span>Sekolah & Inventaris</span></div>
                </div>
                
                <nav class="nav-menu">
                    @if ($currentUser->hasPermission(\App\Models\RolePermission::PERMISSION_DASHBOARD_VIEW))
                        <a href="{{ route('dashboard') ?? '#' }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Dashboard
                        </a>
                    @endif
                    @if ($currentUser->hasPermission(\App\Models\RolePermission::PERMISSION_ITEMS_VIEW))
                        <a href="{{ route('barang.index') ?? '#' }}" class="nav-item {{ request()->routeIs('barang.*') && !request()->routeIs('barang.create') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            Daftar Barang
                        </a>
                    @endif
                    @if ($currentUser->hasPermission(\App\Models\RolePermission::PERMISSION_ITEMS_MANAGE))
                        <a href="{{ route('barang.create') ?? '#' }}" class="nav-item {{ request()->routeIs('barang.create') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Barang
                        </a>
                    @endif
                    @if ($currentUser->hasPermission(\App\Models\RolePermission::PERMISSION_STOCK_MOVEMENTS_MANAGE))
                        <a href="{{ route('stock-movements.create') ?? '#' }}" class="nav-item {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            Mutasi Stok
                        </a>
                    @endif
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="app-main">
                <div class="page-header fade-in-up">
                    <div>
                        <h1 class="page-title">@yield('page_title', 'Sekolah Permata Harapan')</h1>
                        <p class="page-subtitle">@yield('page_subtitle', 'Pantau inventaris sekolah dengan tampilan yang elegan.')</p>
                    </div>
                    <div class="header-actions">
                        @auth
                            <div class="user-chip">
                                <a class="user-chip__identity" href="{{ route('profile.show') }}">
                                    @if ($currentUser->profilePhotoUrl())
                                        <img class="user-chip__avatar" src="{{ $currentUser->profilePhotoUrl() }}" alt="Foto profil {{ $currentUser->name }}">
                                    @else
                                        <span class="user-chip__avatar user-chip__avatar--fallback">{{ $currentUser->initials() }}</span>
                                    @endif
                                    <div>
                                        <strong>{{ $currentUser->name }}</strong>
                                        <span>{{ $currentUser->roleLabel() }}</span>
                                    </div>
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="button-ghost" type="submit">Logout</button>
                                </form>
                            </div>
                        @endauth
                        @yield('page_actions')
                    </div>
                </div>

                @if (session('status'))
                    <div class="flash fade-in-up">
                        <strong>Status</strong>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="error-box fade-in-up">
                        <strong>Periksa input berikut:</strong>
                        <ul style="margin: 10px 0 0; padding-left: 18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="fade-in-up delay-1">
                    @yield('content')
                </div>
            </main>
        </div>
        @stack('scripts')
    </body>
</html>
