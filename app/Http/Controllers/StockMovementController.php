<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Item;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    /**
     * Show the form for creating a new stock movement.
     */
    public function create(Request $request): View
    {
        $items = Item::query()
            ->with(['category', 'location'])
            ->orderBy('name')
            ->get();

        $selectedItem = Item::query()->find($request->integer('item'));

        return view('stock-movements.create', compact('items', 'selectedItem'));
    }

    /**
     * Store a newly created stock movement.
     */
    public function store(Request $request, StockMovementService $stockMovementService): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'type' => ['required', Rule::in(['masuk', 'keluar'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'condition_bucket' => ['required', Rule::in(Item::conditionBuckets())],
            'reference' => ['nullable', 'string', 'max:255'],
            'actor' => ['nullable', 'string', 'max:255'],
            'moved_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $item = Item::query()->findOrFail($validated['item_id']);

        try {
            $stockMovementService->record($item, $validated['type'], (int) $validated['quantity'], [
                'condition_bucket' => $validated['condition_bucket'],
                'reference' => $validated['reference'] ?? null,
                'actor' => $validated['actor'] ?? null,
                'note' => $validated['note'] ?? null,
                'moved_at' => $validated['moved_at'],
            ]);
        } catch (InsufficientStockException $exception) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => $exception->getMessage()]);
        }

        return redirect()
            ->route('barang.show', $item)
            ->with('status', 'Mutasi stok berhasil disimpan.');
    }
}
