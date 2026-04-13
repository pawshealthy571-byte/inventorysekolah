<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'storage_location_id',
        'sku',
        'name',
        'unit',
        'stock',
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
            'category_id' => 'integer',
            'storage_location_id' => 'integer',
            'stock' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }

    /**
     * Get the category that owns the item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
     * Get the label for the condition status.
     */
    public function conditionLabel(): string
    {
        return match ($this->condition_status) {
            'baik' => 'Baik',
            'perlu-perawatan' => 'Perlu Perawatan',
            'rusak-ringan' => 'Rusak Ringan',
            default => ucfirst(str_replace('-', ' ', $this->condition_status)),
        };
    }
}
