<nav class="site-menu">
    <div class="site-menu__inner panel">
        <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <em>01</em>
            <strong>Dashboard</strong>
            <span>Ringkasan gudang</span>
        </a>
        <a class="menu-link {{ request()->routeIs('barang.index') ? 'active' : '' }}" href="{{ route('barang.index') }}">
            <em>02</em>
            <strong>Daftar Barang</strong>
            <span>Cari dan filter item</span>
        </a>
        <a class="menu-link {{ request()->routeIs('barang.create') ? 'active' : '' }}" href="{{ route('barang.create') }}">
            <em>03</em>
            <strong>Tambah Barang</strong>
            <span>Input inventaris baru</span>
        </a>
        <a class="menu-link {{ request()->routeIs('stock-movements.create') ? 'active' : '' }}" href="{{ route('stock-movements.create') }}">
            <em>04</em>
            <strong>Mutasi Stok</strong>
            <span>Catat barang masuk keluar</span>
        </a>
    </div>
</nav>
