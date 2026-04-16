@extends('layouts.app')

@section('title', 'Operasional Gudang')
@section('eyebrow', 'Detail Operasional')
@section('page_title', 'Operasional Gudang')
@section('page_subtitle', 'Halaman ini menampung detail stok menipis, mutasi terbaru, kategori, lokasi, dan kondisi barang agar dashboard utama tetap ringkas.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
    <a class="button" href="{{ route('stock-movements.create') }}">Catat Mutasi</a>
@endsection

@section('content')
    @php
        $maxCategoryItems = max((int) ($categories->max('items_count') ?? 1), 1);
        $maxLocationItems = max((int) ($locations->max('items_count') ?? 1), 1);
    @endphp

    @if ($setupRequired ?? false)
        <section class="panel section-card notice-box">
            <div class="section-header">
                <div>
                    <div class="muted">Informasi</div>
                    <h3 class="section-title">Data inventaris belum tersedia</h3>
                </div>
                <span class="badge badge-warning">Belum ada data</span>
            </div>
            <p class="muted">
                Halaman operasional tetap bisa dibuka, tetapi data inventaris pada sistem ini belum tersedia
                untuk ditampilkan.
            </p>
        </section>
    @endif

    <section class="stats-grid" style="margin-bottom: 18px;">
        <article class="panel stat-card">
            <span class="muted">Total stok tersedia</span>
            <strong>{{ number_format($summary['total_stock'], 0, ',', '.') }}</strong>
            <p>Akumulasi seluruh unit barang aktif.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Data barang</span>
            <strong>{{ number_format($summary['item_count'], 0, ',', '.') }}</strong>
            <p>{{ number_format($summary['category_count'], 0, ',', '.') }} kategori dan {{ number_format($summary['location_count'], 0, ',', '.') }} lokasi.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Stok menipis</span>
            <strong>{{ number_format($summary['low_stock_count'], 0, ',', '.') }}</strong>
            <p>Barang yang sudah menyentuh batas minimum.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Permintaan menunggu</span>
            <strong>{{ number_format($summary['pending_request_count'], 0, ',', '.') }}</strong>
            <p>Perlu review atau tindak lanjut pembelian.</p>
        </article>
    </section>

    <section class="dashboard-grid" style="margin-bottom: 18px;">
        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Prioritas tindak lanjut</div>
                    <h3 class="section-title">Barang Dengan Stok Menipis</h3>
                </div>
                <span class="badge badge-danger">{{ number_format($summary['low_stock_count'], 0, ',', '.') }} item</span>
            </div>

            @if ($lowStockItems->isEmpty())
                <div class="empty-state">Belum ada barang yang masuk kategori stok menipis.</div>
            @else
                <div class="data-list">
                    @foreach ($lowStockItems as $item)
                        <div class="data-item">
                            <div class="list-top">
                                <div>
                                    <strong>{{ $item->name }}</strong>
                                    <div class="meta">
                                        <span>SKU {{ $item->sku }}</span>
                                        <span>{{ $item->category?->name ?? 'Tanpa kategori' }}</span>
                                        <span>{{ $item->location?->name ?? 'Lokasi belum diatur' }}</span>
                                    </div>
                                </div>
                                <span class="badge badge-danger">{{ number_format($item->stock, 0, ',', '.') }} / min {{ number_format($item->minimum_stock, 0, ',', '.') }}</span>
                            </div>
                            <div class="meta">
                                <span>Baik {{ number_format($item->stock_good, 0, ',', '.') }}</span>
                                <span>Kurang baik {{ number_format($item->stock_less_good, 0, ',', '.') }}</span>
                                <span>Rusak {{ number_format($item->stock_damaged, 0, ',', '.') }}</span>
                                <span>Rekomendasi beli {{ number_format($item->recommendedPurchaseQuantityFor(), 0, ',', '.') }}</span>
                            </div>
                            <div class="inline-actions" style="margin-top: 12px;">
                                <a class="button-secondary" href="{{ route('barang.show', $item) }}">Detail</a>
                                <a class="button-ghost" href="{{ route('pembelian-barang.create') }}">Restok</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Aktivitas terbaru</div>
                    <h3 class="section-title">Mutasi Stok Terbaru</h3>
                </div>
                <span class="badge badge-accent">{{ number_format($recentMovements->count(), 0, ',', '.') }} catatan</span>
            </div>

            @if ($recentMovements->isEmpty())
                <div class="empty-state">Belum ada mutasi stok yang tercatat.</div>
            @else
                <div class="movement-list">
                    @foreach ($recentMovements as $movement)
                        <article class="movement-item">
                            <div class="list-top">
                                <div>
                                    <strong>{{ $movement->item?->name ?? 'Barang tidak ditemukan' }}</strong>
                                    <div class="meta">
                                        <span>{{ $movement->typeLabel() }}</span>
                                        <span>{{ $movement->conditionBucketLabel() }}</span>
                                        <span>{{ $movement->moved_at->format('d/m/Y H:i') }}</span>
                                        @if ($movement->actor)
                                            <span>{{ $movement->actor }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="badge {{ $movement->isIncoming() ? 'badge-accent' : 'badge-warning' }}">
                                    {{ $movement->isIncoming() ? '+' : '-' }}{{ number_format($movement->quantity, 0, ',', '.') }}
                                </span>
                            </div>
                            @if ($movement->reference || $movement->note)
                                <div class="meta">
                                    @if ($movement->reference)
                                        <span>Ref {{ $movement->reference }}</span>
                                    @endif
                                    @if ($movement->note)
                                        <span>{{ $movement->note }}</span>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </section>

    <section class="grid-3">
        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Riwayat permintaan</div>
                    <h3 class="section-title">Permintaan Terbaru</h3>
                </div>
            </div>

            @if ($recentRequests->isEmpty())
                <div class="empty-state">Belum ada permintaan barang.</div>
            @else
                <div class="stack-list">
                    @foreach ($recentRequests as $requestItem)
                        <div class="stack-item">
                            <div class="list-top">
                                <strong>{{ $requestItem->item?->name ?? 'Barang tidak ditemukan' }}</strong>
                                <span class="badge {{ $requestItem->status === 'disetujui' ? 'badge-accent' : ($requestItem->status === 'ditolak' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $requestItem->statusLabel() }}
                                </span>
                            </div>
                            <p class="muted" style="margin: 12px 0 0;">
                                {{ $requestItem->requester_name }} meminta {{ number_format($requestItem->quantity_requested, 0, ',', '.') }} unit.
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Sebaran data</div>
                    <h3 class="section-title">Kategori Terpadat</h3>
                </div>
            </div>

            @if ($categories->isEmpty())
                <div class="empty-state">Kategori belum tersedia.</div>
            @else
                <div class="stack-list">
                    @foreach ($categories as $category)
                        <div class="stack-item">
                            <div class="list-top">
                                <strong>{{ $category->name }}</strong>
                                <span class="muted">{{ number_format($category->items_count, 0, ',', '.') }} barang</span>
                            </div>
                            <div class="bar">
                                <span style="width: {{ max((int) round(($category->items_count / $maxCategoryItems) * 100), 8) }}%"></span>
                            </div>
                            <div class="meta">
                                <span>Total stok {{ number_format((int) ($category->stock_total ?? 0), 0, ',', '.') }}</span>
                                @if ($category->description)
                                    <span>{{ $category->description }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Kapasitas penyimpanan</div>
                    <h3 class="section-title">Lokasi Tersibuk</h3>
                </div>
            </div>

            @if ($locations->isEmpty())
                <div class="empty-state">Lokasi penyimpanan belum tersedia.</div>
            @else
                <div class="stack-list">
                    @foreach ($locations as $location)
                        <div class="stack-item">
                            <div class="list-top">
                                <strong>{{ $location->name }}</strong>
                                <span class="muted">{{ number_format($location->items_count, 0, ',', '.') }} barang</span>
                            </div>
                            <div class="bar">
                                <span style="width: {{ max((int) round(($location->items_count / $maxLocationItems) * 100), 8) }}%"></span>
                            </div>
                            <div class="meta">
                                <span>Kode {{ $location->code }}</span>
                                <span>Total stok {{ number_format((int) ($location->stock_total ?? 0), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Pembelian terbaru</div>
                    <h3 class="section-title">Restok Barang</h3>
                </div>
            </div>

            @if ($recentPurchases->isEmpty())
                <div class="empty-state">Belum ada pembelian barang.</div>
            @else
                <div class="stack-list">
                    @foreach ($recentPurchases as $purchase)
                        <div class="stack-item">
                            <div class="list-top">
                                <strong>{{ $purchase->item?->name ?? 'Barang tidak ditemukan' }}</strong>
                                <span class="badge badge-accent">Rp{{ number_format((float) $purchase->total_cost, 0, ',', '.') }}</span>
                            </div>
                            <p class="muted" style="margin: 12px 0 0;">
                                {{ number_format($purchase->quantity_purchased, 0, ',', '.') }} unit dari {{ $purchase->store_name }} pada {{ $purchase->purchased_at->format('d/m/Y H:i') }}.
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>
    </section>

    <section class="panel section-card" style="margin-top: 18px;">
        <div class="section-header">
            <div>
                <div class="muted">Kondisi inventaris</div>
                <h3 class="section-title">Ringkasan Kondisi Stok</h3>
            </div>
        </div>

        <div class="stats-grid">
            @foreach ($conditionSummary as $condition)
                <article class="panel stat-card">
                    <span class="muted">{{ $condition['label'] }}</span>
                    <strong>{{ number_format($condition['count'], 0, ',', '.') }}</strong>
                    <p>
                        @if ($condition['status'] === 'baik')
                            Unit siap pakai.
                        @elseif ($condition['status'] === 'kurang-baik')
                            Unit masih dapat dipakai dengan catatan.
                        @else
                            Unit perlu perbaikan atau penggantian.
                        @endif
                    </p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
