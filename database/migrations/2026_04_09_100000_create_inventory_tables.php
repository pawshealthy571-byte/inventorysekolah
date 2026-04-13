<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('storage_location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('unit', 50);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->string('condition_status', 50)->default('baik');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->unsignedInteger('quantity');
            $table->string('reference')->nullable();
            $table->string('actor')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('moved_at');
            $table->timestamps();

            $table->index(['type', 'moved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('items');
        Schema::dropIfExists('storage_locations');
        Schema::dropIfExists('categories');
    }
};
