<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
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
            $movement = $this->applyMovement($lockedItem, $type, $quantity, $attributes);

            $this->syncSnapshot($item, $lockedItem);

            return $movement;
        });
    }

    /**
     * Release usable stock for an approved request.
     *
     * @param  array<string, mixed>  $attributes
     * @return Collection<int, StockMovement>
     */
    public function releaseForRequest(Item $item, int $quantity, array $attributes = []): Collection
    {
        return DB::transaction(function () use ($item, $quantity, $attributes): Collection {
            $lockedItem = Item::query()->lockForUpdate()->findOrFail($item->id);
            $allocation = $this->allocateOutgoing($lockedItem, $quantity);
            $movements = collect();

            foreach ($allocation as $bucket => $allocatedQuantity) {
                if ($allocatedQuantity < 1) {
                    continue;
                }

                $movements->push($this->applyMovement($lockedItem, 'keluar', $allocatedQuantity, [
                    ...$attributes,
                    'condition_bucket' => $bucket,
                ]));
            }

            $this->syncSnapshot($item, $lockedItem);

            return $movements;
        });
    }

    /**
     * Record a restock purchase to the good stock bucket.
     */
    public function receivePurchase(Item $item, int $quantity, array $attributes = []): StockMovement
    {
        return $this->record($item, 'masuk', $quantity, [
            ...$attributes,
            'condition_bucket' => 'baik',
        ]);
    }

    /**
     * Apply a movement to a locked item row.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function applyMovement(Item $item, string $type, int $quantity, array $attributes = []): StockMovement
    {
        $condition = Item::normalizeConditionStatus($attributes['condition_bucket'] ?? 'baik');
        $column = Item::conditionColumn($condition);
        $availableStock = (int) $item->{$column};

        if ($type === 'keluar' && $availableStock < $quantity) {
            throw new InsufficientStockException('Stok pada kondisi yang dipilih tidak mencukupi untuk pengeluaran ini.');
        }

        $item->{$column} = $type === 'masuk'
            ? $availableStock + $quantity
            : $availableStock - $quantity;
        $item->stock = $this->totalStock($item);
        $item->condition_status = $item->dominantConditionStatus();
        $item->save();

        return $item->stockMovements()->create([
            'type' => $type,
            'quantity' => $quantity,
            'condition_bucket' => $condition,
            'balance_after' => $item->stock,
            'reference' => $attributes['reference'] ?? null,
            'actor' => $attributes['actor'] ?? null,
            'note' => $attributes['note'] ?? null,
            'moved_at' => $attributes['moved_at'] ?? now(),
        ]);
    }

    /**
     * Allocate outgoing quantities from usable stock buckets.
     *
     * @return array<string, int>
     */
    private function allocateOutgoing(Item $item, int $quantity): array
    {
        $remaining = $quantity;
        $allocation = [];

        foreach (['baik', 'kurang-baik'] as $condition) {
            $available = $item->stockForCondition($condition);

            if ($available < 1 || $remaining < 1) {
                continue;
            }

            $allocated = min($available, $remaining);
            $allocation[$condition] = $allocated;
            $remaining -= $allocated;
        }

        if ($remaining > 0) {
            throw new InsufficientStockException('Stok barang baik dan kurang baik tidak cukup untuk menyetujui permintaan ini.');
        }

        return $allocation;
    }

    /**
     * Calculate the total stock from all condition buckets.
     */
    private function totalStock(Item $item): int
    {
        return (int) $item->stock_good + (int) $item->stock_less_good + (int) $item->stock_damaged;
    }

    /**
     * Sync the latest stock snapshot back to the original instance.
     */
    private function syncSnapshot(Item $item, Item $lockedItem): void
    {
        $item->forceFill([
            'stock' => $lockedItem->stock,
            'stock_good' => $lockedItem->stock_good,
            'stock_less_good' => $lockedItem->stock_less_good,
            'stock_damaged' => $lockedItem->stock_damaged,
            'condition_status' => $lockedItem->condition_status,
        ]);
    }
}
