@extends('layouts.app')

@section('title', 'Catat Pembelian Barang')
@section('eyebrow', 'Pembelian Barang')
@section('page_title', 'Input Pembelian Barang')
@section('page_subtitle', 'Gunakan halaman ini saat stok habis atau kurang. Setelah pembelian disimpan, stok barang baik akan bertambah otomatis.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('pembelian-barang.index') }}">Riwayat Pembelian</a>
    <a class="button-secondary" href="{{ route('barang.index') }}">Daftar Barang</a>
@endsection

@section('content')
    <section class="detail-grid">
        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Form transaksi</div>
                    <h3 class="section-title">Catat Pembelian Baru</h3>
                </div>
            </div>

            <form method="POST" action="{{ route('pembelian-barang.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="field-wide">
                        <label for="item_id">Barang</label>
                        <select class="select" id="item_id" name="item_id" required>
                            <option value="">Pilih barang</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" @selected((string) old('item_id') === (string) $item->id)>
                                    {{ $item->name }} | stok total {{ number_format($item->stock, 0, ',', '.') }} | rekomendasi beli {{ number_format($item->recommendedPurchaseQuantityFor(), 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="quantity_purchased">Jumlah dibeli</label>
                        <input class="input" id="quantity_purchased" name="quantity_purchased" type="number" min="1" value="{{ old('quantity_purchased', 1) }}" required>
                    </div>

                    <div class="field">
                        <label for="store_name">Toko pembelian</label>
                        <input class="input" id="store_name" name="store_name" type="text" value="{{ old('store_name') }}" required>
                    </div>

                    <div class="field">
                        <label for="unit_price">Harga satuan</label>
                        <input class="input" id="unit_price" name="unit_price" type="number" min="0" step="0.01" value="{{ old('unit_price', 0) }}" required>
                    </div>

                    <div class="field">
                        <label for="purchaser_name">Pembeli</label>
                        <input class="input" id="purchaser_name" name="purchaser_name" type="text" value="{{ old('purchaser_name', auth()->user()?->name) }}">
                    </div>

                    <div class="field">
                        <label for="purchased_at">Tanggal pembelian</label>
                        <input class="input" id="purchased_at" name="purchased_at" type="datetime-local" value="{{ old('purchased_at', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="field-wide">
                        <label for="note">Catatan</label>
                        <textarea class="textarea" id="note" name="note" placeholder="Catatan pembelian">{{ old('note') }}</textarea>
                    </div>
                </div>

                <div class="button-row" style="margin-top: 18px;">
                    <button class="button" type="submit">Simpan Pembelian</button>
                    <a class="button-ghost" href="{{ route('pembelian-barang.index') }}">Batal</a>
                </div>
            </form>
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Aturan otomatis</div>
                    <h3 class="section-title">Yang Terjadi Setelah Simpan</h3>
                </div>
            </div>

            <div class="stack-list">
                <div class="stack-item">
                    <strong>Total biaya dihitung otomatis</strong>
                    <p class="muted" style="margin: 8px 0 0;">Sistem mengalikan jumlah pembelian dengan harga satuan.</p>
                </div>
                <div class="stack-item">
                    <strong>Stok barang baik bertambah</strong>
                    <p class="muted" style="margin: 8px 0 0;">Seluruh hasil pembelian masuk ke kategori barang baik secara default.</p>
                </div>
                <div class="stack-item">
                    <strong>Riwayat tetap sinkron</strong>
                    <p class="muted" style="margin: 8px 0 0;">Transaksi pembelian dan mutasi stok masuk tercatat dalam histori sistem.</p>
                </div>
            </div>
        </article>
    </section>
@endsection
