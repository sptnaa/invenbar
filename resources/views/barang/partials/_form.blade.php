@csrf
<div class="row mb-3">
    <div class="col-md-6">
        <x-form-input label="Kode Barang" name="kode_barang" :value="$barang->kode_barang" />
    </div>
    <div class="col-md-6">
        <x-form-input label="Nama Barang" name="nama_barang" :value="$barang->nama_barang" />
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <x-form-select label="Kategori" name="kategori_id" :value="$barang->kategori_id"
            :option-data="$kategori" option-label="nama_kategori" option-value="id" />
    </div>
    <div class="col-md-6">
        <x-form-select label="Lokasi" name="lokasi_id" :value="$barang->lokasi_id"
            :option-data="$lokasi" option-label="nama_lokasi" option-value="id" />
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <x-form-input label="Satuan" name="satuan" :value="$barang->satuan" />
    </div>
    <div class="col-md-6">
        @php
        $tanggal = $barang->tanggal_pengadaan
            ? date('Y-m-d', strtotime($barang->tanggal_pengadaan))
            : null;
        @endphp
        <x-form-input label="Tanggal Pengadaan" name="tanggal_pengadaan" type="date" :value="$tanggal" />
    </div>
</div>

<!-- Detail Kondisi Barang -->
<div class="card mb-3" style="border: 1px solid #dee2e6;">
    <div class="card-header bg-light">
        <h6 class="mb-0 text-primary">📊 Detail Kondisi Barang</h6>
        <small class="text-muted">Masukkan jumlah barang untuk setiap kondisi</small>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded" style="border-left: 4px solid #28a745 !important;">
                    <label class="form-label text-success fw-bold">✓ Kondisi Baik</label>
                    <input type="number" class="form-control kondisi-input" name="jumlah_baik"
                        value="{{ $barang->jumlah_baik ?? 0 }}" min="0" placeholder="0">
                    <small class="text-muted">Barang dalam kondisi sempurna</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded" style="border-left: 4px solid #ffc107 !important;">
                    <label class="form-label text-warning fw-bold">⚠️ Rusak Ringan</label>
                    <input type="number" class="form-control kondisi-input" name="jumlah_rusak_ringan"
                        value="{{ $barang->jumlah_rusak_ringan ?? 0 }}" min="0" placeholder="0">
                    <small class="text-muted">Barang masih bisa digunakan</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded" style="border-left: 4px solid #dc3545 !important;">
                    <label class="form-label text-danger fw-bold">❌ Rusak Berat</label>
                    <input type="number" class="form-control kondisi-input" name="jumlah_rusak_berat"
                        value="{{ $barang->jumlah_rusak_berat ?? 0 }}" min="0" placeholder="0">
                    <small class="text-muted">Barang tidak bisa digunakan</small>
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 bg-primary text-white rounded text-center">
            <h5 class="mb-1">Total Jumlah Barang</h5>
            <h2 class="mb-0" id="total-display">
                {{ ($barang->jumlah_baik ?? 0) + ($barang->jumlah_rusak_ringan ?? 0) + ($barang->jumlah_rusak_berat ?? 0) }}
            </h2>
        </div>
    </div>
</div>

<!-- Checkbox Barang Bisa Dipinjam -->
<div class="mb-3 form-check">
    <input type="checkbox"
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

<div class="mb-3">
    <x-form-input label="Gambar Barang" name="gambar" type="file" />
</div>

<div class="mt-4">
    <x-primary-button>
        {{ isset($update) ? __('Update') : __('Simpan') }}
    </x-primary-button>

    <x-tombol-kembali :href="route('barang.index')" />
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kondisiInputs = document.querySelectorAll('.kondisi-input');
        const totalDisplay = document.getElementById('total-display');

        function updateTotal() {
            let total = 0;
            kondisiInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                total += value;
            });
            totalDisplay.textContent = total;

            // Add animation effect
            totalDisplay.style.transform = 'scale(1.1)';
            setTimeout(() => {
                totalDisplay.style.transform = 'scale(1)';
            }, 200);
        }

        kondisiInputs.forEach(input => {
            input.addEventListener('input', updateTotal);
            input.addEventListener('change', updateTotal);
        });
    });
</script>
