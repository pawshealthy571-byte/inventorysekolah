@extends('layouts.app')

@section('title', $item->name)
@section('eyebrow', 'Detail Inventaris')
@section('page_title', $item->name)
@section('page_subtitle', 'Cek identitas barang, status stok, lokasi simpan, dan riwayat mutasi terakhir dari satu halaman.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('barang.edit', $item) }}">Edit Barang</a>
    <a class="button-secondary" href="{{ route('permintaan-barang.create') }}">Buat Permintaan</a>
    <a class="button-secondary" href="{{ route('pembelian-barang.create') }}">Catat Pembelian</a>
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
            <span class="muted">Stok layak pakai</span>
            <strong>{{ number_format($item->usableStock(), 0, ',', '.') }}</strong>
            <p>{{ $item->isLowStock() ? 'Sudah masuk prioritas restok.' : 'Masih berada di atas batas aman.' }}</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Jumlah mutasi</span>
            <strong>{{ number_format($movements->count(), 0, ',', '.') }}</strong>
            <p>Data yang ditampilkan adalah 12 mutasi terbaru.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Rekomendasi beli</span>
            <strong style="font-size: 2rem;">{{ number_format($item->recommendedPurchaseQuantityFor(), 0, ',', '.') }}</strong>
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
                    <strong>Stok baik</strong>
                    <div>
                        <span class="badge badge-accent">{{ number_format($item->stock_good, 0, ',', '.') }} {{ $item->unit }}</span>
                    </div>
                </div>
                <div class="detail-row">
                    <strong>Stok kurang baik</strong>
                    <div>
                        <span class="badge badge-warning">{{ number_format($item->stock_less_good, 0, ',', '.') }} {{ $item->unit }}</span>
                    </div>
                </div>
                <div class="detail-row">
                    <strong>Stok rusak</strong>
                    <div>
                        <span class="badge badge-danger">{{ number_format($item->stock_damaged, 0, ',', '.') }} {{ $item->unit }}</span>
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
                    <strong>Kelola permintaan barang</strong>
                    <p class="muted" style="margin: 8px 0 0;">Permintaan yang disetujui akan mengurangi stok baik lalu stok kurang baik secara otomatis.</p>
                    <div class="button-row" style="margin-top: 14px;">
                        <a class="button-secondary" href="{{ route('permintaan-barang.index') }}">Lihat Permintaan</a>
                        <a class="button-ghost" href="{{ route('permintaan-barang.create') }}">Buat Permintaan</a>
                    </div>
                </div>

                <div class="stack-item">
                    <strong>Catat pembelian</strong>
                    <p class="muted" style="margin: 8px 0 0;">Hasil pembelian otomatis masuk ke stok barang baik dan tercatat di riwayat.</p>
                    <div class="button-row" style="margin-top: 14px;">
                        <a class="button-secondary" href="{{ route('pembelian-barang.index') }}">Riwayat Pembelian</a>
                        <a class="button-ghost" href="{{ route('pembelian-barang.create') }}">Tambah Pembelian</a>
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
                                    <span>{{ $movement->conditionBucketLabel() }}</span>
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

    <section class="detail-grid" style="margin-top: 18px;">
        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Riwayat permintaan</div>
                    <h3 class="section-title">6 Permintaan Terakhir</h3>
                </div>
            </div>

            @if ($requests->isEmpty())
                <div class="empty-state">Belum ada permintaan untuk barang ini.</div>
            @else
                <div class="movement-list">
                    @foreach ($requests as $requestItem)
                        <article class="movement-item">
                            <div class="list-top">
                                <div>
                                    <strong>{{ $requestItem->requester_name }}</strong>
                                    <div class="meta">
                                        <span>{{ $requestItem->requested_at->format('d/m/Y H:i') }}</span>
                                        <span>{{ number_format($requestItem->quantity_requested, 0, ',', '.') }} {{ $item->unit }}</span>
                                    </div>
                                </div>
                                <span class="badge {{ $requestItem->status === 'disetujui' ? 'badge-accent' : ($requestItem->status === 'ditolak' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $requestItem->statusLabel() }}
                                </span>
                            </div>
                            <p class="muted" style="margin: 12px 0 0;">
                                {{ $requestItem->note ?: 'Tidak ada catatan tambahan.' }}
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Riwayat pembelian</div>
                    <h3 class="section-title">6 Pembelian Terakhir</h3>
                </div>
            </div>

            @if ($purchases->isEmpty())
                <div class="empty-state">Belum ada pembelian untuk barang ini.</div>
            @else
                <div class="movement-list">
                    @foreach ($purchases as $purchase)
                        <article class="movement-item">
                            <div class="list-top">
                                <div>
                                    <strong>{{ $purchase->store_name }}</strong>
                                    <div class="meta">
                                        <span>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</span>
                                        <span>{{ number_format($purchase->quantity_purchased, 0, ',', '.') }} {{ $item->unit }}</span>
                                    </div>
                                </div>
                                <span class="badge badge-accent">
                                    Rp{{ number_format((float) $purchase->total_cost, 0, ',', '.') }}
                                </span>
                            </div>
                            <p class="muted" style="margin: 12px 0 0;">
                                {{ $purchase->note ?: 'Pembelian tanpa catatan tambahan.' }}
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
