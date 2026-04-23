@extends('layouts.app')

@section('title', 'Daftar Barang')
@section('eyebrow', 'Inventaris Sekolah')
@section('page_title', 'Daftar Barang')
@section('page_subtitle', 'Filter inventaris berdasarkan nama, kategori, lokasi, dan status stok untuk melihat kondisi gudang dengan cepat.')

@section('page_actions')
    <a class="button" href="{{ route('barang.create') }}">Tambah Barang</a>
@endsection

@section('content')
    <section class="panel section-card" style="margin-bottom: 18px;">
        <div class="section-header">
            <div>
                <div class="muted">Filter pencarian</div>
                <h3 class="section-title">Cari dan Kelompokkan Data Barang</h3>
            </div>
        </div>

        <form class="filter-form" method="GET" action="{{ route('barang.index') }}">
            <div class="field">
                <label for="q">Nama atau SKU</label>
                <input class="input" id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: ATK-001 atau Proyektor">
            </div>

            <div class="field">
                <label for="category">Kategori</label>
                <select class="select" id="category" name="category">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="location">Lokasi</label>
                <select class="select" id="location" name="location">
                    <option value="">Semua lokasi</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) ($filters['location'] ?? '') === (string) $location->id)>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select class="select" id="status" name="status">
                    <option value="">Semua status</option>
                    <option value="aman" @selected(($filters['status'] ?? '') === 'aman')>Stok aman</option>
                    <option value="menipis" @selected(($filters['status'] ?? '') === 'menipis')>Stok menipis</option>
                </select>
            </div>

            <div class="button-row">
                <button class="button" type="submit">Terapkan</button>
                <a class="button-ghost" href="{{ route('barang.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="stats-grid" style="margin-bottom: 18px;">
        <article class="panel stat-card">
            <span class="muted">Baris tampil</span>
            <strong>{{ number_format($items->count(), 0, ',', '.') }}</strong>
            <p>Jumlah barang setelah filter diterapkan.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Stok total</span>
            <strong>{{ number_format($items->sum('stock'), 0, ',', '.') }}</strong>
            <p>Akumulasi stok dari hasil filter aktif.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Stok menipis</span>
            <strong>{{ number_format($items->filter(fn ($item) => $item->isLowStock())->count(), 0, ',', '.') }}</strong>
            <p>Barang yang perlu segera ditindak lanjuti.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Kategori tercakup</span>
            <strong>{{ number_format($items->pluck('category_id')->filter()->unique()->count(), 0, ',', '.') }}</strong>
            <p>Jumlah kategori yang muncul di hasil pencarian.</p>
        </article>
    </section>

    <section class="panel section-card">
        <div class="section-header">
            <div>
                <div class="muted">Data inventaris</div>
                <h3 class="section-title">Tabel Barang</h3>
            </div>
            <span class="badge badge-accent">{{ number_format($items->count(), 0, ',', '.') }} item</span>
        </div>

        @if ($items->isEmpty())
            <div class="empty-state">Tidak ada barang yang cocok dengan filter saat ini.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Stok</th>
                            <th>Kondisi Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    <div class="meta">
                                        <span>SKU {{ $item->sku }}</span>
                                        <span>{{ $item->unit }}</span>
                                    </div>
                                </td>
                                <td>{{ $item->category?->name ?? 'Tanpa kategori' }}</td>
                                <td>{{ $item->location?->name ?? 'Belum diatur' }}</td>
                                <td>
                                    <span class="badge {{ $item->isLowStock() ? 'badge-danger' : 'badge-accent' }}">
                                        {{ number_format($item->stock, 0, ',', '.') }} / min {{ number_format($item->minimum_stock, 0, ',', '.') }}
                                    </span>
                                    @if ($item->pending_requests_count > 0)
                                        <div class="meta">
                                            <span>{{ number_format($item->pending_requests_count, 0, ',', '.') }} permintaan menunggu</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="meta" style="margin-top: 0;">
                                        @foreach ($item->stockBreakdown() as $breakdown)
                                            <span class="badge {{ $breakdown['key'] === 'baik' ? 'badge-accent' : ($breakdown['key'] === 'kurang-baik' ? 'badge-warning' : 'badge-danger') }}">
                                                {{ $breakdown['label'] }} {{ number_format($breakdown['stock'], 0, ',', '.') }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="inline-actions">
                                        <a class="button-secondary" href="{{ route('barang.show', $item) }}">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
