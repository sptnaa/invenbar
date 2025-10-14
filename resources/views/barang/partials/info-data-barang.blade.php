<table class="table table-bordered table-striped">
    <tbody>
        <tr>
            <th style="width: 30%;">Nama Barang</th>
            <td>{{ $barang->nama_barang }}</td>
        </tr>
        <tr>
            <th>Kategori</th>
            <td>{{ $barang->kategori->nama_kategori }}</td>
        </tr>
        <tr>
            <th>Lokasi</th>
            <td>{{ $barang->lokasi->nama_lokasi }}</td>
        </tr>
        <tr>
            <th>Stok Tersedia</th>
            <td><strong>{{ $barang->stok_tersedia }}</strong> {{ $barang->satuan }}</td>
        </tr>
        <tr>
            <th>Total Jumlah</th>
            <td>{{ $barang->jumlah }} {{ $barang->satuan }}</td>
        </tr>
        <tr>
            <th>Kondisi Umum</th>
            <td>
                @php
                $badgeClass = 'bg-success';
                if ($barang->kondisi == 'Rusak Ringan') {
                $badgeClass = 'bg-warning text-dark';
                }
                if ($barang->kondisi == 'Rusak Berat') {
                $badgeClass = 'bg-danger';
                }
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $barang->kondisi }}</span>
            </td>
        </tr>
        <tr>
            <th>Sumber</th>
            <td>{{ $barang->sumber ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal Pengadaan</th>
            <td>{{ \Carbon\Carbon::parse($barang->tanggal_pengadaan)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <th>Terakhir Diperbarui</th>
            <td>{{ $barang->updated_at->translatedFormat('d F Y, H:i') }}</td>
        </tr>
        <tr>
            <th>Status Barang</th>
            <td>
                @if ($barang->sedang_dipinjam)
                <span class="badge bg-warning text-dark">Sedang Dipinjam</span>
                @elseif ($barang->sedang_diperbaiki)
                <span class="badge bg-danger text-white">Dalam Perbaikan</span>
                @else
                <span class="badge bg-success">Tersedia</span>
                @endif
            </td>
        </tr>

    </tbody>
</table>

<!-- Tambahkan Breakdown Kondisi -->
<div class="mt-4">
    <h6 class="text-primary mb-3">Detail Kondisi Barang</h6>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="text-success">{{ $barang->jumlah_baik ?? 0 }}</h5>
                    <small class="text-muted">Kondisi Baik</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h5 class="text-warning">{{ $barang->jumlah_rusak_ringan ?? 0 }}</h5>
                    <small class="text-muted">Rusak Ringan</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h5 class="text-danger">{{ $barang->jumlah_rusak_berat ?? 0 }}</h5>
                    <small class="text-muted">Rusak Berat</small>
                </div>
            </div>
        </div>
    </div>
</div>