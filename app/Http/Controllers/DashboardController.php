<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\Purchase;
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
                'summary' => $this->emptySummary(),
                'lowStockItems' => collect(),
                'conditionSummary' => $this->emptyConditionSummary(),
                'pendingRequests' => collect(),
                'recentPurchases' => collect(),
                'setupRequired' => true,
            ]);
        }

        return view('dashboard', [
            'summary' => $this->summary(),
            'lowStockItems' => $this->lowStockItems(4),
            'conditionSummary' => $this->conditionSummary(),
            'pendingRequests' => $this->pendingRequests(4),
            'recentPurchases' => $this->recentPurchases(4),
            'setupRequired' => false,
        ]);
    }

    /**
     * Display detailed operational inventory information.
     */
    public function operational(): View
    {
        if (! $this->inventoryTablesExist()) {
            return view('dashboard.operasional', [
                'summary' => $this->emptySummary(),
                'lowStockItems' => collect(),
                'recentMovements' => collect(),
                'recentRequests' => collect(),
                'recentPurchases' => collect(),
                'categories' => collect(),
                'locations' => collect(),
                'conditionSummary' => $this->emptyConditionSummary(),
                'setupRequired' => true,
            ]);
        }

        return view('dashboard.operasional', [
            'summary' => $this->summary(),
            'lowStockItems' => $this->lowStockItems(),
            'recentMovements' => $this->recentMovements(),
            'recentRequests' => $this->recentRequests(),
            'recentPurchases' => $this->recentPurchases(),
            'categories' => $this->categories(),
            'locations' => $this->locations(),
            'conditionSummary' => $this->conditionSummary(),
            'setupRequired' => false,
        ]);
    }

    /**
     * Build the summary data used across dashboard pages.
     */
    private function summary(): array
    {
        $currentMonth = now();

        return [
            'item_count' => Item::query()->count(),
            'category_count' => Category::query()->count(),
            'location_count' => StorageLocation::query()->count(),
            'total_stock' => Item::query()->sum('stock'),
            'low_stock_count' => Item::query()->lowStock()->count(),
            'pending_request_count' => ItemRequest::query()->where('status', 'menunggu')->count(),
            'purchase_total_this_month' => (float) Purchase::query()
                ->whereBetween('purchased_at', [$currentMonth->copy()->startOfMonth(), $currentMonth->copy()->endOfMonth()])
                ->sum('total_cost'),
            'incoming_this_month' => StockMovement::query()
                ->where('type', 'masuk')
                ->whereBetween('moved_at', [$currentMonth->copy()->startOfMonth(), $currentMonth->copy()->endOfMonth()])
                ->sum('quantity'),
            'outgoing_this_month' => StockMovement::query()
                ->where('type', 'keluar')
                ->whereBetween('moved_at', [$currentMonth->copy()->startOfMonth(), $currentMonth->copy()->endOfMonth()])
                ->sum('quantity'),
        ];
    }

    /**
     * Build an empty summary for environments without inventory tables.
     */
    private function emptySummary(): array
    {
        return [
            'item_count' => 0,
            'category_count' => 0,
            'location_count' => 0,
            'total_stock' => 0,
            'low_stock_count' => 0,
            'pending_request_count' => 0,
            'purchase_total_this_month' => 0,
            'incoming_this_month' => 0,
            'outgoing_this_month' => 0,
        ];
    }

    /**
     * Get low stock items ordered by urgency.
     */
    private function lowStockItems(int $limit = 6)
    {
        return Item::query()
            ->with(['category', 'location'])
            ->lowStock()
            ->orderBy('stock')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent stock movements.
     */
    private function recentMovements()
    {
        return StockMovement::query()
            ->with('item')
            ->latest('moved_at')
            ->latest('id')
            ->limit(8)
            ->get();
    }

    /**
     * Get recent item requests.
     */
    private function recentRequests(int $limit = 6)
    {
        return ItemRequest::query()
            ->with('item')
            ->latest('requested_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the pending item requests.
     */
    private function pendingRequests(int $limit = 6)
    {
        return ItemRequest::query()
            ->with('item')
            ->where('status', 'menunggu')
            ->latest('requested_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent purchase transactions.
     */
    private function recentPurchases(int $limit = 6)
    {
        return Purchase::query()
            ->with('item')
            ->latest('purchased_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the busiest categories.
     */
    private function categories()
    {
        return Category::query()
            ->withCount('items')
            ->withSum('items as stock_total', 'stock')
            ->orderByDesc('items_count')
            ->orderBy('name')
            ->limit(6)
            ->get();
    }

    /**
     * Get the busiest storage locations.
     */
    private function locations()
    {
        return StorageLocation::query()
            ->withCount('items')
            ->withSum('items as stock_total', 'stock')
            ->orderByDesc('items_count')
            ->orderBy('name')
            ->limit(6)
            ->get();
    }

    /**
     * Build the condition summary for dashboard pages.
     */
    private function conditionSummary()
    {
        return collect([
            [
                'status' => 'baik',
                'label' => $this->conditionLabel('baik'),
                'count' => (int) Item::query()->sum('stock_good'),
            ],
            [
                'status' => 'kurang-baik',
                'label' => $this->conditionLabel('kurang-baik'),
                'count' => (int) Item::query()->sum('stock_less_good'),
            ],
            [
                'status' => 'rusak',
                'label' => $this->conditionLabel('rusak'),
                'count' => (int) Item::query()->sum('stock_damaged'),
            ],
        ]);
    }

    /**
     * Build the empty condition summary fallback.
     */
    private function emptyConditionSummary()
    {
        return collect($this->conditionStatuses())->map(fn (string $status): array => [
            'status' => $status,
            'label' => $this->conditionLabel($status),
            'count' => 0,
        ]);
    }

    /**
     * Supported inventory condition statuses.
     */
    private function conditionStatuses(): array
    {
        return [
            'baik',
            'kurang-baik',
            'rusak',
        ];
    }

    /**
     * Get the dashboard label for an item condition status.
     */
    private function conditionLabel(string $status): string
    {
        return match ($status) {
            'baik' => 'Baik',
            'kurang-baik' => 'Kurang Baik',
            'rusak' => 'Rusak',
            default => ucfirst(str_replace('-', ' ', $status)),
        };
    }

    /**
     * Determine whether all inventory tables required by the dashboard exist.
     */
    private function inventoryTablesExist(): bool
    {
        foreach (['categories', 'storage_locations', 'items', 'stock_movements', 'item_requests', 'purchases'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }
}
