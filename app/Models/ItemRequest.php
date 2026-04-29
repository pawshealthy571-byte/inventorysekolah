<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemRequest extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'requester_name',
        'quantity_requested',
        'requested_at',
        'status',
        'recommended_purchase_quantity',
        'reviewer_name',
        'reviewed_at',
        'note',
        'review_note',
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
            'quantity_requested' => 'integer',
            'recommended_purchase_quantity' => 'integer',
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get the item that was requested.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Determine if the request is still waiting for a decision.
     */
    public function isPending(): bool
    {
        return $this->status === 'menunggu';
    }

    /**
     * Determine if the request already needs a purchase recommendation.
     */
    public function needsPurchaseRecommendation(): bool
    {
        return $this->recommended_purchase_quantity > 0;
    }

    /**
     * Get the display label for the request status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => 'Menunggu',
        };
    }
}
