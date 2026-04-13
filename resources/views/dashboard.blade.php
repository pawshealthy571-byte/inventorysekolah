@extends('layouts.app')

@section('title', 'Dashboard Gudang Sekolah')
@section('eyebrow', 'Ringkasan Operasional Gudang')
@section('page_title', 'Dashboard Gudang Sekolah')
@section('page_subtitle', 'Pantau stok, mutasi barang, dan titik rawan inventaris dalam satu layar tanpa dependensi UI tambahan.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('barang.index') }}">Lihat Barang</a>
    <a class="button" href="{{ route('stock-movements.create') }}">Catat Mutasi</a>
@endsection

@section('content')
    @php
        $netMovement = $summary['incoming_this_month'] - $summary['outgoing_this_month'];
        $safeItems = max($summary['item_count'] - $summary['low_stock_count'], 0);
        $safeRate = $summary['item_count'] > 0 ? round(($safeItems / $summary['item_count']) * 100) : 0;
        $maxCategoryItems = max((int) ($categories->max('items_count') ?? 1), 1);
        $maxLocationItems = max((int) ($locations->max('items_count') ?? 1), 1);
    @endphp

    @if ($setupRequired ?? false)
        <section class="panel section-card notice-box">
            <div class="section-header">
                <div>
                    <div class="muted">Setup database diperlukan</div>
                    <h3 class="section-title">Tabel inventaris belum tersedia</h3>
                </div>
                <span class="badge badge-warning">Perlu migrate</span>
            </div>
            <p class="muted">
                Dashboard berhasil dibuka, tetapi tabel `categories`, `storage_locations`, `items`,
                atau `stock_movements` belum ada di database aktif. Jalankan migrasi lalu seed data contoh
                bila diperlukan.
            </p>
            <div class="code-block">php artisan migrate --seed</div>
        </section>
    @endif

    <section class="panel section-card" style="margin-bottom: 18px;">
        <div class="dashboard-grid">
            <div>
                <span class="eyebrow">Kesehatan Inventaris</span>
                <h3 class="section-title" style="margin-top: 18px; font-size: clamp(2rem, 4vw, 3.3rem);">Stok terpantau dengan prioritas yang jelas.</h3>
                <p class="page-copy" style="margin-top: 16px;">
                    Ringkasan ini memakai data kategori, lokasi, barang, dan mutasi stok yang tersimpan di sistem.
                    Fokus utamanya adalah area yang butuh tindakan cepat dan volume perpindahan barang bulan berjalan.
                </p>
            </div>

            <div class="grid-2">
                <article class="summary-card">
                    <span class="muted">Kesehatan stok aktif</span>
                    <strong>{{ $safeRate }}%</strong>
                    <p>{{ $safeItems }} dari {{ $summary['item_count'] }} barang masih berada di atas batas minimum.</p>
                </article>
                <article class="summary-card">
                    <span class="muted">Neraca mutasi bulan ini</span>
                    <strong>{{ $netMovement >= 0 ? '+' : '' }}{{ number_format($netMovement, 0, ',', '.') }}</strong>
                    <p>Per hari {{ now()->format('d/m/Y') }}.</p>
                </article>
            </div>
        </div>
    </section>

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
            <span class="muted">Mutasi bulan ini</span>
            <strong>{{ number_format($summary['incoming_this_month'] + $summary['outgoing_this_month'], 0, ',', '.') }}</strong>
            <p>{{ number_format($summary['incoming_this_month'], 0, ',', '.') }} masuk dan {{ number_format($summary['outgoing_this_month'], 0, ',', '.') }} keluar.</p>
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
                            <div class="inline-actions" style="margin-top: 12px;">
                                <a class="button-secondary" href="{{ route('barang.show', $item) }}">Detail</a>
                                <a class="button-ghost" href="{{ route('stock-movements.create', ['item' => $item->id]) }}">Mutasi</a>
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
                    <div class="muted">Kondisi inventaris</div>
                    <h3 class="section-title">Status Barang</h3>
                </div>
            </div>

            <div class="stack-list">
                @foreach ($conditionSummary as $condition)
                    <div class="stack-item">
                        <div class="list-top">
                            <strong>{{ $condition['label'] }}</strong>
                            <span class="badge {{ $condition['status'] === 'baik' ? 'badge-accent' : ($condition['status'] === 'perlu-perawatan' ? 'badge-warning' : 'badge-danger') }}">
                                {{ number_format($condition['count'], 0, ',', '.') }}
                            </span>
                        </div>
                        <p class="muted" style="margin: 12px 0 0;">
                            @if ($condition['status'] === 'baik')
                                Inventaris siap dipakai tanpa catatan perawatan khusus.
                            @elseif ($condition['status'] === 'perlu-perawatan')
                                Perlu dicek agar tidak mengganggu operasional harian.
                            @else
                                Prioritaskan evaluasi fisik dan rencana penggantian.
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        </article>
    </section>
@endsection
