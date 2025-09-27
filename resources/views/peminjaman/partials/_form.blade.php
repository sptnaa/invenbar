@csrf
<div class="row mb-3">
    <div class="col-md-6">
        <x-form-input label="Nama Peminjam" name="nama_peminjam" :value="$peminjaman->nama_peminjam" required />
    </div>
    <div class="col-md-6">
        <x-form-input label="Email Peminjam" name="email_peminjam" type="email" :value="$peminjaman->email_peminjam" />
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <x-form-input label="Telepon Peminjam" name="telepon_peminjam" :value="$peminjaman->telepon_peminjam" />
    </div>
    <div class="col-md-6">
        @if(isset($update))
            <x-form-input label="Nomor Transaksi" :value="$peminjaman->nomor_transaksi" readonly />
        @endif
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <label class="form-label">Barang <span class="text-danger">*</span></label>
        <select name="barang_id" id="barang_id" class="form-select" required>
            <option value="">Pilih Barang</option>
            @foreach($barangs as $barang)
                <option value="{{ $barang->id }}" 
                        data-kode="{{ $barang->kode_barang }}"
                        data-nama="{{ $barang->nama_barang }}"
                        data-kategori="{{ $barang->kategori->nama_kategori }}"
                        data-lokasi="{{ $barang->lokasi->nama_lokasi }}"
                        data-stok="{{ $barang->stok_tersedia }}"
                        data-satuan="{{ $barang->satuan }}"
                        {{ old('barang_id', $peminjaman->barang_id) == $barang->id ? 'selected' : '' }}>
                    {{ $barang->kode_barang }} - {{ $barang->nama_barang }} (Stok: {{ $barang->stok_tersedia }})
                </option>
            @endforeach
        </select>
        @error('barang_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Info Barang -->
<div class="row mb-3" id="info-barang" style="display: none;">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">Informasi Barang</h6>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Kode:</strong> <span id="info-kode">-</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Kategori:</strong> <span id="info-kategori">-</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Lokasi:</strong> <span id="info-lokasi">-</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Stok Tersedia:</strong> <span id="info-stok" class="badge bg-success">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <x-form-input label="Jumlah Pinjam" name="jumlah_pinjam" type="number" min="1" 
                      :value="$peminjaman->jumlah_pinjam" required />
        <small class="text-muted">Maksimal sesuai stok tersedia</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">Keperluan</label>
        <textarea name="keperluan" class="form-control" rows="3" 
                  placeholder="Jelaskan keperluan peminjaman...">{{ old('keperluan', $peminjaman->keperluan) }}</textarea>
        @error('keperluan')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        @php
            $tanggalPinjam = old('tanggal_pinjam', $peminjaman->tanggal_pinjam 
                ? $peminjaman->tanggal_pinjam->format('Y-m-d\TH:i') 
                : null); // <-- biarkan null biar kosong
        @endphp
        <x-form-input label="Tanggal Pinjam" name="tanggal_pinjam" type="datetime-local" 
                      :value="$tanggalPinjam" required />
    </div>
    <div class="col-md-6">
        @php
            $tanggalKembali = old('tanggal_kembali_rencana', $peminjaman->tanggal_kembali_rencana 
                ? $peminjaman->tanggal_kembali_rencana->format('Y-m-d\TH:i') 
                : null); // <-- biarkan null juga
        @endphp
        <x-form-input label="Tanggal Kembali Rencana" name="tanggal_kembali_rencana" type="datetime-local" 
                      :value="$tanggalKembali" required />
    </div>
</div>


@if(isset($update))
<div class="row mb-3">
    <div class="col-md-12">
        <label class="form-label">Keterangan Tambahan</label>
        <textarea name="keterangan" class="form-control" rows="3" 
                  placeholder="Keterangan tambahan...">{{ old('keterangan', $peminjaman->keterangan) }}</textarea>
        @error('keterangan')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
@endif

<div class="mt-4">
    <x-primary-button>
        {{ isset($update) ? __('Update') : __('Simpan') }}
    </x-primary-button>
    <x-tombol-kembali :href="route('peminjaman.index')" />
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const barangSelect = document.getElementById('barang_id');
    const infoBarang = document.getElementById('info-barang');
    const jumlahInput = document.querySelector('input[name="jumlah_pinjam"]');
    
    // Show info when barang is selected
    barangSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            document.getElementById('info-kode').textContent = selectedOption.dataset.kode;
            document.getElementById('info-kategori').textContent = selectedOption.dataset.kategori;
            document.getElementById('info-lokasi').textContent = selectedOption.dataset.lokasi;
            
            const stok = parseInt(selectedOption.dataset.stok);
            const stokBadge = document.getElementById('info-stok');
            stokBadge.textContent = stok + ' ' + selectedOption.dataset.satuan;
            stokBadge.className = stok > 0 ? 'badge bg-success' : 'badge bg-danger';
            
            // Set max quantity
            jumlahInput.setAttribute('max', stok);
            
            infoBarang.style.display = 'block';
        } else {
            infoBarang.style.display = 'none';
        }
    });
    
    // Trigger change if there's already a selected value
    if (barangSelect.value) {
        barangSelect.dispatchEvent(new Event('change'));
    }
    
    // Validate quantity on input
    jumlahInput.addEventListener('input', function() {
        const max = parseInt(this.getAttribute('max')) || 0;
        const value = parseInt(this.value) || 0;
        
        if (value > max) {
            this.setCustomValidity(`Jumlah tidak boleh lebih dari ${max}`);
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>