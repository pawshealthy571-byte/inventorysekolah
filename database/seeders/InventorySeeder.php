<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\Purchase;
use App\Models\StorageLocation;
use App\Services\StockMovementService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    /**
     * Seed the application's inventory data.
     */
    public function run(): void
    {
        if (Item::query()->exists()) {
            return;
        }



        $locations = collect([
            ['name' => 'Gudang Utama', 'code' => 'GDU', 'description' => 'Ruang penyimpanan pusat untuk kebutuhan umum.'],
            ['name' => 'Lemari ATK TU', 'code' => 'ATK-TU', 'description' => 'Penyimpanan ATK harian tata usaha.'],
            ['name' => 'Ruang Multimedia', 'code' => 'MULTI', 'description' => 'Perangkat presentasi dan multimedia sekolah.'],
            ['name' => 'Gudang Kebersihan', 'code' => 'GBS', 'description' => 'Penyimpanan alat dan bahan kebersihan.'],
        ])->mapWithKeys(function (array $location): array {
            $record = StorageLocation::query()->create($location);

            return [$record->code => $record];
        });

        $stockMovementService = app(StockMovementService::class);

        $seedItems = [
            [
                'data' => [
                    'sku' => 'ATK-001',
                    'name' => 'Buku Tulis 38 Lembar',
                    'storage_location_id' => $locations['GDU']->id,
                    'unit' => 'pack',
                    'minimum_stock' => 12,
                    'condition_status' => 'baik',
                    'description' => 'Persediaan untuk kebutuhan siswa dan wali kelas.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 120, 'condition_bucket' => 'baik', 'reference' => 'PO-2026-ATK-01', 'actor' => 'Tim Pengadaan', 'note' => 'Pengadaan awal semester.', 'moved_at' => now()->subDays(25)],
                    ['type' => 'keluar', 'quantity' => 36, 'condition_bucket' => 'baik', 'reference' => 'REQ-KLS-7A', 'actor' => 'Wali Kelas 7A', 'note' => 'Distribusi kebutuhan pembelajaran.', 'moved_at' => now()->subDays(8)],
                ],
            ],
            [
                'data' => [
                    'sku' => 'ATK-014',
                    'name' => 'Spidol Whiteboard',
                    'storage_location_id' => $locations['ATK-TU']->id,
                    'unit' => 'box',
                    'minimum_stock' => 8,
                    'condition_status' => 'baik',
                    'description' => 'Stok untuk kelas dan ruang guru.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 24, 'condition_bucket' => 'baik', 'reference' => 'PO-2026-ATK-04', 'actor' => 'Tim Pengadaan', 'note' => 'Pembelian rutin bulanan.', 'moved_at' => now()->subDays(18)],
                    ['type' => 'keluar', 'quantity' => 18, 'condition_bucket' => 'baik', 'reference' => 'REQ-GURU-11', 'actor' => 'Ruang Guru', 'note' => 'Distribusi untuk kegiatan belajar mengajar.', 'moved_at' => now()->subDays(3)],
                ],
            ],
            [
                'data' => [
                    'sku' => 'BKP-007',
                    'name' => 'Buku Matematika Kelas 8',
                    'storage_location_id' => $locations['GDU']->id,
                    'unit' => 'eksemplar',
                    'minimum_stock' => 20,
                    'condition_status' => 'baik',
                    'description' => 'Cadangan buku paket untuk siswa baru dan penggantian.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 90, 'condition_bucket' => 'baik', 'reference' => 'PO-2026-BUKU-02', 'actor' => 'Kepala Perpustakaan', 'note' => 'Pengadaan buku semester genap.', 'moved_at' => now()->subDays(22)],
                    ['type' => 'keluar', 'quantity' => 42, 'condition_bucket' => 'baik', 'reference' => 'DIST-8B', 'actor' => 'Koordinator Kelas 8', 'note' => 'Distribusi ke siswa kelas 8.', 'moved_at' => now()->subDays(10)],
                ],
            ],
            [
                'data' => [
                    'sku' => 'ELK-003',
                    'name' => 'Proyektor LCD',
                    'storage_location_id' => $locations['MULTI']->id,
                    'unit' => 'unit',
                    'minimum_stock' => 2,
                    'condition_status' => 'kurang-baik',
                    'description' => 'Digunakan untuk presentasi kelas dan rapat sekolah.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 3, 'condition_bucket' => 'kurang-baik', 'reference' => 'PO-2026-ELK-01', 'actor' => 'Wakasek Sarpras', 'note' => 'Tambahan alat presentasi sekolah.', 'moved_at' => now()->subDays(30)],
                    ['type' => 'masuk', 'quantity' => 2, 'condition_bucket' => 'baik', 'reference' => 'PO-2026-ELK-01', 'actor' => 'Wakasek Sarpras', 'note' => 'Unit cadangan baru untuk ruang multimedia.', 'moved_at' => now()->subDays(29)],
                    ['type' => 'keluar', 'quantity' => 2, 'condition_bucket' => 'baik', 'reference' => 'PINJAM-RAPAT', 'actor' => 'Panitia Rapat', 'note' => 'Dipakai untuk kegiatan rapat komite.', 'moved_at' => now()->subDays(2)],
                ],
            ],
            [
                'data' => [
                    'sku' => 'KBS-010',
                    'name' => 'Cairan Pembersih Lantai',
                    'storage_location_id' => $locations['GBS']->id,
                    'unit' => 'botol',
                    'minimum_stock' => 15,
                    'condition_status' => 'baik',
                    'description' => 'Dipakai oleh petugas kebersihan untuk area kelas dan kantor.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 40, 'condition_bucket' => 'baik', 'reference' => 'PO-2026-KBS-05', 'actor' => 'Petugas Sarpras', 'note' => 'Pengadaan bahan kebersihan bulanan.', 'moved_at' => now()->subDays(16)],
                    ['type' => 'keluar', 'quantity' => 28, 'condition_bucket' => 'baik', 'reference' => 'PAKAI-HARIAN', 'actor' => 'Petugas Kebersihan', 'note' => 'Pemakaian rutin untuk gedung utama.', 'moved_at' => now()->subDays(1)],
                ],
            ],
        ];

        foreach ($seedItems as $seedItem) {
            $item = Item::query()->create([
                ...$seedItem['data'],
                'stock' => 0,
                'stock_good' => 0,
                'stock_less_good' => 0,
                'stock_damaged' => 0,
            ]);

            foreach ($seedItem['movements'] as $movement) {
                $stockMovementService->record($item, $movement['type'], $movement['quantity'], [
                    'condition_bucket' => $movement['condition_bucket'],
                    'reference' => $movement['reference'],
                    'actor' => $movement['actor'],
                    'note' => $movement['note'],
                    'moved_at' => $movement['moved_at'],
                ]);
            }
        }

        $this->seedRequestsAndPurchases($stockMovementService);
    }

    /**
     * Seed request and purchase history.
     */
    private function seedRequestsAndPurchases(StockMovementService $stockMovementService): void
    {
        $spidol = Item::query()->where('sku', 'ATK-014')->firstOrFail();
        $buku = Item::query()->where('sku', 'BKP-007')->firstOrFail();

        $approvedRequest = ItemRequest::query()->create([
            'item_id' => $buku->id,
            'requester_name' => 'Guru Matematika',
            'quantity_requested' => 6,
            'requested_at' => now()->subDays(4),
            'status' => 'menunggu',
            'recommended_purchase_quantity' => $buku->recommendedPurchaseQuantityFor(6),
            'note' => 'Tambahan untuk siswa pindahan semester ini.',
        ]);

        $stockMovementService->releaseForRequest($buku, 6, [
            'reference' => 'REQ-' . str_pad((string) $approvedRequest->id, 5, '0', STR_PAD_LEFT),
            'actor' => 'Wakil Kepala Sekolah',
            'note' => 'Distribusi buku paket untuk siswa baru.',
            'moved_at' => now()->subDays(4),
        ]);

        $approvedRequest->update([
            'status' => 'disetujui',
            'reviewer_name' => 'Wakil Kepala Sekolah',
            'reviewed_at' => now()->subDays(4),
            'review_note' => 'Disetujui dan langsung dikeluarkan dari stok tersedia.',
        ]);

        ItemRequest::query()->create([
            'item_id' => $spidol->id,
            'requester_name' => 'Koordinator Guru',
            'quantity_requested' => 10,
            'requested_at' => now()->subDay(),
            'status' => 'menunggu',
            'recommended_purchase_quantity' => $spidol->recommendedPurchaseQuantityFor(10),
            'note' => 'Kebutuhan ujian praktik kelas.',
        ]);

        DB::transaction(function () use ($stockMovementService, $spidol): void {
            $purchase = Purchase::query()->create([
                'item_id' => $spidol->id,
                'quantity_purchased' => 20,
                'store_name' => 'Toko Sinar Edukasi',
                'unit_price' => 18500,
                'total_cost' => 370000,
                'purchaser_name' => 'Tim Pengadaan',
                'purchased_at' => now()->subDays(2),
                'note' => 'Restok bulanan alat tulis guru.',
            ]);

            $stockMovementService->receivePurchase($spidol, 20, [
                'reference' => 'PO-' . str_pad((string) $purchase->id, 5, '0', STR_PAD_LEFT),
                'actor' => 'Tim Pengadaan',
                'note' => 'Penambahan stok dari transaksi pembelian.',
                'moved_at' => now()->subDays(2),
            ]);
        });
    }
}
