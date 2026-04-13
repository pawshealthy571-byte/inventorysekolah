@extends('layouts.app')

@section('title', 'Edit Barang')
@section('eyebrow', 'Pembaruan Inventaris')
@section('page_title', 'Edit Data Barang')
@section('page_subtitle', 'Perbarui metadata barang tanpa mengubah stok langsung. Penyesuaian jumlah barang dilakukan lewat menu mutasi stok.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('barang.show', $item) }}">Kembali ke Detail</a>
    <a class="button" href="{{ route('stock-movements.create', ['item' => $item->id]) }}">Catat Mutasi</a>
@endsection

@section('content')
    <section class="panel section-card">
        <div class="section-header">
            <div>
                <div class="muted">Form pembaruan</div>
                <h3 class="section-title">{{ $item->name }}</h3>
            </div>
            <span class="badge {{ $item->isLowStock() ? 'badge-danger' : 'badge-accent' }}">
                Stok {{ number_format($item->stock, 0, ',', '.') }} {{ $item->unit }}
            </span>
        </div>

        <form method="POST" action="{{ route('barang.update', $item) }}">
            @csrf
            @method('PUT')

            @include('items._form')

            <div class="button-row" style="margin-top: 18px;">
                <button class="button" type="submit">Simpan Perubahan</button>
                <a class="button-ghost" href="{{ route('barang.show', $item) }}">Batal</a>
            </div>
        </form>
    </section>
@endsection
