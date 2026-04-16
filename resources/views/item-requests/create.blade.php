@extends('layouts.app')

@section('title', 'Permintaan Barang')
@section('eyebrow', 'Permintaan Barang')
@section('page_title', 'Ajukan Permintaan Barang')
@section('page_subtitle', 'Guru atau staf dapat mengajukan kebutuhan barang. Permintaan akan dicek terhadap stok layak pakai sebelum disetujui.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('permintaan-barang.index') }}">Lihat Riwayat</a>
    <a class="button-secondary" href="{{ route('barang.index') }}">Daftar Barang</a>
@endsection

@section('content')
    <section class="detail-grid">
        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Form pengajuan</div>
                    <h3 class="section-title">Input Permintaan Barang</h3>
                </div>
            </div>

            <form method="POST" action="{{ route('permintaan-barang.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="field-wide">
                        <label for="item_id">Barang</label>
                        <select class="select" id="item_id" name="item_id" required>
                            <option value="">Pilih barang</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" @selected((string) old('item_id') === (string) $item->id)>
                                    {{ $item->name }} | stok layak pakai {{ number_format($item->usableStock(), 0, ',', '.') }} {{ $item->unit }} | total {{ number_format($item->stock, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="requester_name">Nama peminta</label>
                        <input class="input" id="requester_name" name="requester_name" type="text" value="{{ old('requester_name', auth()->user()?->name) }}" required>
                    </div>

                    <div class="field">
                        <label for="quantity_requested">Jumlah diminta</label>
                        <input class="input" id="quantity_requested" name="quantity_requested" type="number" min="1" value="{{ old('quantity_requested', 1) }}" required>
                    </div>

                    <div class="field">
                        <label for="requested_at">Tanggal permintaan</label>
                        <input class="input" id="requested_at" name="requested_at" type="datetime-local" value="{{ old('requested_at', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="field-wide">
                        <label for="note">Catatan</label>
                        <textarea class="textarea" id="note" name="note" placeholder="Jelaskan kebutuhan barang">{{ old('note') }}</textarea>
                    </div>
                </div>

                <div class="button-row" style="margin-top: 18px;">
                    <button class="button" type="submit">Kirim Permintaan</button>
                    <a class="button-ghost" href="{{ route('permintaan-barang.index') }}">Batal</a>
                </div>
            </form>
        </article>

        <article class="panel section-card">
            <div class="section-header">
                <div>
                    <div class="muted">Panduan proses</div>
                    <h3 class="section-title">Logika Persetujuan</h3>
                </div>
            </div>

            <div class="stack-list">
                <div class="stack-item">
                    <strong>Status awal menunggu</strong>
                    <p class="muted" style="margin: 8px 0 0;">Semua pengajuan baru masuk ke antrean verifikasi inventaris.</p>
                </div>
                <div class="stack-item">
                    <strong>Jika disetujui</strong>
                    <p class="muted" style="margin: 8px 0 0;">Sistem akan mengurangi stok baik lebih dulu, lalu stok kurang baik bila masih diperlukan.</p>
                </div>
                <div class="stack-item">
                    <strong>Jika stok tidak cukup</strong>
                    <p class="muted" style="margin: 8px 0 0;">Permintaan tetap menunggu dan sistem menampilkan rekomendasi jumlah pembelian.</p>
                </div>
            </div>
        </article>
    </section>
@endsection
