<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedInteger('stock_good')->default(0)->after('stock');
            $table->unsignedInteger('stock_less_good')->default(0)->after('stock_good');
            $table->unsignedInteger('stock_damaged')->default(0)->after('stock_less_good');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('condition_bucket', 50)->default('baik')->after('quantity');
            $table->unsignedInteger('balance_after')->default(0)->after('condition_bucket');
        });

        Schema::create('item_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('requester_name');
            $table->unsignedInteger('quantity_requested');
            $table->timestamp('requested_at');
            $table->string('status', 20)->default('menunggu');
            $table->unsignedInteger('recommended_purchase_quantity')->default(0);
            $table->string('reviewer_name')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('note')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'requested_at']);
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedInteger('quantity_purchased');
            $table->string('store_name');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_cost', 14, 2);
            $table->string('purchaser_name')->nullable();
            $table->timestamp('purchased_at');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('purchased_at');
        });

        $items = DB::table('items')->select(['id', 'stock', 'condition_status'])->orderBy('id')->get();

        foreach ($items as $item) {
            $bucket = match ($item->condition_status) {
                'perlu-perawatan', 'kurang-baik' => 'stock_less_good',
                'rusak-ringan', 'rusak' => 'stock_damaged',
                default => 'stock_good',
            };

            DB::table('items')
                ->where('id', $item->id)
                ->update([
                    'stock_good' => $bucket === 'stock_good' ? $item->stock : 0,
                    'stock_less_good' => $bucket === 'stock_less_good' ? $item->stock : 0,
                    'stock_damaged' => $bucket === 'stock_damaged' ? $item->stock : 0,
                    'condition_status' => match ($bucket) {
                        'stock_less_good' => 'kurang-baik',
                        'stock_damaged' => 'rusak',
                        default => 'baik',
                    },
                ]);
        }

        $movementGroups = DB::table('stock_movements')
            ->select('item_id')
            ->distinct()
            ->orderBy('item_id')
            ->pluck('item_id');

        foreach ($movementGroups as $itemId) {
            $balance = 0;
            $movements = DB::table('stock_movements')
                ->where('item_id', $itemId)
                ->orderBy('moved_at')
                ->orderBy('id')
                ->get(['id', 'type', 'quantity']);

            foreach ($movements as $movement) {
                $balance += $movement->type === 'masuk' ? $movement->quantity : -1 * $movement->quantity;

                DB::table('stock_movements')
                    ->where('id', $movement->id)
                    ->update([
                        'condition_bucket' => 'baik',
                        'balance_after' => max($balance, 0),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('item_requests');

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['condition_bucket', 'balance_after']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['stock_good', 'stock_less_good', 'stock_damaged']);
        });
    }
};
