<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseReportController extends Controller
{
    /**
     * Display the purchase expenditure report.
     */
    public function index(Request $request): View
    {
        $filterType = $request->get('filter', 'bulan');
        $filterDate = $request->get('date');
        $filterMonth = $request->get('month');
        $filterYear = $request->get('year', now()->year);

        $query = Purchase::query()->with('item');

        // Apply filters
        switch ($filterType) {
            case 'hari':
                $date = $filterDate ? Carbon::parse($filterDate) : now();
                $query->whereDate('purchased_at', $date);
                $periodLabel = 'Tanggal ' . $date->translatedFormat('d F Y');
                break;

            case 'bulan':
                $month = $filterMonth ?: now()->month;
                $year = $filterYear ?: now()->year;
                $query->whereMonth('purchased_at', $month)
                      ->whereYear('purchased_at', $year);
                $periodDate = Carbon::createFromDate($year, $month, 1);
                $periodLabel = $periodDate->translatedFormat('F Y');
                break;

            case 'tahun':
                $year = $filterYear ?: now()->year;
                $query->whereYear('purchased_at', $year);
                $periodLabel = 'Tahun ' . $year;
                break;

            default:
                $periodLabel = 'Semua Periode';
                break;
        }

        $purchases = $query->latest('purchased_at')->latest('id')->get();

        // Data for chart
        $chartYear = $filterYear ?: now()->year;
        $monthlyData = Purchase::query()
            ->selectRaw('MONTH(purchased_at) as month, SUM(total_cost) as total')
            ->whereYear('purchased_at', $chartYear)
            ->groupByRaw('MONTH(purchased_at)')
            ->orderByRaw('MONTH(purchased_at)')
            ->pluck('total', 'month')
            ->toArray();

        $chartLabels = [];
        $chartValues = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartLabels[] = Carbon::createFromDate($chartYear, $m, 1)->translatedFormat('M');
            $chartValues[] = (float) ($monthlyData[$m] ?? 0);
        }

        $availableYears = Purchase::query()
            ->selectRaw('YEAR(purchased_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        return view('purchases.report', [
            'purchases' => $purchases,
            'filterType' => $filterType,
            'filterDate' => $filterDate,
            'filterMonth' => $filterMonth ?: now()->month,
            'filterYear' => $filterYear ?: now()->year,
            'periodLabel' => $periodLabel,
            'totalCost' => $purchases->sum('total_cost'),
            'totalItems' => $purchases->sum('quantity_purchased'),
            'totalTransactions' => $purchases->count(),
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'availableYears' => $availableYears,
        ]);
    }

    /**
     * Export the report to Excel (CSV).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $purchases = $this->getFilteredPurchases($request);
        $appName = Setting::getValue('app_name', 'Inventory Sekolah');
        $fileName = 'laporan-pengeluaran-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($purchases, $appName) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

            fputcsv($handle, [$appName . ' - Laporan Pengeluaran'], ';');
            fputcsv($handle, ['Tanggal Cetak: ' . now()->translatedFormat('d F Y H:i')], ';');
            fputcsv($handle, [], ';');

            fputcsv($handle, ['No', 'Tanggal', 'Barang', 'Toko', 'Jumlah', 'Harga Satuan', 'Total Biaya', 'Pembeli', 'Catatan'], ';');

            $no = 1;
            $grandTotal = 0;
            foreach ($purchases as $purchase) {
                fputcsv($handle, [
                    $no++,
                    $purchase->purchased_at->format('d/m/Y H:i'),
                    $purchase->item?->name ?? 'N/A',
                    $purchase->store_name,
                    $purchase->quantity_purchased,
                    number_format((float) $purchase->unit_price, 0, ',', '.'),
                    number_format((float) $purchase->total_cost, 0, ',', '.'),
                    $purchase->purchaser_name ?? '-',
                    $purchase->note ?? '-',
                ], ';');
                $grandTotal += (float) $purchase->total_cost;
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['', '', '', '', '', 'GRAND TOTAL:', 'Rp' . number_format($grandTotal, 0, ',', '.')], ';');
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Display print/PDF view.
     */
    public function exportPdf(Request $request): View
    {
        $purchases = $this->getFilteredPurchases($request);
        $appName = Setting::getValue('app_name', 'Inventory Sekolah');
        
        return view('purchases.report-print', [
            'purchases' => $purchases,
            'periodLabel' => $this->getPeriodLabel($request),
            'totalCost' => $purchases->sum('total_cost'),
            'totalItems' => $purchases->sum('quantity_purchased'),
            'totalTransactions' => $purchases->count(),
            'appName' => $appName,
        ]);
    }

    private function getFilteredPurchases(Request $request)
    {
        $filterType = $request->get('filter', 'bulan');
        $filterDate = $request->get('date');
        $filterMonth = $request->get('month');
        $filterYear = $request->get('year', now()->year);

        $query = Purchase::query()->with('item');

        if ($filterType === 'hari') {
            $query->whereDate('purchased_at', $filterDate ?: now());
        } elseif ($filterType === 'bulan') {
            $query->whereMonth('purchased_at', $filterMonth ?: now()->month)
                  ->whereYear('purchased_at', $filterYear ?: now()->year);
        } elseif ($filterType === 'tahun') {
            $query->whereYear('purchased_at', $filterYear ?: now()->year);
        }

        return $query->latest('purchased_at')->get();
    }

    private function getPeriodLabel(Request $request): string
    {
        $filterType = $request->get('filter', 'bulan');
        $filterDate = $request->get('date');
        $filterMonth = $request->get('month');
        $filterYear = $request->get('year', now()->year);

        if ($filterType === 'hari') {
            return 'Tanggal ' . Carbon::parse($filterDate ?: now())->translatedFormat('d F Y');
        } elseif ($filterType === 'bulan') {
            return Carbon::createFromDate($filterYear ?: now()->year, $filterMonth ?: now()->month, 1)->translatedFormat('F Y');
        } elseif ($filterType === 'tahun') {
            return 'Tahun ' . ($filterYear ?: now()->year);
        }
        return 'Semua Periode';
    }
}
