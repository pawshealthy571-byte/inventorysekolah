@extends('layouts.app')

@section('title', 'Mutasi Stok')
@section('eyebrow', 'Pergerakan Barang')
@section('page_title', 'Catat Mutasi Stok')
@section('page_subtitle', 'Gunakan halaman ini untuk mencatat barang masuk dan keluar agar stok selalu sinkron dengan histori transaksi.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('barang.index') }}">Lihat Barang</a>
@endsection

@section('content')
    <section class="detail-grid">
        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Form mutasi</div>
                    <h3 class="section-title">Input Barang Masuk atau Keluar</h3>
                </div>
            </div>

            <form method="POST" action="{{ route('stock-movements.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="field-wide">
                        <label for="item_id">Barang</label>
                        <select class="select" id="item_id" name="item_id" required>
                            <option value="">Pilih barang</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" @selected((string) old('item_id', $selectedItem?->id) === (string) $item->id)>
                                    {{ $item->name }} | SKU {{ $item->sku }} | stok {{ number_format($item->stock, 0, ',', '.') }} {{ $item->unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="type">Jenis mutasi</label>
                        <select class="select" id="type" name="type" required>
                            <option value="masuk" @selected(old('type') === 'masuk')>Barang masuk</option>
                            <option value="keluar" @selected(old('type') === 'keluar')>Barang keluar</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="quantity">Jumlah</label>
                        <input class="input" id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required>
                    </div>

                    <div class="field">
                        <label for="condition_bucket">Kondisi stok</label>
                        <select class="select" id="condition_bucket" name="condition_bucket" required>
                            <option value="baik" @selected(old('condition_bucket', 'baik') === 'baik')>Barang baik</option>
                            <option value="kurang-baik" @selected(old('condition_bucket') === 'kurang-baik')>Barang kurang baik</option>
                            <option value="rusak" @selected(old('condition_bucket') === 'rusak')>Barang rusak</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="reference">Referensi</label>
                        <input class="input" id="reference" name="reference" type="text" value="{{ old('reference') }}" placeholder="Contoh: PO-2026-ATK-01">
                    </div>

                    <div class="field">
                        <label for="actor">Pelaksana</label>
                        <input class="input" id="actor" name="actor" type="text" value="{{ old('actor') }}" placeholder="Contoh: Tim Pengadaan">
                    </div>

                    <div class="field">
                        <label for="moved_at">Tanggal dan waktu</label>
                        <input class="input" id="moved_at" name="moved_at" type="datetime-local" value="{{ old('moved_at', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="field-wide">
                        <label for="note">Catatan</label>
                        <textarea class="textarea" id="note" name="note" placeholder="Catatan tambahan untuk histori mutasi">{{ old('note') }}</textarea>
                    </div>
                </div>

                <div class="button-row" style="margin-top: 18px;">
                    <button class="button" type="submit">Simpan Mutasi</button>
                    <a class="button-ghost" href="{{ $selectedItem ? route('barang.show', $selectedItem) : route('dashboard') }}">Batal</a>
                </div>
            </form>
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Konteks barang</div>
                    <h3 class="section-title">Ringkasan Item Terpilih</h3>
                </div>
            </div>

            @if ($selectedItem)
                <div class="detail-list">
                    <div class="detail-row">
                        <strong>Nama</strong>
                        <div>{{ $selectedItem->name }}</div>
                    </div>
                    <div class="detail-row">
                        <strong>SKU</strong>
                        <div>{{ $selectedItem->sku }}</div>
                    </div>
                    <div class="detail-row">
                        <strong>Stok saat ini</strong>
                        <div>{{ number_format($selectedItem->stock, 0, ',', '.') }} {{ $selectedItem->unit }}</div>
                    </div>
                    <div class="detail-row">
                        <strong>Barang baik</strong>
                        <div>{{ number_format($selectedItem->stock_good, 0, ',', '.') }} {{ $selectedItem->unit }}</div>
                    </div>
                    <div class="detail-row">
                        <strong>Kurang baik</strong>
                        <div>{{ number_format($selectedItem->stock_less_good, 0, ',', '.') }} {{ $selectedItem->unit }}</div>
                    </div>
                    <div class="detail-row">
                        <strong>Rusak</strong>
                        <div>{{ number_format($selectedItem->stock_damaged, 0, ',', '.') }} {{ $selectedItem->unit }}</div>
                    </div>
                    <div class="detail-row">
                        <strong>Batas minimum</strong>
                        <div>{{ number_format($selectedItem->minimum_stock, 0, ',', '.') }} {{ $selectedItem->unit }}</div>
                    </div>
                    <div class="detail-row">
                        <strong>Rekomendasi beli</strong>
                        <div>{{ number_format($selectedItem->recommendedPurchaseQuantityFor(), 0, ',', '.') }} {{ $selectedItem->unit }}</div>
                    </div>
                </div>

                <div class="button-row" style="margin-top: 18px;">
                    <a class="button-secondary" href="{{ route('barang.show', $selectedItem) }}">Lihat Detail Barang</a>
                </div>
            @else
                <div class="empty-state">
                    Pilih barang dari daftar untuk melihat konteks stok sebelum menyimpan mutasi.
                </div>
            @endif
        </article>
    </section>
@endsection
