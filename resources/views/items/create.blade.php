@extends('layouts.app')

@section('title', 'Tambah Barang')
@section('eyebrow', 'Input Inventaris')
@section('page_title', 'Tambah Barang Baru')
@section('page_subtitle', 'Masukkan data barang, lokasi, kategori, dan stok awal agar inventaris langsung bisa dipantau dari dashboard.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('barang.index') }}">Lihat Daftar</a>
@endsection

@section('content')
    <section class="panel section-card">
        <div class="section-header">
            <div>
                <div class="muted">Form barang</div>
                <h3 class="section-title">Data Inventaris Baru</h3>
            </div>
        </div>

        <form method="POST" action="{{ route('barang.store') }}">
            @csrf

            @include('items._form')

            <div class="button-row" style="margin-top: 18px;">
                <button class="button" type="submit">Simpan Barang</button>
                <a class="button-ghost" href="{{ route('barang.index') }}">Batal</a>
            </div>
        </form>
    </section>
@endsection
