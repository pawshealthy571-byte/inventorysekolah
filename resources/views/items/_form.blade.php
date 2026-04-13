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
        <label for="category_id">Kategori</label>
        <select class="select" id="category_id" name="category_id">
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $item->category_id ?? '') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
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
            <label for="initial_stock">Stok awal</label>
            <input class="input" id="initial_stock" name="initial_stock" type="number" min="0" value="{{ old('initial_stock', 0) }}" required>
            <small>Jika lebih dari 0, sistem otomatis membuat mutasi stok masuk.</small>
        </div>
    @else
        <div class="field">
            <label>Stok saat ini</label>
            <div class="input" style="display: flex; align-items: center;">{{ number_format($item->stock, 0, ',', '.') }} {{ $item->unit }}</div>
            <small>Perubahan stok dilakukan dari menu mutasi stok.</small>
        </div>
    @endif

    <div class="field">
        <label for="condition_status">Kondisi barang</label>
        <select class="select" id="condition_status" name="condition_status" required>
            <option value="baik" @selected(old('condition_status', $item->condition_status ?? 'baik') === 'baik')>Baik</option>
            <option value="perlu-perawatan" @selected(old('condition_status', $item->condition_status ?? '') === 'perlu-perawatan')>Perlu Perawatan</option>
            <option value="rusak-ringan" @selected(old('condition_status', $item->condition_status ?? '') === 'rusak-ringan')>Rusak Ringan</option>
        </select>
    </div>

    <div class="field-wide">
        <label for="description">Deskripsi</label>
        <textarea class="textarea" id="description" name="description">{{ old('description', $item->description ?? '') }}</textarea>
    </div>
</div>
