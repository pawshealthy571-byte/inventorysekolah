<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'storage_location_id',
        'sku',
        'name',
        'unit',
        'stock',
        'stock_good',
        'stock_less_good',
        'stock_damaged',
        'minimum_stock',
        'condition_status',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'storage_location_id' => 'integer',
            'stock' => 'integer',
            'stock_good' => 'integer',
            'stock_less_good' => 'integer',
            'stock_damaged' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }

    /**
     * Get the storage location that owns the item.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class, 'storage_location_id');
    }

    /**
     * Get the stock movements for the item.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the requests for the item.
     */
    public function itemRequests(): HasMany
    {
        return $this->hasMany(ItemRequest::class);
    }

    /**
     * Get the purchases for the item.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Scope a query to only include low-stock items.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock');
    }

    /**
     * Scope a query to search by SKU or name.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('name', 'like', '%' . $term . '%')
                ->orWhere('sku', 'like', '%' . $term . '%');
        });
    }

    /**
     * Determine if the item is at or below minimum stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }

    /**
     * Get the usable stock that can still be distributed.
     */
    public function usableStock(): int
    {
        return $this->stock_good + $this->stock_less_good;
    }

    /**
     * Get the stock for a specific condition bucket.
     */
    public function stockForCondition(string $condition): int
    {
        $column = self::conditionColumn($condition);

        return (int) $this->{$column};
    }

    /**
     * Get the stock grouped by item condition.
     *
     * @return array<int, array{key: string, label: string, stock: int}>
     */
    public function stockBreakdown(): array
    {
        return collect(self::conditionBuckets())
            ->map(fn (string $condition): array => [
                'key' => $condition,
                'label' => self::conditionLabelFor($condition),
                'stock' => $this->stockForCondition($condition),
            ])
            ->all();
    }

    /**
     * Suggest how many units should be purchased.
     */
    public function recommendedPurchaseQuantityFor(int $requestedQuantity = 0): int
    {
        $usableStock = $this->usableStock();
        $shortage = max($requestedQuantity - $usableStock, 0);
        $minimumGap = max($this->minimum_stock - $usableStock, 0);

        return max($shortage, $minimumGap);
    }

    /**
     * Get the dominant item condition based on current stock buckets.
     */
    public function dominantConditionStatus(): string
    {
        $stocks = [
            'baik' => $this->stock_good,
            'kurang-baik' => $this->stock_less_good,
            'rusak' => $this->stock_damaged,
        ];

        $highestStock = max($stocks);

        if ($highestStock === 0) {
            return self::normalizeConditionStatus($this->condition_status);
        }

        return (string) collect($stocks)
            ->sortDesc()
            ->keys()
            ->first();
    }

    /**
     * Get the label for the condition status.
     */
    public function conditionLabel(): string
    {
        return self::conditionLabelFor($this->dominantConditionStatus());
    }

    /**
     * Normalize legacy condition values.
     */
    public static function normalizeConditionStatus(?string $status): string
    {
        return match ($status) {
            'perlu-perawatan', 'kurang-baik' => 'kurang-baik',
            'rusak-ringan', 'rusak' => 'rusak',
            default => 'baik',
        };
    }

    /**
     * Get the item condition buckets.
     *
     * @return list<string>
     */
    public static function conditionBuckets(): array
    {
        return ['baik', 'kurang-baik', 'rusak'];
    }

    /**
     * Get the storage column for a condition bucket.
     */
    public static function conditionColumn(string $condition): string
    {
        return match (self::normalizeConditionStatus($condition)) {
            'kurang-baik' => 'stock_less_good',
            'rusak' => 'stock_damaged',
            default => 'stock_good',
        };
    }

    /**
     * Get the display label for a condition bucket.
     */
    public static function conditionLabelFor(string $condition): string
    {
        return match (self::normalizeConditionStatus($condition)) {
            'kurang-baik' => 'Kurang Baik',
            'rusak' => 'Rusak',
            default => 'Baik',
        };
    }
}
