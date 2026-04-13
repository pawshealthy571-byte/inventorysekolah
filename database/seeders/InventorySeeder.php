<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\StorageLocation;
use App\Services\StockMovementService;
use Illuminate\Database\Seeder;
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

        $categories = collect([
            ['name' => 'ATK', 'description' => 'Perlengkapan administrasi dan kelas.'],
            ['name' => 'Buku Pelajaran', 'description' => 'Buku paket dan buku penunjang.'],
            ['name' => 'Kebersihan', 'description' => 'Peralatan dan bahan kebersihan sekolah.'],
            ['name' => 'Elektronik', 'description' => 'Perangkat pendukung kegiatan belajar.'],
        ])->mapWithKeys(function (array $category): array {
            $record = Category::query()->create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
            ]);

            return [$record->name => $record];
        });

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
                    'category_id' => $categories['ATK']->id,
                    'storage_location_id' => $locations['GDU']->id,
                    'unit' => 'pack',
                    'minimum_stock' => 12,
                    'condition_status' => 'baik',
                    'description' => 'Persediaan untuk kebutuhan siswa dan wali kelas.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 120, 'reference' => 'PO-2026-ATK-01', 'actor' => 'Tim Pengadaan', 'note' => 'Pengadaan awal semester.', 'moved_at' => now()->subDays(25)],
                    ['type' => 'keluar', 'quantity' => 36, 'reference' => 'REQ-KLS-7A', 'actor' => 'Wali Kelas 7A', 'note' => 'Distribusi kebutuhan pembelajaran.', 'moved_at' => now()->subDays(8)],
                ],
            ],
            [
                'data' => [
                    'sku' => 'ATK-014',
                    'name' => 'Spidol Whiteboard',
                    'category_id' => $categories['ATK']->id,
                    'storage_location_id' => $locations['ATK-TU']->id,
                    'unit' => 'box',
                    'minimum_stock' => 8,
                    'condition_status' => 'baik',
                    'description' => 'Stok untuk kelas dan ruang guru.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 24, 'reference' => 'PO-2026-ATK-04', 'actor' => 'Tim Pengadaan', 'note' => 'Pembelian rutin bulanan.', 'moved_at' => now()->subDays(18)],
                    ['type' => 'keluar', 'quantity' => 18, 'reference' => 'REQ-GURU-11', 'actor' => 'Ruang Guru', 'note' => 'Distribusi untuk kegiatan belajar mengajar.', 'moved_at' => now()->subDays(3)],
                ],
            ],
            [
                'data' => [
                    'sku' => 'BKP-007',
                    'name' => 'Buku Matematika Kelas 8',
                    'category_id' => $categories['Buku Pelajaran']->id,
                    'storage_location_id' => $locations['GDU']->id,
                    'unit' => 'eksemplar',
                    'minimum_stock' => 20,
                    'condition_status' => 'baik',
                    'description' => 'Cadangan buku paket untuk siswa baru dan penggantian.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 90, 'reference' => 'PO-2026-BUKU-02', 'actor' => 'Kepala Perpustakaan', 'note' => 'Pengadaan buku semester genap.', 'moved_at' => now()->subDays(22)],
                    ['type' => 'keluar', 'quantity' => 42, 'reference' => 'DIST-8B', 'actor' => 'Koordinator Kelas 8', 'note' => 'Distribusi ke siswa kelas 8.', 'moved_at' => now()->subDays(10)],
                ],
            ],
            [
                'data' => [
                    'sku' => 'ELK-003',
                    'name' => 'Proyektor LCD',
                    'category_id' => $categories['Elektronik']->id,
                    'storage_location_id' => $locations['MULTI']->id,
                    'unit' => 'unit',
                    'minimum_stock' => 2,
                    'condition_status' => 'perlu-perawatan',
                    'description' => 'Digunakan untuk presentasi kelas dan rapat sekolah.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 5, 'reference' => 'PO-2026-ELK-01', 'actor' => 'Wakasek Sarpras', 'note' => 'Tambahan alat presentasi sekolah.', 'moved_at' => now()->subDays(30)],
                    ['type' => 'keluar', 'quantity' => 2, 'reference' => 'PINJAM-RAPAT', 'actor' => 'Panitia Rapat', 'note' => 'Dipakai untuk kegiatan rapat komite.', 'moved_at' => now()->subDays(2)],
                ],
            ],
            [
                'data' => [
                    'sku' => 'KBS-010',
                    'name' => 'Cairan Pembersih Lantai',
                    'category_id' => $categories['Kebersihan']->id,
                    'storage_location_id' => $locations['GBS']->id,
                    'unit' => 'botol',
                    'minimum_stock' => 15,
                    'condition_status' => 'baik',
                    'description' => 'Dipakai oleh petugas kebersihan untuk area kelas dan kantor.',
                ],
                'movements' => [
                    ['type' => 'masuk', 'quantity' => 40, 'reference' => 'PO-2026-KBS-05', 'actor' => 'Petugas Sarpras', 'note' => 'Pengadaan bahan kebersihan bulanan.', 'moved_at' => now()->subDays(16)],
                    ['type' => 'keluar', 'quantity' => 28, 'reference' => 'PAKAI-HARIAN', 'actor' => 'Petugas Kebersihan', 'note' => 'Pemakaian rutin untuk gedung utama.', 'moved_at' => now()->subDays(1)],
                ],
            ],
        ];

        foreach ($seedItems as $seedItem) {
            $item = Item::query()->create([
                ...$seedItem['data'],
                'stock' => 0,
            ]);

            foreach ($seedItem['movements'] as $movement) {
                $stockMovementService->record($item, $movement['type'], $movement['quantity'], [
                    'reference' => $movement['reference'],
                    'actor' => $movement['actor'],
                    'note' => $movement['note'],
                    'moved_at' => $movement['moved_at'],
                ]);
            }
        }
    }
}
