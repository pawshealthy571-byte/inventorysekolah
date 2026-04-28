@extends('layouts.app')

@section('title', 'Laporan Pengeluaran Pembelian')
@section('page_title', 'Laporan Pengeluaran')
@section('page_subtitle', 'Analisis pengeluaran dana untuk pembelian inventaris sekolah.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('pembelian-barang.index') }}">Kembali ke Pembelian</a>
@endsection

@section('content')
    <section class="panel section-card" style="margin-bottom: 20px;">
        <div class="section-header">
            <div>
                <h3 class="section-title">Filter Laporan</h3>
                <p class="muted">Pilih periode laporan yang ingin ditampilkan.</p>
            </div>
        </div>

        <form action="{{ route('laporan-pengeluaran.index') }}" method="GET" class="filter-form-custom">
            <div class="report-filter-tabs">
                <label class="tab-item">
                    <input type="radio" name="filter" value="hari" {{ $filterType == 'hari' ? 'checked' : '' }} onchange="this.form.submit()">
                    <span>Harian</span>
                </label>
                <label class="tab-item">
                    <input type="radio" name="filter" value="bulan" {{ $filterType == 'bulan' ? 'checked' : '' }} onchange="this.form.submit()">
                    <span>Bulanan</span>
                </label>
                <label class="tab-item">
                    <input type="radio" name="filter" value="tahun" {{ $filterType == 'tahun' ? 'checked' : '' }} onchange="this.form.submit()">
                    <span>Tahunan</span>
                </label>
            </div>

            <div class="filter-inputs" style="margin-top: 15px; display: flex; gap: 10px; align-items: flex-end;">
                @if($filterType == 'hari')
                    <div class="field">
                        <label>Pilih Tanggal</label>
                        <input type="date" name="date" class="input" value="{{ $filterDate ?? date('Y-m-d') }}">
                    </div>
                @elseif($filterType == 'bulan')
                    <div class="field">
                        <label>Bulan</label>
                        <select name="month" class="select">
                            @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ $filterMonth == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="field">
                        <label>Tahun</label>
                        <select name="year" class="select">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="field">
                        <label>Pilih Tahun</label>
                        <select name="year" class="select">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit" class="button">Tampilkan</button>
            </div>
        </form>
    </section>

    <section class="stats-grid" style="margin-bottom: 20px;">
        <article class="panel stat-card">
            <span class="muted">Periode</span>
            <strong>{{ $periodLabel }}</strong>
        </article>
        <article class="panel stat-card">
            <span class="muted">Total Transaksi</span>
            <strong>{{ $totalTransactions }}</strong>
        </article>
        <article class="panel stat-card">
            <span class="muted">Unit Dibeli</span>
            <strong>{{ number_format($totalItems, 0, ',', '.') }}</strong>
        </article>
        <article class="panel stat-card" style="border-left: 4px solid var(--accent);">
            <span class="muted">Total Pengeluaran</span>
            <strong style="color: var(--accent);">Rp{{ number_format($totalCost, 0, ',', '.') }}</strong>
        </article>
    </section>

    <div class="report-actions-bar" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <a href="{{ route('laporan-pengeluaran.excel', request()->query()) }}" class="button-secondary" style="background: #10b981; color: white; border: none;">
            <svg style="width: 16px; height: 16px; margin-right: 5px;" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1.8 18H14l-2-3.2-2 3.2H8.2l2.9-4.5L8.2 11H10l2 3.2 2-3.2h1.8l-2.9 4.5 2.9 4.5zM13 9V3.5L18.5 9H13z"/></svg>
            Excel
        </a>
        <a href="{{ route('laporan-pengeluaran.pdf', request()->query()) }}" target="_blank" class="button-secondary" style="background: #ef4444; color: white; border: none;">
            <svg style="width: 16px; height: 16px; margin-right: 5px;" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm.2 16h-1.4l-.8-2.6h-2l-.8 2.6H7.8l2.2-6.5h1.4l2.2 6.5zM13 9V3.5L18.5 9H13zm-3.1 7.2h1.2l-.6-2-0.6 2z"/></svg>
            PDF / Print
        </a>
    </div>

    <section class="panel section-card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Toko</th>
                        <th style="text-align: right;">Jumlah</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $p)
                        <tr>
                            <td>{{ $p->purchased_at->format('d/m/Y') }}</td>
                            <td><strong>{{ $p->item?->name }}</strong></td>
                            <td>{{ $p->store_name }}</td>
                            <td style="text-align: right;">{{ number_format($p->quantity_purchased, 0, ',', '.') }}</td>
                            <td style="text-align: right;">Rp{{ number_format($p->unit_price, 0, ',', '.') }}</td>
                            <td style="text-align: right;"><strong>Rp{{ number_format($p->total_cost, 0, ',', '.') }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">Tidak ada data pembelian untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($purchases->isNotEmpty())
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: bold;">
                            <td colspan="3" style="text-align: right;">TOTAL</td>
                            <td style="text-align: right;">{{ number_format($totalItems, 0, ',', '.') }}</td>
                            <td></td>
                            <td style="text-align: right; color: var(--accent); font-size: 1.1rem;">Rp{{ number_format($totalCost, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>

    <style>
        .filter-form-custom .report-filter-tabs {
            display: flex;
            background: #f1f5f9;
            padding: 5px;
            border-radius: 10px;
            width: fit-content;
        }
        .filter-form-custom .tab-item {
            cursor: pointer;
        }
        .filter-form-custom .tab-item input {
            display: none;
        }
        .filter-form-custom .tab-item span {
            padding: 8px 20px;
            display: inline-block;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.2s;
        }
        .filter-form-custom .tab-item input:checked + span {
            background: white;
            color: var(--accent);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
@endsection
