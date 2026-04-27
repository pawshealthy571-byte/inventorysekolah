<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemRequestController extends Controller
{
    /**
     * Display the item request history.
     */
    public function index(): View
    {
        $requests = ItemRequest::query()
            ->with('item')
            ->latest('requested_at')
            ->latest('id')
            ->get();

        $pendingRequests = $requests->where('status', 'menunggu');

        return view('item-requests.index', [
            'requests' => $requests,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /**
     * Show the form for creating a new request.
     */
    public function create(): View
    {
        $items = Item::query()
            ->with(['category', 'location'])
            ->orderBy('name')
            ->get();

        return view('item-requests.create', [
            'items' => $items,
        ]);
    }

    /**
     * Store a new item request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'requester_name' => ['required', 'string', 'max:255'],
            'quantity_requested' => ['required', 'integer', 'min:1'],
            'requested_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $item = Item::query()->findOrFail($validated['item_id']);

        ItemRequest::query()->create([
            'item_id' => $item->id,
            'requester_name' => $validated['requester_name'],
            'quantity_requested' => $validated['quantity_requested'],
            'requested_at' => $validated['requested_at'],
            'status' => 'menunggu',
            'recommended_purchase_quantity' => $item->recommendedPurchaseQuantityFor((int) $validated['quantity_requested']),
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()
            ->route('permintaan-barang.index')
            ->with('status', 'Permintaan barang berhasil dikirim.');
    }

    /**
     * Review an existing request.
     */
    public function updateStatus(
        Request $request,
        ItemRequest $permintaan,
        StockMovementService $stockMovementService
    ): RedirectResponse {
        if (! $request->user()?->isAdmin() && ! $request->user()?->isSuperAdmin()) {
            abort(403, 'Hanya admin yang dapat memproses permintaan barang.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'review_note' => ['nullable', 'string'],
        ]);

        if (! $permintaan->isPending()) {
            return back()->withErrors([
                'status' => 'Permintaan ini sudah diproses dan tidak dapat diubah lagi.',
            ]);
        }

        $item = $permintaan->item()->firstOrFail();
        $recommendedPurchaseQuantity = $item->recommendedPurchaseQuantityFor($permintaan->quantity_requested);

        if ($validated['status'] === 'disetujui') {
            try {
                $stockMovementService->releaseForRequest($item, $permintaan->quantity_requested, [
                    'reference' => $this->requestReference($permintaan),
                    'actor' => $request->user()?->name ?? 'Petugas Inventaris',
                    'note' => 'Stok keluar otomatis dari persetujuan permintaan barang.',
                    'moved_at' => now(),
                ]);
            } catch (InsufficientStockException $exception) {
                $permintaan->update([
                    'recommended_purchase_quantity' => $recommendedPurchaseQuantity,
                ]);

                return back()->withErrors([
                    'status' => $exception->getMessage() . ' Rekomendasi pembelian: ' . $recommendedPurchaseQuantity . ' unit.',
                ]);
            }
        }

        $permintaan->update([
            'status' => $validated['status'],
            'recommended_purchase_quantity' => $recommendedPurchaseQuantity,
            'reviewer_name' => $request->user()?->name ?? 'Petugas Inventaris',
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'] ?? null,
        ]);

        return redirect()
            ->route('permintaan-barang.index')
            ->with('status', 'Status permintaan berhasil diperbarui.');
    }

    /**
     * Build the request reference used in stock movements.
     */
    private function requestReference(ItemRequest $itemRequest): string
    {
        return 'REQ-' . str_pad((string) $itemRequest->id, 5, '0', STR_PAD_LEFT);
    }
}
