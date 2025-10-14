<div class="row mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Detail Peminjaman: {{ $peminjaman->nomor_transaksi }}</h5>
        @php
            $badgeClass = match($peminjaman->status) {
                'Sedang Dipinjam' => 'bg-warning text-dark',
                'Terlambat' => 'bg-danger',
                'Sudah Dikembalikan' => 'bg-success',
                default => 'bg-secondary'
            };
        @endphp
        <span class="badge {{ $badgeClass }} fs-6">{{ $peminjaman->status }}</span>
    </div>
</div>

<div class="row">
    {{-- Info Peminjam --}}
    <div class="col-md-6">
        <h6 class="text-primary mb-3">
            <i class="fas fa-user"></i> Informasi Peminjam
        </h6>
        <table class="table table-borderless">
            <tr><th>Nama</th><td>{{ $peminjaman->nama_peminjam }}</td></tr>
            @if($peminjaman->email_peminjam)
                <tr><th>Email</th><td>{{ $peminjaman->email_peminjam }}</td></tr>
            @endif
            @if($peminjaman->telepon_peminjam)
                <tr><th>Telepon</th><td>{{ $peminjaman->telepon_peminjam }}</td></tr>
            @endif
            @if($peminjaman->keperluan)
                <tr><th>Keperluan</th><td>{{ $peminjaman->keperluan }}</td></tr>
            @endif
        </table>
    </div>

    {{-- Info Barang --}}
    <div class="col-md-6">
        <h6 class="text-primary mb-3">
            <i class="fas fa-box"></i> Informasi Barang
        </h6>
        <table class="table table-borderless">
            <tr><th>Nama Barang</th><td>{{ $peminjaman->barang->nama_barang }}</td></tr>
            <tr><th>Kode Barang</th><td>{{ $peminjaman->barang->kode_barang }}</td></tr>
            <tr><th>Kategori</th><td>{{ $peminjaman->barang->kategori->nama_kategori }}</td></tr>
            <tr><th>Lokasi</th><td>{{ $peminjaman->barang->lokasi->nama_lokasi }}</td></tr>
            <tr><th>Jumlah Pinjam</th><td>{{ $peminjaman->jumlah_pinjam }} {{ $peminjaman->barang->satuan }}</td></tr>

            {{-- Kondisi Barang --}}
            <tr>
                <th>Kondisi Barang (Master)</th>
                <td>
                    @php
                        $kondisiBadge = match($peminjaman->barang->kondisi) {
                            'Rusak Ringan' => 'bg-warning text-dark',
                            'Rusak Berat' => 'bg-danger',
                            default => 'bg-success'
                        };
                    @endphp
                    <span class="badge {{ $kondisiBadge }}">{{ $peminjaman->barang->kondisi }}</span>
                </td>
            </tr>
            <tr>
                <th>Kondisi Saat Dikembalikan</th>
                <td>
                    @if($peminjaman->kondisi_barang)
                        @php
                            $badge = match($peminjaman->kondisi_barang) {
                                'Rusak Ringan' => 'bg-warning text-dark',
                                'Rusak Berat' => 'bg-danger',
                                default => 'bg-success'
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ $peminjaman->kondisi_barang }}</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

<hr>

{{-- Info Waktu --}}
<h6 class="text-primary mb-3">
    <i class="fas fa-calendar"></i> Informasi Waktu
</h6>
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6>Tanggal Pinjam</h6>
                <h5 class="text-primary">{{ $peminjaman->tanggal_pinjam->format('d M Y, H:i') }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6>Rencana Kembali</h6>
                <h5 class="text-warning">{{ $peminjaman->tanggal_kembali_rencana->format('d M Y, H:i') }}</h5>
            </div>
        </div>
    </div>
    @if($peminjaman->tanggal_kembali_aktual)
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6>Tanggal Kembali</h6>
                <h5 class="text-success">{{ $peminjaman->tanggal_kembali_aktual->format('d M Y, H:i') }}</h5>
            </div>
        </div>
    </div>
    @endif
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6>Durasi Peminjaman</h6>
                <h5 class="text-info">{{ $peminjaman->durasi_peminjaman }} Hari</h5>
            </div>
        </div>
    </div>
</div>

{{-- Jika Terlambat --}}
@if($peminjaman->hari_terlambat > 0)
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Peminjaman Terlambat!</strong>
        @if($peminjaman->status === 'Sudah Dikembalikan')
            Barang dikembalikan terlambat {{ $peminjaman->hari_terlambat }} hari.
        @else
            Barang sudah terlambat {{ $peminjaman->hari_terlambat }} hari dari jadwal.
        @endif
    </div>
@endif

{{-- Keterangan --}}
@if($peminjaman->keterangan)
    <hr>
    <h6 class="text-primary">Keterangan</h6>
    <p class="text-muted">{{ $peminjaman->keterangan }}</p>
@endif

{{-- Timeline --}}
<hr>
<h6 class="text-primary mb-3">
    <i class="fas fa-history"></i> Timeline
</h6>
<div class="timeline">
    <div class="timeline-item">
        <div class="timeline-marker bg-primary"></div>
        <div class="timeline-content">
            <h6>Peminjaman Dibuat</h6>
            <p class="text-muted">{{ $peminjaman->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>
    @if($peminjaman->updated_at != $peminjaman->created_at)
        <div class="timeline-item">
            <div class="timeline-marker bg-info"></div>
            <div class="timeline-content">
                <h6>Data Diupdate</h6>
                <p class="text-muted">{{ $peminjaman->updated_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
    @endif
    @if($peminjaman->tanggal_kembali_aktual)
        <div class="timeline-item">
            <div class="timeline-marker bg-success"></div>
            <div class="timeline-content">
                <h6>Barang Dikembalikan</h6>
                <p class="text-muted">{{ $peminjaman->tanggal_kembali_aktual->format('d M Y') }}</p>
            </div>
        </div>
    @endif
</div>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-marker {
    position: absolute;
    left: -15px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}
.timeline-content {
    margin-left: 20px;
}
.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}
.timeline-content p {
    margin-bottom: 0;
    font-size: 0.875rem;
}
</style>
