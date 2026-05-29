@php
    $editing = isset($item);
@endphp

<div class="form-grid">
    <div class="field">
        <label for="name">Nama barang</label>
        <input class="input" id="name" name="name" type="text" value="{{ old('name', $item->name ?? '') }}" required>
    </div>

    <div class="field">
        <label for="sku">SKU</label>
        <input class="input" id="sku" name="sku" type="text" value="{{ old('sku', $item->sku ?? '') }}" required>
    </div>



    <div class="field">
        <label for="storage_location_id">Lokasi penyimpanan</label>
        <select class="select" id="storage_location_id" name="storage_location_id">
            <option value="">Pilih lokasi</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected((string) old('storage_location_id', $item->storage_location_id ?? '') === (string) $location->id)>
                    {{ $location->name }} ({{ $location->code }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="unit">Satuan</label>
        <input class="input" id="unit" name="unit" type="text" value="{{ old('unit', $item->unit ?? '') }}" required>
    </div>

    <div class="field">
        <label for="minimum_stock">Minimum stok</label>
        <input class="input" id="minimum_stock" name="minimum_stock" type="number" min="0" value="{{ old('minimum_stock', $item->minimum_stock ?? 0) }}" required>
    </div>

    @if (! $editing)
        <div class="field">
            <label for="initial_stock_good">Stok awal barang baik</label>
            <input class="input" id="initial_stock_good" name="initial_stock_good" type="number" min="0" value="{{ old('initial_stock_good', 0) }}" required>
        </div>

        <div class="field">
            <label for="initial_stock_less_good">Stok awal barang kurang baik</label>
            <input class="input" id="initial_stock_less_good" name="initial_stock_less_good" type="number" min="0" value="{{ old('initial_stock_less_good', 0) }}" required>
        </div>

        <div class="field">
            <label for="initial_stock_damaged">Stok awal barang rusak</label>
            <input class="input" id="initial_stock_damaged" name="initial_stock_damaged" type="number" min="0" value="{{ old('initial_stock_damaged', 0) }}" required>
            <small>Setiap stok awal otomatis tercatat sebagai mutasi masuk sesuai kondisi.</small>
        </div>
    @else
        <div class="field">
            <label>Stok saat ini</label>
            <div class="input" style="display: flex; align-items: center;">{{ number_format($item->stock, 0, ',', '.') }} {{ $item->unit }}</div>
            <small>Perubahan stok dilakukan dari menu mutasi stok.</small>
        </div>

        <div class="field">
            <label>Barang baik</label>
            <div class="input" style="display: flex; align-items: center;">{{ number_format($item->stock_good, 0, ',', '.') }} {{ $item->unit }}</div>
        </div>

        <div class="field">
            <label>Barang kurang baik</label>
            <div class="input" style="display: flex; align-items: center;">{{ number_format($item->stock_less_good, 0, ',', '.') }} {{ $item->unit }}</div>
        </div>

        <div class="field">
            <label>Barang rusak</label>
            <div class="input" style="display: flex; align-items: center;">{{ number_format($item->stock_damaged, 0, ',', '.') }} {{ $item->unit }}</div>
        </div>
    @endif

    <div class="field-wide">
        <label for="condition_description">Deskripsi Kondisi</label>
        <textarea class="textarea" id="condition_description" name="condition_description" placeholder="Contoh: Kenapa rusak atau kurang baik?">{{ old('condition_description', $item->condition_description ?? '') }}</textarea>
    </div>

    <div class="field-wide">
        <label for="description">Deskripsi</label>
        <textarea class="textarea" id="description" name="description">{{ old('description', $item->description ?? '') }}</textarea>
    </div>

    <div class="field-wide">
        <label for="image">Foto Barang</label>
        <input class="input" type="file" id="image" name="image" accept="image/*">
        <small id="image-warning" style="color: #ef4444; display: none; margin-top: 4px; font-weight: 600;">⚠️ Ukuran file terlalu besar! Maksimal 2MB.</small>
        @if($editing && $item->image)
            <div style="margin-top: 10px;">
                <img src="{{ Storage::url($item->image) }}" alt="Foto Barang" style="max-height: 150px; border-radius: 8px; object-fit: cover;">
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('image').addEventListener('change', function() {
        const file = this.files[0];
        const warning = document.getElementById('image-warning');
        if (file && file.size > 2 * 1024 * 1024) {
            warning.style.display = 'block';
            alert('Peringatan: Ukuran file yang Anda pilih (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB) melebihi batas maksimal 2MB. Silakan pilih file yang lebih kecil atau kompres terlebih dahulu.');
            this.value = ''; // Reset the input
            warning.style.display = 'none';
        } else {
            warning.style.display = 'none';
        }
    });
</script>
@endpush
