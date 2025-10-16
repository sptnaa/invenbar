@csrf

{{-- Mode Input --}}
<div class="card mb-3 border-primary">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">⚙️ Mode Input Barang</h6>
    </div>
    <div class="card-body">
        <x-form-select
            label="Pilih Mode Input"
            name="mode_input"
            :value="$barang->mode_input ?? 'masal'"
            :option-data="[
                ['id' => 'masal', 'nama' => 'Masal (1 Kode untuk Banyak Unit)'],
                ['id' => 'unit', 'nama' => 'Per Unit (1 Kode per 1 Unit)'],
            ]"
            option-label="nama"
            option-value="id"
            required />
        <small class="text-muted">
            <strong>Masal:</strong> Input normal, 1 kode untuk semua unit.<br>
            <strong>Per Unit:</strong> Sistem akan generate kode otomatis untuk setiap unit.
        </small>
    </div>
</div>

{{-- Baris 1: Kode & Nama Barang --}}
<div class="row mb-3">
    <div class="col-md-6">
        <x-form-input
            label="Kode Barang"
            name="kode_barang"
            :value="$barang->kode_barang"
            placeholder="Contoh: PJTR01"
            required />
        <small class="text-muted" id="kode-hint">
            Mode Unit: Kode akan digenerate otomatis (PJTR01, PJTR02, ...)
        </small>
    </div>
    <div class="col-md-6">
        <x-form-input
            label="Nama Barang"
            name="nama_barang"
            :value="$barang->nama_barang"
            required />
    </div>
</div>

{{-- Baris 2: Kategori & Lokasi --}}
<div class="row mb-3">
    <div class="col-md-6">
        <x-form-select
            label="Kategori"
            name="kategori_id"
            :value="$barang->kategori_id"
            :option-data="$kategori"
            option-label="nama_kategori"
            option-value="id"
            required />
    </div>
    <div class="col-md-6">
        <x-form-select
            label="Lokasi"
            name="lokasi_id"
            :value="$barang->lokasi_id"
            :option-data="$lokasi"
            option-label="nama_lokasi"
            option-value="id"
            required />
    </div>
</div>

{{-- Baris 3: Satuan & Tanggal Pengadaan --}}
<div class="row mb-3">
    <div class="col-md-6">
        <x-form-input
            label="Satuan"
            name="satuan"
            :value="$barang->satuan"
            placeholder="cth: Unit, Buah, Set"
            required />
    </div>
    <div class="col-md-6">
        @php
        $tanggal = $barang->tanggal_pengadaan
        ? date('Y-m-d', strtotime($barang->tanggal_pengadaan))
        : null;
        @endphp
        <x-form-input
            label="Tanggal Pengadaan"
            name="tanggal_pengadaan"
            type="date"
            :value="$tanggal"
            required />
    </div>
</div>

{{-- Baris 4: Sumber --}}
<div class="row mb-3">
    <div class="col-md-6">
        <x-form-select
            label="Sumber Barang"
            name="sumber"
            :value="$barang->sumber"
            :option-data="[
                ['id' => 'Pemerintah', 'nama' => 'Pemerintah'],
                ['id' => 'Swadaya', 'nama' => 'Swadaya'],
                ['id' => 'Mitra', 'nama' => 'Mitra'],
            ]"
            option-label="nama"
            option-value="id"
            required />
    </div>
</div>

