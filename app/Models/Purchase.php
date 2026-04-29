<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'quantity_purchased',
        'store_name',
        'unit_price',
        'total_cost',
        'purchaser_name',
        'purchased_at',
        'note',
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
            'quantity_purchased' => 'integer',
            'unit_price' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'purchased_at' => 'datetime',
        ];
    }

    /**
     * Get the purchased item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
