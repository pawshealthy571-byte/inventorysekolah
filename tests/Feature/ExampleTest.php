<?php

namespace Tests\Feature;

use App\Models\User;
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
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Catat Mutasi');
        $response->assertSee('Barang Perlu Restok');
        $response->assertSee('Lihat Semua');
    }

    public function test_the_operational_page_renders_inventory_details(): void
    {
        $this->seed(InventorySeeder::class);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.operational'));

        $response->assertOk();
        $response->assertSee('Operasional Gudang');
        $response->assertSee('Barang Dengan Stok Menipis');
        $response->assertSee('Mutasi Stok Terbaru');
        $response->assertSee('Buku Tulis 38 Lembar');
    }

    public function test_the_dashboard_handles_missing_inventory_tables(): void
    {
        $user = User::factory()->create();

        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('items');
        Schema::dropIfExists('storage_locations');
        Schema::dropIfExists('categories');

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Tabel inventaris belum tersedia');
        $response->assertSee('php artisan migrate --seed');
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
    }
}
