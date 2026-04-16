<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    /**
     * Display the purchase history.
     */
    public function index(): View
    {
        $purchases = Purchase::query()
            ->with('item')
            ->latest('purchased_at')
            ->latest('id')
            ->get();

        $restockRecommendations = Item::query()
            ->with(['category', 'location'])
            ->get()
            ->map(fn (Item $item): array => [
                'item' => $item,
                'recommended_quantity' => $item->recommendedPurchaseQuantityFor(),
            ])
            ->filter(fn (array $entry): bool => $entry['recommended_quantity'] > 0)
            ->sortByDesc('recommended_quantity')
            ->values();

        return view('purchases.index', [
            'purchases' => $purchases,
            'restockRecommendations' => $restockRecommendations,
        ]);
    }

    /**
     * Show the form for recording a purchase.
     */
    public function create(): View
    {
        $items = Item::query()
            ->with(['category', 'location'])
            ->orderBy('name')
            ->get();

        return view('purchases.create', [
            'items' => $items,
        ]);
    }

    /**
     * Store a new purchase and add the stock to the good bucket.
     */
    public function store(Request $request, StockMovementService $stockMovementService): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'quantity_purchased' => ['required', 'integer', 'min:1'],
            'store_name' => ['required', 'string', 'max:255'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'purchaser_name' => ['nullable', 'string', 'max:255'],
            'purchased_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $item = Item::query()->findOrFail($validated['item_id']);
        $purchase = DB::transaction(function () use ($validated, $item, $stockMovementService): Purchase {
            $purchase = Purchase::query()->create([
                'item_id' => $item->id,
                'quantity_purchased' => (int) $validated['quantity_purchased'],
                'store_name' => $validated['store_name'],
                'unit_price' => $validated['unit_price'],
                'total_cost' => (int) $validated['quantity_purchased'] * (float) $validated['unit_price'],
                'purchaser_name' => $validated['purchaser_name'] ?? null,
                'purchased_at' => $validated['purchased_at'],
                'note' => $validated['note'] ?? null,
            ]);

            $stockMovementService->receivePurchase($item, (int) $validated['quantity_purchased'], [
                'reference' => $this->purchaseReference($purchase),
                'actor' => $validated['purchaser_name'] ?? 'Tim Pengadaan',
                'note' => 'Stok masuk otomatis dari transaksi pembelian.',
                'moved_at' => $validated['purchased_at'],
            ]);

            return $purchase;
        });

        return redirect()
            ->route('pembelian-barang.index')
            ->with('status', 'Pembelian barang berhasil dicatat dengan stok masuk otomatis.');
    }

    /**
     * Build the purchase reference used in stock movements.
     */
    private function purchaseReference(Purchase $purchase): string
    {
        return 'PO-' . str_pad((string) $purchase->id, 5, '0', STR_PAD_LEFT);
    }
}
