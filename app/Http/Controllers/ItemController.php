<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\StorageLocation;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Display a listing of the items.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'category', 'location', 'status']);

        $items = Item::query()
            ->with(['category', 'location'])
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->search($term))
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['location'] ?? null, fn ($query, $locationId) => $query->where('storage_location_id', $locationId))
            ->when(($filters['status'] ?? null) === 'menipis', fn ($query) => $query->lowStock())
            ->when(($filters['status'] ?? null) === 'aman', fn ($query) => $query->whereColumn('stock', '>', 'minimum_stock'))
            ->orderBy('name')
            ->get();

        $categories = Category::query()->orderBy('name')->get();
        $locations = StorageLocation::query()->orderBy('name')->get();

        return view('items.index', compact('items', 'categories', 'locations', 'filters'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $locations = StorageLocation::query()->orderBy('name')->get();

        return view('items.create', compact('categories', 'locations'));
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request, StockMovementService $stockMovementService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:items,sku'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'storage_location_id' => ['nullable', 'exists:storage_locations,id'],
            'unit' => ['required', 'string', 'max:50'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'initial_stock' => ['required', 'integer', 'min:0'],
            'condition_status' => ['required', Rule::in(['baik', 'perlu-perawatan', 'rusak-ringan'])],
            'description' => ['nullable', 'string'],
        ]);

        $item = Item::query()->create([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'category_id' => $validated['category_id'] ?? null,
            'storage_location_id' => $validated['storage_location_id'] ?? null,
            'unit' => $validated['unit'],
            'minimum_stock' => $validated['minimum_stock'],
            'stock' => 0,
            'condition_status' => $validated['condition_status'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($validated['initial_stock'] > 0) {
            $stockMovementService->record($item, 'masuk', $validated['initial_stock'], [
                'reference' => 'STOK-AWAL',
                'actor' => 'Pengaturan awal',
                'note' => 'Stok awal saat data barang dibuat.',
                'moved_at' => now(),
            ]);
        }

        return redirect()
            ->route('barang.show', $item)
            ->with('status', 'Barang berhasil ditambahkan.');
    }

    /**
     * Display the specified item.
     */
    public function show(Item $barang): View
    {
        $barang->load(['category', 'location']);

        $movements = $barang->stockMovements()
            ->latest('moved_at')
            ->latest('id')
            ->limit(12)
            ->get();

        return view('items.show', [
            'item' => $barang,
            'movements' => $movements,
        ]);
    }

    /**
     * Show the form for editing the specified item.
     */
    public function edit(Item $barang): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $locations = StorageLocation::query()->orderBy('name')->get();

        return view('items.edit', [
            'item' => $barang,
            'categories' => $categories,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, Item $barang): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('items', 'sku')->ignore($barang->id)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'storage_location_id' => ['nullable', 'exists:storage_locations,id'],
            'unit' => ['required', 'string', 'max:50'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'condition_status' => ['required', Rule::in(['baik', 'perlu-perawatan', 'rusak-ringan'])],
            'description' => ['nullable', 'string'],
        ]);

        $barang->update($validated);

        return redirect()
            ->route('barang.show', $barang)
            ->with('status', 'Data barang berhasil diperbarui.');
    }

    /**
     * Remove the specified item.
     */
    public function destroy(Item $barang): RedirectResponse
    {
        $barang->delete();

        return redirect()
            ->route('barang.index')
            ->with('status', 'Barang berhasil dihapus.');
    }
}
