<header class="site-header">
    <div class="site-header__inner panel">
        <div class="site-header__copy">
            <div class="brand-kicker">Sistem Inventaris</div>
            <a class="brand-title-link" href="{{ route('dashboard') }}">Gudang Sekolah</a>
            <p class="brand-subtitle">
                Kelola stok, mutasi, dan data barang sekolah dari satu aplikasi inventaris berbasis Blade.
            </p>
        </div>

        <div class="header-console">
            <div class="console-card">
                <span class="console-label">Layout</span>
                <strong>Blade</strong>
                <small>Header, menu, dan footer dipisah.</small>
            </div>
            <div class="console-grid">
                <div class="console-chip">
                    <span>Halaman</span>
                    <strong>{{ request()->route()?->getName() ?? 'web' }}</strong>
                </div>
                <div class="console-chip">
                    <span>URL</span>
                    <strong>{{ request()->path() === '/' ? '/' : '/'.request()->path() }}</strong>
                </div>
                <div class="console-chip">
                    <span>Menu</span>
                    <strong>Aktif</strong>
                </div>
                <div class="console-chip">
                    <span>Modul</span>
                    <strong>Gudang</strong>
                </div>
            </div>
        </div>
    </div>
</header>
