<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ItemCreationService
{
    /**
     * Validate incoming item payload.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(array $input): array
    {
        return Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:items,sku'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'storage_location_id' => ['nullable', 'exists:storage_locations,id'],
            'unit' => ['required', 'string', 'max:50'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'initial_stock_good' => ['required', 'integer', 'min:0'],
            'initial_stock_less_good' => ['required', 'integer', 'min:0'],
            'initial_stock_damaged' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ])->validate();
    }

    /**
     * Create an item and record its opening stock.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated, StockMovementService $stockMovementService): Item
    {
        $initialStocks = [
            'baik' => (int) $validated['initial_stock_good'],
            'kurang-baik' => (int) $validated['initial_stock_less_good'],
            'rusak' => (int) $validated['initial_stock_damaged'],
        ];

        $item = Item::query()->create([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'category_id' => $validated['category_id'] ?? null,
            'storage_location_id' => $validated['storage_location_id'] ?? null,
            'unit' => $validated['unit'],
            'minimum_stock' => $validated['minimum_stock'],
            'stock' => 0,
            'stock_good' => 0,
            'stock_less_good' => 0,
            'stock_damaged' => 0,
            'condition_status' => collect($initialStocks)
                ->sortDesc()
                ->keys()
                ->first() ?? 'baik',
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($initialStocks as $condition => $quantity) {
            if ($quantity < 1) {
                continue;
            }

            $stockMovementService->record($item, 'masuk', $quantity, [
                'condition_bucket' => $condition,
                'reference' => 'STOK-AWAL',
                'actor' => 'Pengaturan awal',
                'note' => 'Stok awal saat data barang dibuat.',
                'moved_at' => now(),
            ]);
        }

        return $item;
    }
}
