<div class="alert alert-info">
    <i class="fas fa-tools me-2"></i>
    <strong>Informasi Perbaikan</strong>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <th width="30%">Nomor Perbaikan</th>
            <td>{{ $perbaikan->nomor_perbaikan }}</td>
        </tr>
        <tr>
            <th>Barang</th>
            <td>{{ $perbaikan->barang->nama_barang }} ({{ $perbaikan->barang->kode_barang }})</td>
        </tr>
        <tr>
            <th>Jumlah Rusak</th>
            <td>{{ $perbaikan->jumlah_rusak }} {{ $perbaikan->barang->satuan }}</td>
        </tr>
        <tr>
            <th>Tingkat Kerusakan</th>
            <td>
                <span class="badge {{ $perbaikan->kerusakan_badge_class }}">
                    {{ $perbaikan->tingkat_kerusakan }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <span class="badge 
                    {{ $perbaikan->status == 'Selesai' ? 'bg-success' : 
                       ($perbaikan->status == 'Dalam Perbaikan' ? 'bg-warning' : 'bg-secondary') }}">
                    {{ $perbaikan->status }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Tanggal Masuk</th>
            <td>{{ $perbaikan->tanggal_masuk->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Tanggal Selesai</th>
            <td>
                {{ $perbaikan->tanggal_selesai 
                    ? $perbaikan->tanggal_selesai->format('d/m/Y') 
                    : '-' }}
            </td>
        </tr>
        <tr>
            <th>Keterangan Kerusakan</th>
            <td>{{ $perbaikan->keterangan_kerusakan ?? '-' }}</td>
        </tr>
        <tr>
            <th>Catatan Perbaikan</th>
            <td>{{ $perbaikan->catatan_perbaikan ?? '-' }}</td>
        </tr>
        <tr>
            <th>Biaya Perbaikan</th>
            <td>
                {{ $perbaikan->biaya_perbaikan 
                    ? 'Rp ' . number_format($perbaikan->biaya_perbaikan, 0, ',', '.') 
                    : '-' }}
            </td>
        </tr>
        <tr>
            <th>Dibuat Pada</th>
            <td>{{ $perbaikan->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <th>Diperbarui Pada</th>
            <td>{{ $perbaikan->updated_at->format('d/m/Y H:i') }}</td>
        </tr>
    </table>
</div>
