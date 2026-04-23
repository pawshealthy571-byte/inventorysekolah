<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Item;
use App\Models\StorageLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ItemAssistantService
{
    /**
     * Build item attributes from a natural-language message.
     *
     * @return array{
     *     attributes: array<string, mixed>,
     *     warnings: list<string>,
     *     missing: list<string>,
     *     summary: list<string>
     * }
     */
    public function buildDraft(string $message): array
    {
        $warnings = [];

        $name = $this->extractField($message, ['nama barang', 'nama', 'barang'], [
            'sku',
            'kategori',
            'lokasi',
            'satuan',
            'unit',
            'minimum stok',
            'min stok',
            'minimal stok',
            'stok baik',
            'stok kurang baik',
            'stok rusak',
            'stok awal',
            'deskripsi',
            'keterangan',
            'catatan',
        ]);

        $sku = $this->extractValue($message, '/\bsku\s*[:=-]?\s*([A-Z0-9\-\/]+)/iu');
        $unit = $this->extractField($message, ['satuan', 'unit'], [
            'minimum stok',
            'min stok',
            'minimal stok',
            'stok baik',
            'stok kurang baik',
            'stok rusak',
            'stok awal',
            'deskripsi',
            'keterangan',
            'catatan',
        ]);

        $description = $this->extractField($message, ['deskripsi', 'keterangan', 'catatan'], []);
        $minimumStock = $this->extractInteger($message, '/\b(?:minimum stok|min stok|minimal stok|stok minimum)\s*[:=-]?\s*(\d+)/iu');
        $stockGood = $this->extractInteger($message, '/\b(?:stok baik|stok awal baik)\s*[:=-]?\s*(\d+)/iu');
        $stockLessGood = $this->extractInteger($message, '/\b(?:stok kurang baik|stok kurangbaik|stok awal kurang baik)\s*[:=-]?\s*(\d+)/iu');
        $stockDamaged = $this->extractInteger($message, '/\b(?:stok rusak|stok awal rusak)\s*[:=-]?\s*(\d+)/iu');
        $genericStock = $this->extractInteger($message, '/\b(?:stok awal|stok)\s*[:=-]?\s*(\d+)/iu');

        if ($stockGood === null && $stockLessGood === null && $stockDamaged === null && $genericStock !== null) {
            $stockGood = $genericStock;
        }

        $unit = $unit ?: 'pcs';
        if (! $this->extractField($message, ['satuan', 'unit'], ['minimum stok', 'min stok', 'minimal stok'])) {
            $warnings[] = 'Satuan tidak disebut, sistem memakai default `pcs`.';
        }

        $minimumStock ??= 0;
        if (! preg_match('/\b(?:minimum stok|min stok|minimal stok|stok minimum)\b/iu', $message)) {
            $warnings[] = 'Minimum stok tidak disebut, sistem memakai default `0`.';
        }

        $stockGood ??= 0;
        $stockLessGood ??= 0;
        $stockDamaged ??= 0;

        $categoryName = $this->extractField($message, ['kategori'], [
            'lokasi',
            'satuan',
            'unit',
            'minimum stok',
            'min stok',
            'minimal stok',
            'stok baik',
            'stok kurang baik',
            'stok rusak',
            'stok awal',
            'deskripsi',
            'keterangan',
            'catatan',
        ]);
        $locationName = $this->extractField($message, ['lokasi'], [
            'satuan',
            'unit',
            'minimum stok',
            'min stok',
            'minimal stok',
            'stok baik',
            'stok kurang baik',
            'stok rusak',
            'stok awal',
            'deskripsi',
            'keterangan',
            'catatan',
        ]);

        $category = $this->matchCategory($categoryName);
        $location = $this->matchLocation($locationName);

        if ($categoryName && ! $category) {
            $warnings[] = "Kategori `{$categoryName}` tidak ditemukan, barang akan disimpan tanpa kategori.";
        }

        if ($locationName && ! $location) {
            $warnings[] = "Lokasi `{$locationName}` tidak ditemukan, barang akan disimpan tanpa lokasi.";
        }

        if (! $sku && $name) {
            $sku = $this->generateSku($name);
            $warnings[] = "SKU tidak disebut, sistem membuat SKU otomatis `{$sku}`.";
        }

        $missing = [];

        if (! $name) {
            $missing[] = 'nama barang';
        }

        $attributes = [
            'name' => $name,
            'sku' => $sku,
            'category_id' => $category?->id,
            'storage_location_id' => $location?->id,
            'unit' => $unit,
            'minimum_stock' => $minimumStock,
            'initial_stock_good' => $stockGood,
            'initial_stock_less_good' => $stockLessGood,
            'initial_stock_damaged' => $stockDamaged,
            'description' => $description,
        ];

        $summary = array_values(array_filter([
            $name ? "Nama: {$name}" : null,
            $sku ? "SKU: {$sku}" : null,
            $category ? "Kategori: {$category->name}" : 'Kategori: tidak diisi',
            $location ? "Lokasi: {$location->name}" : 'Lokasi: tidak diisi',
            "Satuan: {$unit}",
            'Minimum stok: ' . $minimumStock,
            'Stok awal baik: ' . $stockGood,
            'Stok awal kurang baik: ' . $stockLessGood,
            'Stok awal rusak: ' . $stockDamaged,
        ]));

        return [
            'attributes' => $attributes,
            'warnings' => $warnings,
            'missing' => $missing,
            'summary' => $summary,
        ];
    }

    private function extractField(string $message, array $keywords, array $stoppers): ?string
    {
        $keywordPattern = implode('|', array_map(fn (string $keyword): string => preg_quote($keyword, '/'), $keywords));
        $stopperPattern = $stoppers === []
            ? '$'
            : '(?=\s+(?:' . implode('|', array_map(fn (string $stopper): string => preg_quote($stopper, '/'), $stoppers)) . ')\b|$)';

        $pattern = '/(?:' . $keywordPattern . ')\s*[:=-]?\s*(.+?)' . $stopperPattern . '/iu';

        if (! preg_match($pattern, $message, $matches)) {
            return null;
        }

        return Str::of($matches[1])
            ->replaceMatches('/\s+/', ' ')
            ->trim(" \t\n\r\0\x0B,.:;")
            ->toString() ?: null;
    }

    private function extractValue(string $message, string $pattern): ?string
    {
        if (! preg_match($pattern, $message, $matches)) {
            return null;
        }

        return trim((string) $matches[1]);
    }

    private function extractInteger(string $message, string $pattern): ?int
    {
        if (! preg_match($pattern, $message, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    private function matchCategory(?string $rawValue): ?Category
    {
        if (! $rawValue) {
            return null;
        }

        return $this->bestTextMatch(
            Category::query()->orderBy('name')->get(),
            $rawValue,
            fn (Category $category): string => $category->name,
        );
    }

    private function matchLocation(?string $rawValue): ?StorageLocation
    {
        if (! $rawValue) {
            return null;
        }

        return $this->bestTextMatch(
            StorageLocation::query()->orderBy('name')->get(),
            $rawValue,
            fn (StorageLocation $location): string => $location->name . ' ' . $location->code,
        );
    }

    /**
     * @template TModel of object
     *
     * @param  Collection<int, TModel>  $items
     * @param  callable(TModel): string  $resolver
     * @return TModel|null
     */
    private function bestTextMatch(Collection $items, string $rawValue, callable $resolver): ?object
    {
        $needle = Str::lower(Str::ascii($rawValue));

        foreach ($items as $item) {
            $candidate = Str::lower(Str::ascii($resolver($item)));

            if ($candidate === $needle) {
                return $item;
            }
        }

        foreach ($items as $item) {
            $candidate = Str::lower(Str::ascii($resolver($item)));

            if (str_contains($candidate, $needle) || str_contains($needle, $candidate)) {
                return $item;
            }
        }

        return null;
    }

    private function generateSku(string $name): string
    {
        $base = Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '-')
            ->trim('-')
            ->substr(0, 18)
            ->toString();

        $base = $base !== '' ? $base : 'ITEM';
        $counter = 1;

        do {
            $sku = sprintf('AI-%s-%03d', $base, $counter);
            $exists = Item::query()->where('sku', $sku)->exists();
            $counter++;
        } while ($exists);

        return $sku;
    }
}
