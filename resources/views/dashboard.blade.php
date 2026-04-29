@extends('layouts.app')

@section('title', 'Dashboard - Sekolah Permata Harapan')
@section('eyebrow', 'Ringkasan Inventaris')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Ringkasan operasional inventaris dan status stok terbaru.')

@section('page_actions')
    @if (auth()->user()->hasPermission(\App\Models\RolePermission::PERMISSION_STOCK_MOVEMENTS_MANAGE))
        <a class="btn btn-primary" href="{{ route('stock-movements.create') }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Catat Mutasi
        </a>
    @endif
@endsection

@section('content')
    @php
        $netMovement = $summary['incoming_this_month'] - $summary['outgoing_this_month'];
        $safeItems = max($summary['item_count'] - $summary['low_stock_count'], 0);
        $safeRate = $summary['item_count'] > 0 ? round(($safeItems / $summary['item_count']) * 100) : 0;
    @endphp

    @if ($setupRequired ?? false)
        <div class="notice-box fade-in-up" style="margin-bottom: 24px;">
            <strong>Tabel inventaris belum tersedia</strong>
            <p>Dashboard sudah siap dipakai, tetapi data inventaris di sistem ini masih kosong atau belum lengkap. Jalankan <code>php artisan migrate --seed</code> untuk menyiapkan data awal.</p>
        </div>
    @endif

    <!-- Summary Stats Cards -->
    <div class="stats-container fade-in-up delay-1">
        <div class="stat-card">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div class="stat-label">Total Stok</div>
            <div class="stat-value">{{ number_format($summary['total_stock'], 0, ',', '.') }}</div>
            <div class="stat-desc">Akumulasi seluruh unit aktif.</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            </div>
            <div class="stat-label">Jenis Barang</div>
            <div class="stat-value">{{ number_format($summary['item_count'], 0, ',', '.') }}</div>
            <div class="stat-desc">{{ number_format($summary['location_count'], 0, ',', '.') }} lokasi aktif di gudang.</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon warning">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div class="stat-label">Stok Menipis</div>
            <div class="stat-value" style="color: var(--warning-text);">{{ number_format($summary['low_stock_count'], 0, ',', '.') }}</div>
            <div class="stat-desc">Barang mencapai batas minimum.</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: var(--success); background: var(--success-soft); border-color: rgba(16, 185, 129, 0.2);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
            <div class="stat-label">Permintaan Menunggu</div>
            <div class="stat-value">{{ number_format($summary['pending_request_count'], 0, ',', '.') }}</div>
            <div class="stat-desc">Pengajuan barang yang belum diproses.</div>
        </div>
    </div>

    <!-- Item Table with Low-Stock Badges -->
    <div class="table-panel fade-in-up delay-2">
        <div class="table-panel-header">
            <div>
                <h3 class="panel-title">Barang Perlu Restok</h3>
                <p class="panel-subtitle">Prioritas tindak lanjut untuk menjaga ketersediaan barang.</p>
            </div>
            @if (!$lowStockItems->isEmpty() && auth()->user()->hasPermission(\App\Models\RolePermission::PERMISSION_ITEMS_MANAGE))
                <a class="btn btn-secondary" href="{{ route('barang.index', ['status' => 'menipis']) }}" style="padding: 8px 16px; font-size: 0.85rem;">
                    Lihat Semua
                </a>
            @endif
        </div>

        <div class="table-wrapper">
            @if ($lowStockItems->isEmpty())
                <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:48px;height:48px;opacity:0.5;margin-bottom:12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p>Semua barang dalam kondisi stok aman.</p>
                </div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Kategori & Lokasi</th>
                            <th>Status Stok</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lowStockItems as $item)
                        <tr>
                            <td>
                                <div class="item-cell">
                                    <div class="item-icon">
                                        {{ substr($item->name, 0, 1) }}
                                    </div>
                                    <div class="item-info">
                                        <strong>{{ $item->name }}</strong>
                                        <span>SKU: {{ $item->sku ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="color: var(--text-main); font-weight: 500;">
                                    Lokasi Penyimpanan
                                </div>
                                <div style="color: var(--text-muted); font-size: 0.85rem;">
                                    {{ $item->location?->name ?? 'Lokasi belum diatur' }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                    <span class="badge badge-danger">Sisa: {{ number_format($item->stock, 0, ',', '.') }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Min: {{ number_format($item->minimum_stock, 0, ',', '.') }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Rekomendasi beli: {{ number_format($item->recommendedPurchaseQuantityFor(), 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a class="btn btn-secondary" href="{{ route('barang.show', $item) ?? '#' }}" style="padding: 6px 14px; font-size: 0.85rem;">Detail</a>
                                    @if (auth()->user()->hasPermission(\App\Models\RolePermission::PERMISSION_PURCHASES_MANAGE))
                                        <a class="btn btn-primary" href="{{ route('pembelian-barang.create') ?? '#' }}" style="padding: 6px 14px; font-size: 0.85rem;">Restok</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <section class="dashboard-grid fade-in-up delay-2" style="margin-top: 24px;">
        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Permintaan barang</div>
                    <h3 class="section-title">Menunggu Review</h3>
                </div>
                <a class="button-secondary" href="{{ route('permintaan-barang.index') }}">Kelola</a>
            </div>

            @if ($pendingRequests->isEmpty())
                <div class="empty-state">Tidak ada permintaan yang sedang menunggu.</div>
            @else
                <div class="movement-list">
                    @foreach ($pendingRequests as $requestItem)
                        <article class="movement-item">
                            <div class="list-top">
                                <div>
                                    <strong>{{ $requestItem->item?->name ?? 'Barang tidak ditemukan' }}</strong>
                                    <div class="meta">
                                        <span>{{ $requestItem->requester_name }}</span>
                                        <span>{{ $requestItem->requested_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                                <span class="badge badge-warning">{{ number_format($requestItem->quantity_requested, 0, ',', '.') }}</span>
                            </div>
                            <p class="muted" style="margin: 12px 0 0;">
                                @if ($requestItem->recommended_purchase_quantity > 0)
                                    Rekomendasi pembelian {{ number_format($requestItem->recommended_purchase_quantity, 0, ',', '.') }} unit.
                                @else
                                    Stok masih cukup untuk diproses.
                                @endif
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Pembelian terbaru</div>
                    <h3 class="section-title">Restok Terakhir</h3>
                </div>
                @if (auth()->user()->hasPermission(\App\Models\RolePermission::PERMISSION_PURCHASES_MANAGE))
                    <a class="button-secondary" href="{{ route('pembelian-barang.index') }}">Lihat Semua</a>
                @endif
            </div>

            @if ($recentPurchases->isEmpty())
                <div class="empty-state">Belum ada pembelian yang tercatat.</div>
            @else
                <div class="movement-list">
                    @foreach ($recentPurchases as $purchase)
                        <article class="movement-item">
                            <div class="list-top">
                                <div>
                                    <strong>{{ $purchase->item?->name ?? 'Barang tidak ditemukan' }}</strong>
                                    <div class="meta">
                                        <span>{{ $purchase->store_name }}</span>
                                        <span>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                                <span class="badge badge-accent">Rp{{ number_format((float) $purchase->total_cost, 0, ',', '.') }}</span>
                            </div>
                            <p class="muted" style="margin: 12px 0 0;">
                                {{ number_format($purchase->quantity_purchased, 0, ',', '.') }} unit masuk ke stok barang baik.
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
