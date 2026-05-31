@extends('layouts.app')

@section('title', 'Dashboard - ' . \App\Models\Setting::getValue('app_name', 'Sekolah Permata Harapan'))
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
        <div class="notice-box info fade-in-up" style="margin-bottom: 24px;">
            <div class="notice-header">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Tabel inventaris belum tersedia
            </div>
            <p>Dashboard sudah siap dipakai, tetapi data inventaris di sistem ini masih kosong atau belum lengkap. Jalankan <code>php artisan migrate --seed</code> untuk menyiapkan data awal.</p>
        </div>
    @endif

    @if ($summary['pending_request_count'] > 0 && auth()->user()->hasPermission(\App\Models\RolePermission::PERMISSION_REQUESTS_MANAGE))
        <div class="notice-box warning fade-in-up" style="margin-bottom: 24px;">
            <div class="notice-header">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                Notifikasi Langsung
            </div>
            <p>Terdapat <strong>{{ $summary['pending_request_count'] }}</strong> permintaan barang yang menunggu persetujuan. Silakan tinjau dan proses permintaan tersebut.</p>
            <div style="margin-top: 4px;">
                <a href="{{ route('permintaan-barang.index') }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Lihat Permintaan</a>
            </div>
        </div>
    @endif

    <!-- Summary Stats Cards -->
    <div class="stats-container fade-in-up delay-1">
        <div class="stat-card">
            <div class="stat-icon info">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div class="stat-label">Jenis Barang</div>
            <div class="stat-value">{{ number_format($summary['item_count'], 0, ',', '.') }}</div>
            <div class="stat-desc">{{ number_format($summary['location_count'], 0, ',', '.') }} lokasi aktif di gudang.</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon warning">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div class="stat-label">Stok Menipis</div>
            <div class="stat-value" style="color: var(--warning-text);">{{ number_format($summary['low_stock_count'], 0, ',', '.') }}</div>
            <div class="stat-desc">Barang mencapai batas minimum.</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <div class="stat-label">Permintaan Menunggu</div>
            <div class="stat-value">{{ number_format($summary['pending_request_count'], 0, ',', '.') }}</div>
            <div class="stat-desc">Pengajuan barang yang belum diproses.</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #ede9fe; color: #7c3aed; border-color: #ddd6fe;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="stat-label">Mutasi Bulan Ini</div>
            <div class="stat-value">{{ number_format($netMovement, 0, ',', '.') }}</div>
            <div class="stat-desc">Selisih barang masuk & keluar.</div>
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
                <a class="btn btn-secondary" href="{{ route('barang.index', ['status' => 'menipis']) }}">
                    Lihat Semua
                </a>
            @endif
        </div>

        <div class="table-wrapper">
            @if ($lowStockItems->isEmpty())
                <div style="padding: 60px 40px; text-align: center; color: var(--text-muted);">
                    <div style="background: var(--bg-base); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:32px;height:32px;opacity:0.6;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h4 style="color: var(--text-main); margin-bottom: 4px;">Stok Aman Terkendali</h4>
                    <p style="font-size: 0.9rem;">Semua barang dalam kondisi stok yang mencukupi.</p>
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
                                    <div class="item-icon" style="background: var(--primary-soft); color: var(--primary); font-weight: 700;">
                                        {{ substr($item->name, 0, 1) }}
                                    </div>
                                    <div class="item-info">
                                        <strong>{{ $item->name }}</strong>
                                        <span style="font-family: monospace; font-size: 0.7rem; background: var(--bg-base); padding: 2px 4px; border-radius: 4px;">{{ $item->sku ?? 'NO-SKU' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">
                                    {{ $item->location?->name ?? 'Gudang Utama' }}
                                </div>
                                <div style="color: var(--text-light); font-size: 0.75rem;">
                                    Rak: {{ $item->rack_number ?? '-' }} / Baris: {{ $item->row_number ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span class="badge badge-danger" style="width: fit-content;">Sisa: {{ number_format($item->stock, 0, ',', '.') }}</span>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">
                                        Min: <strong>{{ number_format($item->minimum_stock, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <a class="btn btn-secondary btn-mini" href="{{ route('barang.show', $item) ?? '#' }}">Detail</a>
                                    @if (auth()->user()->hasPermission(\App\Models\RolePermission::PERMISSION_PURCHASES_MANAGE))
                                        <a class="btn btn-primary btn-mini" href="{{ route('pembelian-barang.create') ?? '#' }}">Restok</a>
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
                    <div class="stat-label" style="font-size: 0.7rem; color: var(--text-light);">Permintaan barang</div>
                    <h3 class="section-title">Menunggu Review</h3>
                </div>
                <a class="btn btn-secondary" href="{{ route('permintaan-barang.index') }}" style="font-size: 0.8rem; padding: 6px 12px;">Kelola</a>
            </div>

            @if ($pendingRequests->isEmpty())
                <div class="empty-state">Tidak ada permintaan yang sedang menunggu.</div>
            @else
                <div class="movement-list">
                    @foreach ($pendingRequests as $requestItem)
                        <article class="movement-item">
                            <div class="list-top">
                                <div>
                                    <strong style="font-size: 0.95rem;">{{ $requestItem->item?->name ?? 'Barang tidak ditemukan' }}</strong>
                                    <div class="meta" style="font-size: 0.75rem; margin-top: 4px;">
                                        <span style="font-weight: 600; color: var(--primary);">{{ $requestItem->requester_name }}</span>
                                        <span style="color: var(--text-light);">{{ $requestItem->requested_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <span class="badge badge-warning" style="font-size: 0.8rem; padding: 4px 10px;">{{ number_format($requestItem->quantity_requested, 0, ',', '.') }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="stat-label" style="font-size: 0.7rem; color: var(--text-light);">Pembelian terbaru</div>
                    <h3 class="section-title">Restok Terakhir</h3>
                </div>
                @if (auth()->user()->hasPermission(\App\Models\RolePermission::PERMISSION_PURCHASES_MANAGE))
                    <a class="btn btn-secondary" href="{{ route('pembelian-barang.index') }}" style="font-size: 0.8rem; padding: 6px 12px;">Riwayat</a>
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
                                    <strong style="font-size: 0.95rem;">{{ $purchase->item?->name ?? 'Barang tidak ditemukan' }}</strong>
                                    <div class="meta" style="font-size: 0.75rem; margin-top: 4px;">
                                        <span style="font-weight: 600; color: var(--success-text);">{{ $purchase->store_name }}</span>
                                        <span style="color: var(--text-light);">{{ $purchase->purchased_at->format('d M') }}</span>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div class="badge success" style="font-size: 0.8rem; padding: 4px 10px; margin-bottom: 2px;">+{{ number_format($purchase->quantity_purchased, 0, ',', '.') }}</div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700;">Rp{{ number_format((float) $purchase->total_cost, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
