@csrf

@if(isset($update))
{{-- Form Edit Mode --}}
<div class="alert alert-info">
    <strong>Nomor Perbaikan:</strong> {{ $perbaikan->nomor_perbaikan }}<br>
    <strong>Barang:</strong> {{ $perbaikan->barang->nama_barang }} ({{ $perbaikan->barang->kode_barang }})<br>
    <strong>Jumlah:</strong> {{ $perbaikan->jumlah_rusak }} {{ $perbaikan->barang->satuan }}<br>
    <strong>Tingkat Kerusakan:</strong> <span class="badge {{ $perbaikan->kerusakan_badge_class }}">{{ $perbaikan->tingkat_kerusakan }}</span>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
            <option value="Menunggu" {{ old('status', $perbaikan->status) == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
            <option value="Dalam Perbaikan" {{ old('status', $perbaikan->status) == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
            <option value="Selesai" {{ old('status', $perbaikan->status) == 'Selesai' ? 'selected' : '' }}>Selesai</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6" id="tanggalSelesaiContainer">
        <label class="form-label">Tanggal Selesai</label>
        <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
            value="{{ old('tanggal_selesai', $perbaikan->tanggal_selesai ? $perbaikan->tanggal_selesai->format('Y-m-d') : '') }}">
        @error('tanggal_selesai')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Kosongkan untuk menggunakan tanggal hari ini</small>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Catatan Perbaikan</label>
    <textarea name="catatan_perbaikan" class="form-control @error('catatan_perbaikan') is-invalid @enderror" 
        rows="4" placeholder="Masukkan catatan perbaikan...">{{ old('catatan_perbaikan', $perbaikan->catatan_perbaikan) }}</textarea>
    @error('catatan_perbaikan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Biaya Perbaikan (Rp)</label>
    <input type="number" name="biaya_perbaikan" class="form-control @error('biaya_perbaikan') is-invalid @enderror" 
        value="{{ old('biaya_perbaikan', $perbaikan->biaya_perbaikan) }}" min="0" step="1000" placeholder="0">
    @error('biaya_perbaikan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="alert alert-warning" id="alertSelesai" style="display: none;">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Perhatian!</strong> Jika status diubah menjadi "Selesai", maka:
    <ul class="mb-0 mt-2">
        <li>Barang sebanyak <strong>{{ $perbaikan->jumlah_rusak }} {{ $perbaikan->barang->satuan }}</strong> akan dipindahkan ke stok kondisi "Baik"</li>
        <li>Stok <strong>{{ $perbaikan->tingkat_kerusakan }}</strong> akan berkurang otomatis</li>
        <li>Status tidak dapat diubah kembali</li>
    </ul>
</div>

@else
{{-- Form Create Mode --}}
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Informasi:</strong> Form ini digunakan untuk menambahkan data perbaikan secara manual. Perbaikan juga akan otomatis dibuat saat barang dikembalikan dalam kondisi rusak.
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Barang <span class="text-danger">*</span></label>
        <select name="barang_id" id="barang_id" class="form-control @error('barang_id') is-invalid @enderror" required>
            <option value="">-- Pilih Barang --</option>
            @foreach($barangs as $barang)
                <option value="{{ $barang->id }}" 
                    data-rusak-ringan="{{ $barang->jumlah_rusak_ringan }}"
                    data-rusak-berat="{{ $barang->jumlah_rusak_berat }}"
                    {{ old('barang_id', $perbaikan->barang_id) == $barang->id ? 'selected' : '' }}>
                    {{ $barang->kode_barang }} - {{ $barang->nama_barang }}
                    (Rusak Ringan: {{ $barang->jumlah_rusak_ringan }}, Rusak Berat: {{ $barang->jumlah_rusak_berat }})
                </option>
            @endforeach
        </select>
        @error('barang_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Tingkat Kerusakan <span class="text-danger">*</span></label>
        <select name="tingkat_kerusakan" id="tingkat_kerusakan" class="form-control @error('tingkat_kerusakan') is-invalid @enderror" required>
            <option value="">-- Pilih Tingkat --</option>
            <option value="Rusak Ringan" {{ old('tingkat_kerusakan', $perbaikan->tingkat_kerusakan) == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
            <option value="Rusak Berat" {{ old('tingkat_kerusakan', $perbaikan->tingkat_kerusakan) == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
        </select>
        @error('tingkat_kerusakan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted" id="stokInfo"></small>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Jumlah Rusak <span class="text-danger">*</span></label>
        <input type="number" name="jumlah_rusak" class="form-control @error('jumlah_rusak') is-invalid @enderror" 
            value="{{ old('jumlah_rusak', $perbaikan->jumlah_rusak) }}" min="1" required>
        @error('jumlah_rusak')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
        <input type="date" name="tanggal_masuk" class="form-control @error('tanggal_masuk') is-invalid @enderror" 
            value="{{ old('tanggal_masuk', $perbaikan->tanggal_masuk ? $perbaikan->tanggal_masuk->format('Y-m-d') : date('Y-m-d')) }}" required>
        @error('tanggal_masuk')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Keterangan Kerusakan</label>
    <textarea name="keterangan_kerusakan" class="form-control @error('keterangan_kerusakan') is-invalid @enderror" 
        rows="4" placeholder="Jelaskan detail kerusakan barang...">{{ old('keterangan_kerusakan', $perbaikan->keterangan_kerusakan) }}</textarea>
    @error('keterangan_kerusakan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
@endif

<div class="mt-4">
    <x-primary-button>
        {{ isset($update) ? __('Update') : __('Simpan') }}
    </x-primary-button>

    <x-tombol-kembali :href="route('perbaikan.index')" />
</div>

@if(isset($update))
    {{-- Script untuk Edit Mode --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const alertSelesai = document.getElementById('alertSelesai');
            const tanggalSelesaiContainer = document.getElementById('tanggalSelesaiContainer');

            function updateVisibility() {
                if (statusSelect.value === 'Selesai') {
                    alertSelesai.style.display = 'block';
                    tanggalSelesaiContainer.style.display = 'block';
                } else {
                    alertSelesai.style.display = 'none';
                    if (statusSelect.value === 'Menunggu') {
                        tanggalSelesaiContainer.style.display = 'none';
                    } else {
                        tanggalSelesaiContainer.style.display = 'block';
                    }
                }
            }

            statusSelect.addEventListener('change', updateVisibility);
            updateVisibility();
        });
    </script>
@else
    {{-- Script untuk Create Mode --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const barangSelect = document.getElementById('barang_id');
            const tingkatSelect = document.getElementById('tingkat_kerusakan');
            const stokInfo = document.getElementById('stokInfo');

            function updateStokInfo() {
                const selectedOption = barangSelect.options[barangSelect.selectedIndex];
                const tingkat = tingkatSelect.value;

                if (selectedOption.value && tingkat) {
                    const rusakRingan = selectedOption.getAttribute('data-rusak-ringan');
                    const rusakBerat = selectedOption.getAttribute('data-rusak-berat');

                    if (tingkat === 'Rusak Ringan') {
                        stokInfo.textContent = `Stok rusak ringan tersedia: ${rusakRingan} unit`;
                        stokInfo.className = rusakRingan > 0 ? 'text-success' : 'text-danger';
                    } else {
                        stokInfo.textContent = `Stok rusak berat tersedia: ${rusakBerat} unit`;
                        stokInfo.className = rusakBerat > 0 ? 'text-success' : 'text-danger';
                    }
                } else {
                    stokInfo.textContent = '';
                }
            }

            barangSelect.addEventListener('change', updateStokInfo);
            tingkatSelect.addEventListener('change', updateStokInfo);
            updateStokInfo();
        });
    </script>
@endif