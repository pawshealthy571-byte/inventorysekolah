<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'type',
        'quantity',
        'condition_bucket',
        'balance_after',
        'reference',
        'actor',
        'note',
        'moved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'item_id' => 'integer',
            'quantity' => 'integer',
            'balance_after' => 'integer',
            'moved_at' => 'datetime',
        ];
    }

    /**
     * Get the item that owns the stock movement.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the display label for the movement type.
     */
    public function typeLabel(): string
    {
        return $this->type === 'masuk' ? 'Masuk' : 'Keluar';
    }

    /**
     * Determine if the movement increases stock.
     */
    public function isIncoming(): bool
    {
        return $this->type === 'masuk';
    }

    /**
     * Get the display label for the condition bucket.
     */
    public function conditionBucketLabel(): string
    {
        return Item::conditionLabelFor($this->condition_bucket);
    }
}