{{-- Detail Kondisi Barang --}}
<div class="card mb-3 border" id="kondisi-section">
    <div class="card-header bg-light">
        <h6 class="mb-0 text-primary">📊 Detail Kondisi Barang</h6>
        <small class="text-muted">Masukkan jumlah barang untuk setiap kondisi</small>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded" style="border-left: 4px solid #28a745 !important;">
                    <label class="form-label text-success fw-bold">✓ Kondisi Baik</label>
                    <input
                        type="number"
                        class="form-control kondisi-input"
                        name="jumlah_baik"
                        value="{{ $barang->jumlah_baik ?? 0 }}"
                        min="0"
                        placeholder="0">
                    <small class="text-muted">Barang dalam kondisi sempurna</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded" style="border-left: 4px solid #ffc107 !important;">
                    <label class="form-label text-warning fw-bold">⚠️ Rusak Ringan</label>
                    <input
                        type="number"
                        class="form-control kondisi-input"
                        name="jumlah_rusak_ringan"
                        value="{{ $barang->jumlah_rusak_ringan ?? 0 }}"
                        min="0"
                        placeholder="0">
                    <small class="text-muted">Barang masih bisa digunakan</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded" style="border-left: 4px solid #dc3545 !important;">
                    <label class="form-label text-danger fw-bold">❌ Rusak Berat</label>
                    <input
                        type="number"
                        class="form-control kondisi-input"
                        name="jumlah_rusak_berat"
                        value="{{ $barang->jumlah_rusak_berat ?? 0 }}"
                        min="0"
                        placeholder="0">
                    <small class="text-muted">Barang tidak bisa digunakan</small>
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 bg-primary text-white rounded text-center">
            <h5 class="mb-1">Total Jumlah Barang</h5>
            <h2 class="mb-0" id="total-display">
                {{ ($barang->jumlah_baik ?? 0) + ($barang->jumlah_rusak_ringan ?? 0) + ($barang->jumlah_rusak_berat ?? 0) }}
            </h2>
            <small id="unit-info" class="text-white-50" style="display: none;">
                Akan membuat <span id="unit-count">0</span> data barang terpisah
            </small>
        </div>
    </div>
</div>

{{-- Checkbox Barang Bisa Dipinjam --}}
<div class="mb-3 form-check">
    <input
        type="checkbox"
        class="form-check-input"
        id="is_pinjaman"
        name="is_pinjaman"
        value="1"
        {{ old('is_pinjaman', $barang->is_pinjaman ?? false) ? 'checked' : '' }}>
    <label class="form-check-label fw-bold" for="is_pinjaman">
        Barang Bisa Dipinjam
    </label>
    <small class="text-muted d-block">Centang jika barang ini dapat dipinjamkan</small>
</div>

{{-- Upload Gambar --}}
<div class="mb-3">
    <x-form-input
        label="Gambar Barang"
        name="gambar"
        type="file" />
</div>

{{-- Tombol Aksi --}}
<div class="mt-4">
    <x-primary-button>
        {{ isset($update) ? __('Update') : __('Simpan') }}
    </x-primary-button>
    <x-tombol-kembali :href="route('barang.index')" />
</div>

{{-- Script Enhanced --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modeInput = document.querySelector('select[name="mode_input"]');
        const kondisiInputs = document.querySelectorAll('.kondisi-input');
        const totalDisplay = document.getElementById('total-display');
        const kondisiSection = document.getElementById('kondisi-section');
        const kodeHint = document.getElementById('kode-hint');
        const unitInfo = document.getElementById('unit-info');
        const unitCount = document.getElementById('unit-count');

        function updateTotal() {
            let total = 0;
            kondisiInputs.forEach(input => total += parseInt(input.value) || 0);
            totalDisplay.textContent = total;
            unitCount.textContent = total;

            totalDisplay.style.transform = 'scale(1.1)';
            setTimeout(() => totalDisplay.style.transform = 'scale(1)', 200);
        }

        function toggleModeUI() {
            const isUnitMode = modeInput.value === 'unit';

            if (isUnitMode) {
                kondisiSection.classList.add('border-warning');
                kodeHint.style.display = 'block';
                unitInfo.style.display = 'block';

                // Set semua kondisi ke Baik dan disable rusak
                kondisiInputs[0].value = kondisiInputs[0].value || 1;
                kondisiInputs[1].value = 0;
                kondisiInputs[2].value = 0;
                kondisiInputs[1].readOnly = true;
                kondisiInputs[2].readOnly = true;

            } else {
                kondisiSection.classList.remove('border-warning');
                kodeHint.style.display = 'none';
                unitInfo.style.display = 'none';
                kondisiInputs[1].readOnly = true;
                kondisiInputs[2].readOnly = true;

            }

            updateTotal();
        }

        modeInput.addEventListener('change', toggleModeUI);
        kondisiInputs.forEach(input => {
            input.addEventListener('input', updateTotal);
            input.addEventListener('change', updateTotal);
        });

        toggleModeUI();
    });
</script>