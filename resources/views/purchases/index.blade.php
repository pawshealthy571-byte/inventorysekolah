@extends('layouts.app')

@section('title', 'Riwayat Pembelian Barang')
@section('eyebrow', 'Riwayat Pembelian')
@section('page_title', 'Pembelian Barang')
@section('page_subtitle', 'Lihat seluruh transaksi pembelian, total biaya, dan rekomendasi restok untuk barang yang stoknya menipis.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('permintaan-barang.index') }}">Permintaan Barang</a>
    <a class="button" href="{{ route('pembelian-barang.create') }}">Tambah Pembelian</a>
@endsection

@section('content')
    <section class="stats-grid" style="margin-bottom: 18px;">
        <article class="panel stat-card">
            <span class="muted">Total transaksi</span>
            <strong>{{ number_format($purchases->count(), 0, ',', '.') }}</strong>
            <p>Semua pembelian yang tercatat di sistem.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Unit dibeli</span>
            <strong>{{ number_format($purchases->sum('quantity_purchased'), 0, ',', '.') }}</strong>
            <p>Akumulasi barang masuk dari pembelian.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Total biaya</span>
            <strong>Rp{{ number_format((float) $purchases->sum('total_cost'), 0, ',', '.') }}</strong>
            <p>Nilai belanja yang sudah dikeluarkan.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Butuh restok</span>
            <strong>{{ number_format($restockRecommendations->count(), 0, ',', '.') }}</strong>
            <p>Barang yang saat ini masih memerlukan pembelian.</p>
        </article>
    </section>

    <section class="detail-grid">
        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Prioritas pengadaan</div>
                    <h3 class="section-title">Rekomendasi Pembelian</h3>
                </div>
            </div>

            @if ($restockRecommendations->isEmpty())
                <div class="empty-state">Belum ada barang yang membutuhkan restok tambahan.</div>
            @else
                <div class="stack-list">
                    @foreach ($restockRecommendations as $entry)
                        <div class="stack-item">
                            <div class="list-top">
                                <div>
                                    <strong>{{ $entry['item']->name }}</strong>
                                    <div class="meta">
                                        <span>Stok total {{ number_format($entry['item']->stock, 0, ',', '.') }}</span>
                                        <span>Minimum {{ number_format($entry['item']->minimum_stock, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <span class="badge badge-danger">{{ number_format($entry['recommended_quantity'], 0, ',', '.') }} unit</span>
                            </div>
                            <div class="button-row" style="margin-top: 14px;">
                                <a class="button-secondary" href="{{ route('barang.show', $entry['item']) }}">Detail</a>
                                <a class="button" href="{{ route('pembelian-barang.create') }}">Beli Sekarang</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Riwayat lengkap</div>
                    <h3 class="section-title">Daftar Pembelian</h3>
                </div>
            </div>

            @if ($purchases->isEmpty())
                <div class="empty-state">Belum ada pembelian yang tercatat.</div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Toko</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->item?->name ?? 'Barang tidak ditemukan' }}</td>
                                    <td>{{ $purchase->store_name }}</td>
                                    <td>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ number_format($purchase->quantity_purchased, 0, ',', '.') }}</td>
                                    <td>Rp{{ number_format((float) $purchase->unit_price, 0, ',', '.') }}</td>
                                    <td>Rp{{ number_format((float) $purchase->total_cost, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    </section>
@endsection
