<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StorageLocation;
use App\Models\User;
use Database\Seeders\InventorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_pages_render_successfully(): void
    {
        $this->seed(InventorySeeder::class);
        $user = User::factory()->admin()->create();

        $item = Item::query()->firstOrFail();

        $this->actingAs($user)->get(route('barang.index'))
            ->assertOk()
            ->assertSee('Daftar Barang')
            ->assertSee($item->name);

        $this->actingAs($user)->get(route('barang.create'))
            ->assertOk()
            ->assertSee('Tambah Barang Baru');

        $this->actingAs($user)->get(route('barang.show', $item))
            ->assertOk()
            ->assertSee($item->name)
            ->assertSee('12 Mutasi Terakhir');

        $this->actingAs($user)->get(route('barang.edit', $item))
            ->assertOk()
            ->assertSee('Edit Data Barang');

        $this->actingAs($user)->get(route('stock-movements.create', ['item' => $item->id]))
            ->assertOk()
            ->assertSee('Catat Mutasi Stok')
            ->assertSee($item->sku);

        $this->actingAs($user)->get(route('permintaan-barang.index'))
            ->assertOk()
            ->assertSee('Permintaan Barang');

        $this->actingAs($user)->get(route('permintaan-barang.create'))
            ->assertOk()
            ->assertSee('Ajukan Permintaan Barang');

        $this->actingAs($user)->get(route('pembelian-barang.index'))
            ->assertOk()
            ->assertSee('Pembelian Barang');

        $this->actingAs($user)->get(route('pembelian-barang.create'))
            ->assertOk()
            ->assertSee('Input Pembelian Barang');
    }

    public function test_user_can_create_an_item_with_initial_stock(): void
    {
        $this->seed(InventorySeeder::class);
        $user = User::factory()->admin()->create();

        $location = StorageLocation::query()->firstOrFail();

        $response = $this->actingAs($user)->post(route('barang.store'), [
            'name' => 'Kertas HVS A4',
            'sku' => 'ATK-777',
            'storage_location_id' => $location->id,
            'unit' => 'rim',
            'minimum_stock' => 5,
            'initial_stock_good' => 10,
            'initial_stock_less_good' => 2,
            'initial_stock_damaged' => 0,
            'description' => 'Persediaan untuk kebutuhan cetak administrasi.',
        ]);

        $item = Item::query()->where('sku', 'ATK-777')->firstOrFail();

        $response->assertRedirect(route('barang.show', $item));
        $this->assertSame(12, $item->fresh()->stock);
        $this->assertSame(10, $item->fresh()->stock_good);
        $this->assertSame(2, $item->fresh()->stock_less_good);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => 'masuk',
            'quantity' => 10,
            'condition_bucket' => 'baik',
            'reference' => 'STOK-AWAL',
        ]);
    }

    public function test_user_can_record_stock_movement(): void
    {
        $this->seed(InventorySeeder::class);
        $user = User::factory()->admin()->create();

        $item = Item::query()->where('sku', 'ATK-001')->firstOrFail();
        $originalStock = $item->stock;

        $response = $this->actingAs($user)->post(route('stock-movements.store'), [
            'item_id' => $item->id,
            'type' => 'keluar',
            'quantity' => 5,
            'condition_bucket' => 'baik',
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
            'condition_bucket' => 'baik',
            'reference' => 'REQ-TES',
        ]);
    }

    public function test_approved_request_reduces_stock_automatically(): void
    {
        $this->seed(InventorySeeder::class);
        $user = User::factory()->admin()->create();
        $item = Item::query()->where('sku', 'ATK-001')->firstOrFail();

        $requestResponse = $this->actingAs($user)->post(route('permintaan-barang.store'), [
            'item_id' => $item->id,
            'requester_name' => 'Guru IPA',
            'quantity_requested' => 4,
            'requested_at' => now()->toDateTimeString(),
            'note' => 'Kebutuhan alat tulis kelas.',
        ]);

        $requestResponse->assertRedirect(route('permintaan-barang.index'));

        $itemRequest = $item->itemRequests()->latest('id')->firstOrFail();
        $startingStock = $item->fresh()->stock;

        $approveResponse = $this->actingAs($user)->patch(route('permintaan-barang.update-status', $itemRequest), [
            'status' => 'disetujui',
        ]);

        $approveResponse->assertRedirect(route('permintaan-barang.index'));
        $this->assertSame($startingStock - 4, $item->fresh()->stock);
        $this->assertDatabaseHas('item_requests', [
            'id' => $itemRequest->id,
            'status' => 'disetujui',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => 'keluar',
            'reference' => 'REQ-' . str_pad((string) $itemRequest->id, 5, '0', STR_PAD_LEFT),
        ]);
    }

    public function test_purchase_adds_stock_to_good_bucket_automatically(): void
    {
        $this->seed(InventorySeeder::class);
        $user = User::factory()->admin()->create();
        $item = Item::query()->where('sku', 'ATK-014')->firstOrFail();

        $originalGoodStock = $item->stock_good;
        $originalStock = $item->stock;

        $response = $this->actingAs($user)->post(route('pembelian-barang.store'), [
            'item_id' => $item->id,
            'quantity_purchased' => 7,
            'store_name' => 'Toko Serba Ada',
            'unit_price' => 15000,
            'purchaser_name' => 'Staf Sarpras',
            'purchased_at' => now()->toDateTimeString(),
            'note' => 'Tambahan stok mingguan.',
        ]);

        $response->assertRedirect(route('pembelian-barang.index'));
        $this->assertSame($originalStock + 7, $item->fresh()->stock);
        $this->assertSame($originalGoodStock + 7, $item->fresh()->stock_good);
        $this->assertDatabaseHas('purchases', [
            'item_id' => $item->id,
            'quantity_purchased' => 7,
            'store_name' => 'Toko Serba Ada',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => 'masuk',
            'quantity' => 7,
            'condition_bucket' => 'baik',
        ]);
    }

    public function test_user_can_create_item_from_assistant_chat(): void
    {
        $this->seed(InventorySeeder::class);
        $user = User::factory()->admin()->create();

        $location = StorageLocation::query()->firstOrFail();

        $response = $this->actingAs($user)->postJson(route('ai.barang-chat'), [
            'message' => sprintf(
                'Tambah barang Spidol AI lokasi %s satuan pcs minimum stok 3 stok baik 15 deskripsi Untuk kebutuhan presentasi',
                $location->name,
            ),
        ]);

        $response->assertOk()
            ->assertJson([
                'ok' => true,
                'message' => 'Barang Spidol AI berhasil ditambahkan ke database.',
            ]);

        $item = Item::query()->where('name', 'Spidol AI')->firstOrFail();

        $this->assertSame($location->id, $item->storage_location_id);
        $this->assertSame(15, $item->stock);
        $this->assertSame('pcs', $item->unit);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => 'masuk',
            'quantity' => 15,
            'condition_bucket' => 'baik',
            'reference' => 'STOK-AWAL',
        ]);
    }

    public function test_user_can_create_item_from_voice_like_assistant_message(): void
    {
        $this->seed(InventorySeeder::class);
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->postJson(route('ai.barang-chat'), [
            'message' => 'Tambah barang Proyektor Mini lokasi Ruang Multimedia satuan unit minimum stok lima stok baik dua belas stok kurang baik satu stok rusak nol deskripsi Untuk presentasi kelas.',
        ]);

        $response->assertOk()
            ->assertJson([
                'ok' => true,
                'message' => 'Barang Proyektor Mini berhasil ditambahkan ke database.',
            ]);

        $item = Item::query()->where('name', 'Proyektor Mini')->firstOrFail();

        $this->assertSame(5, $item->minimum_stock);
        $this->assertSame(13, $item->stock);
        $this->assertSame(12, $item->stock_good);
        $this->assertSame(1, $item->stock_less_good);
        $this->assertSame(0, $item->stock_damaged);
        $this->assertSame('unit', $item->unit);
    }

    public function test_guest_cannot_access_inventory_pages(): void
    {
        $this->seed(InventorySeeder::class);

        $item = Item::query()->firstOrFail();

        $this->get(route('barang.index'))->assertRedirect(route('login'));
        $this->get(route('barang.create'))->assertRedirect(route('login'));
        $this->get(route('barang.show', $item))->assertRedirect(route('login'));
        $this->get(route('barang.edit', $item))->assertRedirect(route('login'));
        $this->get(route('stock-movements.create'))->assertRedirect(route('login'));
        $this->get(route('permintaan-barang.index'))->assertRedirect(route('login'));
        $this->get(route('pembelian-barang.index'))->assertRedirect(route('login'));
    }
}
