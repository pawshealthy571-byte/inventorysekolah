<?php

namespace Tests\Feature;

use Database\Seeders\InventorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_dashboard_renders_inventory_information(): void
    {
        $this->seed(InventorySeeder::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Dashboard Gudang Sekolah');
        $response->assertSee('Barang Dengan Stok Menipis');
        $response->assertSee('Mutasi Stok Terbaru');
        $response->assertSee('Buku Tulis 38 Lembar');
    }

    public function test_the_dashboard_handles_missing_inventory_tables(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('items');
        Schema::dropIfExists('storage_locations');
        Schema::dropIfExists('categories');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Tabel inventaris belum tersedia');
        $response->assertSee('php artisan migrate --seed');
    }
}
