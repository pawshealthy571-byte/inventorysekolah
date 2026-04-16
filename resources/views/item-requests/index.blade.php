@extends('layouts.app')

@section('title', 'Riwayat Permintaan Barang')
@section('eyebrow', 'Tracking Permintaan')
@section('page_title', 'Permintaan Barang')
@section('page_subtitle', 'Pantau seluruh permintaan barang, status persetujuan, dan rekomendasi pembelian jika stok tidak mencukupi.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('barang.index') }}">Daftar Barang</a>
    <a class="button" href="{{ route('permintaan-barang.create') }}">Ajukan Permintaan</a>
@endsection

@section('content')
    <section class="stats-grid" style="margin-bottom: 18px;">
        <article class="panel stat-card">
            <span class="muted">Total permintaan</span>
            <strong>{{ number_format($requests->count(), 0, ',', '.') }}</strong>
            <p>Seluruh riwayat pengajuan yang tersimpan di sistem.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Menunggu</span>
            <strong>{{ number_format($pendingRequests->count(), 0, ',', '.') }}</strong>
            <p>Permintaan yang belum diproses.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Disetujui</span>
            <strong>{{ number_format($requests->where('status', 'disetujui')->count(), 0, ',', '.') }}</strong>
            <p>Permintaan yang sudah mengurangi stok otomatis.</p>
        </article>
        <article class="panel stat-card">
            <span class="muted">Perlu pembelian</span>
            <strong>{{ number_format($pendingRequests->where('recommended_purchase_quantity', '>', 0)->count(), 0, ',', '.') }}</strong>
            <p>Permintaan menunggu yang memicu rekomendasi restok.</p>
        </article>
    </section>

    <section class="panel section-card">
        <div class="section-header">
            <div>
                <div class="muted">Antrian review</div>
                <h3 class="section-title">Permintaan Menunggu Persetujuan</h3>
            </div>
        </div>

        @if ($pendingRequests->isEmpty())
            <div class="empty-state">Tidak ada permintaan yang sedang menunggu.</div>
        @else
            <div class="stack-list">
                @foreach ($pendingRequests as $requestItem)
                    <article class="stack-item">
                        <div class="list-top">
                            <div>
                                <strong>{{ $requestItem->item?->name ?? 'Barang tidak ditemukan' }}</strong>
                                <div class="meta">
                                    <span>{{ $requestItem->requester_name }}</span>
                                    <span>{{ $requestItem->requested_at->format('d/m/Y H:i') }}</span>
                                    <span>{{ number_format($requestItem->quantity_requested, 0, ',', '.') }} unit</span>
                                </div>
                            </div>
                            <span class="badge badge-warning">Menunggu</span>
                        </div>

                        <div class="meta" style="margin-top: 12px;">
                            <span>Stok layak pakai {{ number_format($requestItem->item?->usableStock() ?? 0, 0, ',', '.') }}</span>
                            @if ($requestItem->recommended_purchase_quantity > 0)
                                <span>Rekomendasi beli {{ number_format($requestItem->recommended_purchase_quantity, 0, ',', '.') }}</span>
                            @endif
                        </div>

                        @if ($requestItem->note)
                            <p class="muted" style="margin-top: 12px;">{{ $requestItem->note }}</p>
                        @endif

                        <div class="button-row" style="margin-top: 16px;">
                            <form method="POST" action="{{ route('permintaan-barang.update-status', $requestItem) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="disetujui">
                                <button class="button" type="submit">Setujui</button>
                            </form>

                            <form method="POST" action="{{ route('permintaan-barang.update-status', $requestItem) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="ditolak">
                                <button class="button-danger" type="submit">Tolak</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="panel section-card" style="margin-top: 18px;">
        <div class="section-header">
            <div>
                <div class="muted">Riwayat lengkap</div>
                <h3 class="section-title">Semua Permintaan Barang</h3>
            </div>
        </div>

        @if ($requests->isEmpty())
            <div class="empty-state">Belum ada permintaan barang yang tercatat.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Peminta</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $requestItem)
                            <tr>
                                <td>{{ $requestItem->item?->name ?? 'Barang tidak ditemukan' }}</td>
                                <td>{{ $requestItem->requester_name }}</td>
                                <td>{{ $requestItem->requested_at->format('d/m/Y H:i') }}</td>
                                <td>{{ number_format($requestItem->quantity_requested, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $requestItem->status === 'disetujui' ? 'badge-accent' : ($requestItem->status === 'ditolak' ? 'badge-danger' : 'badge-warning') }}">
                                        {{ $requestItem->statusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    {{ $requestItem->recommended_purchase_quantity > 0 ? number_format($requestItem->recommended_purchase_quantity, 0, ',', '.') . ' unit' : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
