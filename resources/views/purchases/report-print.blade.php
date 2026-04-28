<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran - {{ $periodLabel }}</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.4; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { padding: 5px 0; }
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .main-table th { background: #f2f2f2; border: 1px solid #ccc; padding: 10px; text-align: left; font-size: 12px; }
        .main-table td { border: 1px solid #ccc; padding: 10px; font-size: 12px; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; }
        .footer-table { width: 100%; }
        .footer-table td { width: 33%; text-align: center; vertical-align: top; }
        .sign-space { margin-top: 60px; border-bottom: 1px solid #333; width: 150px; margin-left: auto; margin-right: auto; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #f97316; color: white; border: none; border-radius: 5px;">Cetak Laporan</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background: #64748b; color: white; border: none; border-radius: 5px;">Tutup</button>
    </div>

    <div class="header">
        <h1>{{ $appName }}</h1>
        <p>Laporan Pengeluaran Pembelian Barang</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="150">Periode Laporan</td>
                <td>: <strong>{{ $periodLabel }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Cetak</td>
                <td>: {{ date('d F Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Toko</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($purchases as $p)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $p->purchased_at->format('d/m/Y') }}</td>
                    <td>{{ $p->item?->name }}</td>
                    <td>{{ $p->store_name }}</td>
                    <td class="text-right">{{ number_format($p->quantity_purchased, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($p->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($p->total_cost, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f2f2f2;">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($totalItems, 0, ',', '.') }}</td>
                <td></td>
                <td class="text-right">Rp{{ number_format($totalCost, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    Menyetujui,<br><br>
                    <div class="sign-space"></div>
                    Kepala Sekolah
                </td>
                <td></td>
                <td>
                    Dibuat Oleh,<br><br>
                    <div class="sign-space"></div>
                    Petugas Inventaris
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
