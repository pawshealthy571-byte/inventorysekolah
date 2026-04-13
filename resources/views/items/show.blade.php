@extends('layouts.app')

@section('title', $item->name)
@section('eyebrow', 'Detail Inventaris')
@section('page_title', $item->name)
@section('page_subtitle', 'Cek identitas barang, status stok, lokasi simpan, dan riwayat mutasi terakhir dari satu halaman.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('barang.edit', $item) }}">Edit Barang</a>
    <a class="button" href="{{ route('stock-movements.create', ['item' => $item->id]) }}">Tambah Mutasi</a>
@endsection

@section('content')
    <section class="stats-grid" style="margin-bottom: 18px;">
        <article class="panel stat-card">
            <span class="muted">Stok saat ini</span>
            <strong>{{ number_format($item->stock, 0, ',', '.') }}</strong>
            <p>{{ $item->unit }} tersedia di gudang.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Batas minimum</span>
            <strong>{{ number_format($item->minimum_stock, 0, ',', '.') }}</strong>
            <p>{{ $item->isLowStock() ? 'Sudah masuk prioritas restok.' : 'Masih berada di atas batas aman.' }}</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Jumlah mutasi</span>
            <strong>{{ number_format($movements->count(), 0, ',', '.') }}</strong>
            <p>Data yang ditampilkan adalah 12 mutasi terbaru.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Kondisi barang</span>
            <strong style="font-size: 2rem;">{{ $item->conditionLabel() }}</strong>
            <p>{{ $item->category?->name ?? 'Tanpa kategori' }} di {{ $item->location?->name ?? 'lokasi belum diatur' }}.</p>
        </article>
    </section>

    <section class="detail-grid">
        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Informasi utama</div>
                    <h3 class="section-title">Profil Barang</h3>
                </div>
            </div>

            <div class="detail-list">
                <div class="detail-row">
                    <strong>Nama</strong>
                    <div>{{ $item->name }}</div>
                </div>
                <div class="detail-row">
                    <strong>SKU</strong>
                    <div>{{ $item->sku }}</div>
                </div>
                <div class="detail-row">
                    <strong>Kategori</strong>
                    <div>{{ $item->category?->name ?? 'Tanpa kategori' }}</div>
                </div>
                <div class="detail-row">
                    <strong>Lokasi</strong>
                    <div>{{ $item->location?->name ?? 'Belum diatur' }}</div>
                </div>
                <div class="detail-row">
                    <strong>Satuan</strong>
                    <div>{{ $item->unit }}</div>
                </div>
                <div class="detail-row">
                    <strong>Kondisi</strong>
                    <div>
                        <span class="badge {{ $item->condition_status === 'baik' ? 'badge-accent' : ($item->condition_status === 'perlu-perawatan' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $item->conditionLabel() }}
                        </span>
                    </div>
                </div>
                <div class="detail-row">
                    <strong>Deskripsi</strong>
                    <div>{{ $item->description ?: 'Tidak ada deskripsi tambahan.' }}</div>
                </div>
            </div>
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Aksi cepat</div>
                    <h3 class="section-title">Kelola Barang</h3>
                </div>
            </div>

            <div class="stack-list">
                <div class="stack-item">
                    <strong>Catat barang masuk atau keluar</strong>
                    <p class="muted" style="margin: 8px 0 0;">Gunakan form mutasi agar stok berubah lewat histori yang tercatat.</p>
                    <div class="button-row" style="margin-top: 14px;">
                        <a class="button" href="{{ route('stock-movements.create', ['item' => $item->id]) }}">Buka Mutasi</a>
                    </div>
                </div>

                <div class="stack-item">
                    <strong>Perbarui metadata barang</strong>
                    <p class="muted" style="margin: 8px 0 0;">Edit kategori, lokasi, satuan, dan kondisi tanpa merusak histori stok.</p>
                    <div class="button-row" style="margin-top: 14px;">
                        <a class="button-secondary" href="{{ route('barang.edit', $item) }}">Edit Data</a>
                    </div>
                </div>

                <div class="stack-item">
                    <strong>Hapus barang</strong>
                    <p class="muted" style="margin: 8px 0 0;">Gunakan hanya jika item memang tidak lagi dibutuhkan.</p>
                    <div class="button-row" style="margin-top: 14px;">
                        <form class="inline-form" method="POST" action="{{ route('barang.destroy', $item) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button-danger" type="submit">Hapus Barang</button>
                        </form>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="panel section-card" style="margin-top: 18px;">
        <div class="section-header">
            <div>
                <div class="muted">Riwayat stok</div>
                <h3 class="section-title">12 Mutasi Terakhir</h3>
            </div>
        </div>

        @if ($movements->isEmpty())
            <div class="empty-state">Belum ada mutasi stok untuk barang ini.</div>
        @else
            <div class="movement-list">
                @foreach ($movements as $movement)
                    <article class="movement-item">
                        <div class="list-top">
                            <div>
                                <strong>{{ $movement->typeLabel() }}</strong>
                                <div class="meta">
                                    <span>{{ $movement->moved_at->format('d/m/Y H:i') }}</span>
                                    @if ($movement->reference)
                                        <span>Ref {{ $movement->reference }}</span>
                                    @endif
                                    @if ($movement->actor)
                                        <span>{{ $movement->actor }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge {{ $movement->isIncoming() ? 'badge-accent' : 'badge-warning' }}">
                                {{ $movement->isIncoming() ? '+' : '-' }}{{ number_format($movement->quantity, 0, ',', '.') }} {{ $item->unit }}
                            </span>
                        </div>
                        @if ($movement->note)
                            <p class="muted" style="margin: 12px 0 0;">{{ $movement->note }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
