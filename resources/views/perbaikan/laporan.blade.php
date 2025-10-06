<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .badge-danger {
            background-color: #dc3545;
            color: #fff;
        }
        .badge-info {
            background-color: #17a2b8;
            color: #fff;
        }
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Tanggal Cetak: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">No. Perbaikan</th>
                <th width="20%">Barang</th>
                <th width="8%">Jumlah</th>
                <th width="12%">Tingkat</th>
                <th width="10%">Tgl Masuk</th>
                <th width="10%">Tgl Selesai</th>
                <th width="10%">Status</th>
                <th width="13%">Biaya</th>
            </tr>
        </thead>
        <tbody>
            @forelse($perbaikans as $index => $perbaikan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $perbaikan->nomor_perbaikan }}</td>
                <td>
                    <strong>{{ $perbaikan->barang->nama_barang }}</strong><br>
                    <small>{{ $perbaikan->barang->kode_barang }}</small>
                </td>
                <td>{{ $perbaikan->jumlah_rusak }} {{ $perbaikan->barang->satuan }}</td>
                <td>{{ $perbaikan->tingkat_kerusakan }}</td>
                <td>{{ $perbaikan->tanggal_masuk->format('d/m/Y') }}</td>
                <td>{{ $perbaikan->tanggal_selesai ? $perbaikan->tanggal_selesai->format('d/m/Y') : '-' }}</td>
                <td>{{ $perbaikan->status }}</td>
                <td style="text-align: right;">{{ $perbaikan->biaya_perbaikan ? 'Rp ' . number_format($perbaikan->biaya_perbaikan, 0, ',', '.') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center;">Tidak ada data perbaikan</td>
            </tr>
            @endforelse
        </tbody>
        @if($perbaikans->count() > 0)
        <tfoot>
            <tr>
                <th colspan="8" style="text-align: right;">Total Biaya Perbaikan:</th>
                <th style="text-align: right;">Rp {{ number_format($perbaikans->sum('biaya_perbaikan'), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>