<header class="site-header">
    <div class="site-header__inner panel">
        <div class="site-header__copy">
            <div class="brand-kicker">Warehouse Control Board</div>
            <a class="brand-title-link" href="{{ route('dashboard') }}">Gudang Sekolah</a>
            <p class="brand-subtitle">
                Sistem inventaris untuk memantau stok, mutasi, dan prioritas operasional sekolah dalam tampilan panel yang lebih tegas.
            </p>
        </div>

        <div class="header-console">
            <div class="console-card">
                <span class="console-label">Mode Layout</span>
                <strong>Blade Partials</strong>
                <small>Header, menu, dan footer dipisah.</small>
            </div>
            <div class="console-grid">
                <div class="console-chip">
                    <span>Root</span>
                    <strong>/</strong>
                </div>
                <div class="console-chip">
                    <span>Route</span>
                    <strong>{{ request()->route()?->getName() ?? 'web' }}</strong>
                </div>
                <div class="console-chip">
                    <span>Menu</span>
                    <strong>Aktif</strong>
                </div>
                <div class="console-chip">
                    <span>Theme</span>
                    <strong>New UI</strong>
                </div>
            </div>
        </div>
    </div>
</header>
