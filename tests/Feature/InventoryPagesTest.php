<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\StorageLocation;
use Database\Seeders\InventorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_pages_render_successfully(): void
    {
        $this->seed(InventorySeeder::class);

        $item = Item::query()->firstOrFail();

        $this->get(route('barang.index'))
            ->assertOk()
            ->assertSee('Daftar Barang')
            ->assertSee($item->name);

        $this->get(route('barang.create'))
            ->assertOk()
            ->assertSee('Tambah Barang Baru');

        $this->get(route('barang.show', $item))
            ->assertOk()
            ->assertSee($item->name)
            ->assertSee('12 Mutasi Terakhir');

        $this->get(route('barang.edit', $item))
            ->assertOk()
            ->assertSee('Edit Data Barang');

        $this->get(route('stock-movements.create', ['item' => $item->id]))
            ->assertOk()
            ->assertSee('Catat Mutasi Stok')
            ->assertSee($item->sku);
    }

    public function test_user_can_create_an_item_with_initial_stock(): void
    {
        $this->seed(InventorySeeder::class);

        $category = Category::query()->firstOrFail();
        $location = StorageLocation::query()->firstOrFail();

        $response = $this->post(route('barang.store'), [
            'name' => 'Kertas HVS A4',
            'sku' => 'ATK-777',
            'category_id' => $category->id,
            'storage_location_id' => $location->id,
            'unit' => 'rim',
            'minimum_stock' => 5,
            'initial_stock' => 12,
            'condition_status' => 'baik',
            'description' => 'Persediaan untuk kebutuhan cetak administrasi.',
        ]);

        $item = Item::query()->where('sku', 'ATK-777')->firstOrFail();

        $response->assertRedirect(route('barang.show', $item));
        $this->assertSame(12, $item->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => 'masuk',
            'quantity' => 12,
            'reference' => 'STOK-AWAL',
        ]);
    }

    public function test_user_can_record_stock_movement(): void
    {
        $this->seed(InventorySeeder::class);

        $item = Item::query()->where('sku', 'ATK-001')->firstOrFail();
        $originalStock = $item->stock;

        $response = $this->post(route('stock-movements.store'), [
            'item_id' => $item->id,
            'type' => 'keluar',
            'quantity' => 5,
            'reference' => 'REQ-TES',
            'actor' => 'Penguji Sistem',
            'moved_at' => now()->toDateTimeString(),
            'note' => 'Pengeluaran untuk pengujian.',
        ]);

        $response->assertRedirect(route('barang.show', $item));
        $this->assertSame($originalStock - 5, $item->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => 'keluar',
            'quantity' => 5,
            'reference' => 'REQ-TES',
        ]);
    }
}
