<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\RolePermission;
use App\Models\StorageLocation;
use App\Services\ItemCreationService;
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
        $filters = $request->only(['q', 'location', 'status']);

        $items = Item::query()
            ->with(['location'])
            ->withCount([
                'itemRequests as pending_requests_count' => fn ($query) => $query->where('status', 'menunggu'),
            ])
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->search($term))
            ->when($filters['location'] ?? null, fn ($query, $locationId) => $query->where('storage_location_id', $locationId))
            ->when(($filters['status'] ?? null) === 'menipis', fn ($query) => $query->lowStock())
            ->when(($filters['status'] ?? null) === 'aman', fn ($query) => $query->whereColumn('stock', '>', 'minimum_stock'))
            ->orderBy('name')
            ->get();

        $locations = StorageLocation::query()->orderBy('name')->get();

        return view('items.index', compact('items', 'locations', 'filters'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create(): View
    {
        $locations = StorageLocation::query()->orderBy('name')->get();

        return view('items.create', [
            'locations' => $locations,
            'canUseAssistant' => auth()->user()?->hasPermission(RolePermission::PERMISSION_ASSISTANT_USE) ?? false,
        ]);
    }

    /**
     * Store a newly created item.
     */
    public function store(
        Request $request,
        ItemCreationService $itemCreationService,
        StockMovementService $stockMovementService,
    ): RedirectResponse
    {
        $validated = $itemCreationService->validate($request->all());
        $item = $itemCreationService->create($validated, $stockMovementService);

        return redirect()
            ->route('barang.show', $item)
            ->with('status', 'Barang berhasil ditambahkan.');
    }

    /**
     * Display the specified item.
     */
    public function show(Item $barang): View
    {
        $barang->load(['location']);

        $movements = $barang->stockMovements()
            ->latest('moved_at')
            ->latest('id')
            ->limit(12)
            ->get();

        $requests = $barang->itemRequests()
            ->latest('requested_at')
            ->latest('id')
            ->limit(6)
            ->get();

        $purchases = $barang->purchases()
            ->latest('purchased_at')
            ->latest('id')
            ->limit(6)
            ->get();

        return view('items.show', [
            'item' => $barang,
            'movements' => $movements,
            'requests' => $requests,
            'purchases' => $purchases,
        ]);
    }

    /**
     * Show the form for editing the specified item.
     */
    public function edit(Item $barang): View
    {
        $locations = StorageLocation::query()->orderBy('name')->get();

        return view('items.edit', [
            'item' => $barang,
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
            'storage_location_id' => ['nullable', 'exists:storage_locations,id'],
            'unit' => ['required', 'string', 'max:50'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['condition_status'] = $barang->dominantConditionStatus();
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
