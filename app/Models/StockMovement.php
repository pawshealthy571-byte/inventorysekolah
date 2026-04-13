<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'type',
        'quantity',
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
}
