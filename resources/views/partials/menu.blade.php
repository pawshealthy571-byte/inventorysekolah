<nav class="site-menu">
    <div class="site-menu__inner panel">
        <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <em>01</em>
            <strong>Dashboard</strong>
            <span>Ringkasan utama</span>
        </a>
        <a class="menu-link {{ request()->routeIs('dashboard.operational') ? 'active' : '' }}" href="{{ route('dashboard.operational') }}">
            <em>02</em>
            <strong>Operasional</strong>
            <span>Detail stok dan mutasi</span>
        </a>
        <a class="menu-link {{ request()->routeIs('barang.index', 'barang.show', 'barang.edit') ? 'active' : '' }}" href="{{ route('barang.index') }}">
            <em>03</em>
            <strong>Daftar Barang</strong>
            <span>Cari dan filter item</span>
        </a>
        <a class="menu-link {{ request()->routeIs('barang.create') ? 'active' : '' }}" href="{{ route('barang.create') }}">
            <em>04</em>
            <strong>Tambah Barang</strong>
            <span>Input inventaris baru</span>
        </a>
        <a class="menu-link {{ request()->routeIs('permintaan-barang.*') ? 'active' : '' }}" href="{{ route('permintaan-barang.index') }}">
            <em>05</em>
            <strong>Permintaan</strong>
            <span>Ajukan dan setujui barang</span>
        </a>
        <a class="menu-link {{ request()->routeIs('pembelian-barang.*') ? 'active' : '' }}" href="{{ route('pembelian-barang.index') }}">
            <em>06</em>
            <strong>Pembelian</strong>
            <span>Restok dan biaya barang</span>
        </a>
        <a class="menu-link {{ request()->routeIs('stock-movements.create', 'stock-movements.store') ? 'active' : '' }}" href="{{ route('stock-movements.create') }}">
            <em>07</em>
            <strong>Mutasi Stok</strong>
            <span>Catat barang masuk keluar</span>
        </a>
    </div>
</nav>
