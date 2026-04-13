<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\StorageLocation;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the inventory dashboard.
     */
    public function index(): View
    {
        if (! $this->inventoryTablesExist()) {
            return view('dashboard', [
                'summary' => [
                    'item_count' => 0,
                    'category_count' => 0,
                    'location_count' => 0,
                    'total_stock' => 0,
                    'low_stock_count' => 0,
                    'incoming_this_month' => 0,
                    'outgoing_this_month' => 0,
                ],
                'lowStockItems' => collect(),
                'recentMovements' => collect(),
                'categories' => collect(),
                'locations' => collect(),
                'conditionSummary' => collect([
                    ['status' => 'baik', 'label' => 'Baik', 'count' => 0],
                    ['status' => 'perlu-perawatan', 'label' => 'Perlu Perawatan', 'count' => 0],
                    ['status' => 'rusak-ringan', 'label' => 'Rusak Ringan', 'count' => 0],
                ]),
                'setupRequired' => true,
            ]);
        }

        $currentMonth = now();
        $conditionCounts = Item::query()
            ->selectRaw('condition_status, count(*) as total')
            ->groupBy('condition_status')
            ->pluck('total', 'condition_status');

        $summary = [
            'item_count' => Item::query()->count(),
            'category_count' => Category::query()->count(),
            'location_count' => StorageLocation::query()->count(),
            'total_stock' => Item::query()->sum('stock'),
            'low_stock_count' => Item::query()->lowStock()->count(),
            'incoming_this_month' => StockMovement::query()
                ->where('type', 'masuk')
                ->whereBetween('moved_at', [$currentMonth->copy()->startOfMonth(), $currentMonth->copy()->endOfMonth()])
                ->sum('quantity'),
            'outgoing_this_month' => StockMovement::query()
                ->where('type', 'keluar')
                ->whereBetween('moved_at', [$currentMonth->copy()->startOfMonth(), $currentMonth->copy()->endOfMonth()])
                ->sum('quantity'),
        ];

        $lowStockItems = Item::query()
            ->with(['category', 'location'])
            ->lowStock()
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $recentMovements = StockMovement::query()
            ->with('item')
            ->latest('moved_at')
            ->latest('id')
            ->limit(8)
            ->get();

        $categories = Category::query()
            ->withCount('items')
            ->withSum('items as stock_total', 'stock')
            ->orderByDesc('items_count')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $locations = StorageLocation::query()
            ->withCount('items')
            ->withSum('items as stock_total', 'stock')
            ->orderByDesc('items_count')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $conditionSummary = collect([
            'baik',
            'perlu-perawatan',
            'rusak-ringan',
        ])->map(fn (string $status): array => [
            'status' => $status,
            'label' => $this->conditionLabel($status),
            'count' => (int) ($conditionCounts[$status] ?? 0),
        ]);
        $setupRequired = false;

        return view('dashboard', compact(
            'summary',
            'lowStockItems',
            'recentMovements',
            'categories',
            'locations',
            'conditionSummary',
            'setupRequired',
        ));
    }

    /**
     * Get the dashboard label for an item condition status.
     */
    private function conditionLabel(string $status): string
    {
        return match ($status) {
            'baik' => 'Baik',
            'perlu-perawatan' => 'Perlu Perawatan',
            'rusak-ringan' => 'Rusak Ringan',
            default => ucfirst(str_replace('-', ' ', $status)),
        };
    }

    /**
     * Determine whether all inventory tables required by the dashboard exist.
     */
    private function inventoryTablesExist(): bool
    {
        foreach (['categories', 'storage_locations', 'items', 'stock_movements'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }
}
