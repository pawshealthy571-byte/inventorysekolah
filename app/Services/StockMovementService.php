<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    /**
     * Record a stock movement and update the current item stock.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function record(Item $item, string $type, int $quantity, array $attributes = []): StockMovement
    {
        return DB::transaction(function () use ($item, $type, $quantity, $attributes): StockMovement {
            $lockedItem = Item::query()->lockForUpdate()->findOrFail($item->id);

            if ($type === 'keluar' && $lockedItem->stock < $quantity) {
                throw new InsufficientStockException('Stok tidak mencukupi untuk pengeluaran ini.');
            }

            $updatedStock = $type === 'masuk'
                ? $lockedItem->stock + $quantity
                : $lockedItem->stock - $quantity;

            $movement = $lockedItem->stockMovements()->create([
                'type' => $type,
                'quantity' => $quantity,
                'reference' => $attributes['reference'] ?? null,
                'actor' => $attributes['actor'] ?? null,
                'note' => $attributes['note'] ?? null,
                'moved_at' => $attributes['moved_at'] ?? now(),
            ]);

            $lockedItem->update(['stock' => $updatedStock]);
            $item->forceFill(['stock' => $updatedStock]);

            return $movement;
        });
    }
}
