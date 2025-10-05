<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0 0 10px 0;
            font-size: 20px;
            font-weight: bold;
        }

        .header p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        .summary {
            margin-bottom: 25px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .summary-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table, th, td {
            border: 1px solid #333;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px 5px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }

        td {
            padding: 6px 5px;
            text-align: left;
            vertical-align: top;
            font-size: 9px;
        }

        .text-center {
            text-align: center;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }

        .status-dipinjam { 
            background-color: #ffc107; 
            color: #000; 
        }
        
        .status-terlambat { 
            background-color: #dc3545; 
        }
        
        .status-dikembalikan { 
            background-color: #28a745; 
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: right;
            color: #666;
        }

        .alert-box {
            margin-top: 20px; 
            padding: 10px; 
            background-color: #fff3cd; 
            border: 1px solid #ffeaa7;
            border-radius: 5px;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            body { margin: 0; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body>
    
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Tanggal Cetak: {{ $date }}</p>
        <p>Total Data: {{ count($peminjamans) }} Peminjaman</p>
    </div>

    <!-- Summary Statistics -->
    <div class="summary">
        @php
            $totalPeminjaman = count($peminjamans);
            $sedangDipinjam = $peminjamans->where('status', 'Sedang Dipinjam')->count();
            $terlambat = $peminjamans->where('status', 'Terlambat')->count();
            $sudahDikembalikan = $peminjamans->where('status', 'Sudah Dikembalikan')->count();
        @endphp
        
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-value">{{ $totalPeminjaman }}</div>
                    <div class="summary-label">Total Peminjaman</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $sedangDipinjam }}</div>
                    <div class="summary-label">Sedang Dipinjam</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $terlambat }}</div>
                    <div class="summary-label">Terlambat</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $sudahDikembalikan }}</div>
                    <div class="summary-label">Sudah Dikembalikan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="12%">No. Transaksi</th>
                <th width="15%">Peminjam</th>
                <th width="18%">Barang</th>
                <th width="7%">Jumlah</th>
                <th width="9%">Tgl. Pinjam</th>
                <th width="9%">Tgl. Kembali</th>
                <th width="8%">Durasi</th>
                <th width="9%">Status</th>
                <th width="10%">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($peminjamans as $index => $peminjaman)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $peminjaman->nomor_transaksi }}</td>
                <td>
                    <strong>{{ $peminjaman->nama_peminjam }}</strong>
                    @if($peminjaman->email_peminjam)
                        <br>{{ $peminjaman->email_peminjam }}
                    @endif
                    @if($peminjaman->telepon_peminjam)
                        <br>{{ $peminjaman->telepon_peminjam }}
                    @endif
                </td>
                <td>
                    <strong>{{ $peminjaman->barang->nama_barang }}</strong>
                    <br>{{ $peminjaman->barang->kode_barang }}
                    <br><small>{{ $peminjaman->barang->kategori->nama_kategori }}</small>
                </td>
                <td class="text-center">
                    {{ $peminjaman->jumlah_pinjam }}<br>
                    <small>{{ $peminjaman->barang->satuan }}</small>
                </td>
                <td class="text-center">{{ $peminjaman->tanggal_pinjam->format('d/m/Y') }}</td>
                <td class="text-center">
                    {{ $peminjaman->tanggal_kembali_rencana->format('d/m/Y') }}
                    @if($peminjaman->tanggal_kembali_aktual)
                        <br><small>({{ $peminjaman->tanggal_kembali_aktual->format('d/m/Y') }})</small>
                    @endif
                </td>
                <td class="text-center">
                    {{ $peminjaman->durasi_peminjaman }}
                    @if($peminjaman->hari_terlambat > 0)
                        <br><small style="color: red;">+{{ $peminjaman->hari_terlambat }} hari</small>
                    @endif
                </td>
                <td class="text-center">
                    @php
                        $badgeClass = match($peminjaman->status) {
                            'Sedang Dipinjam' => 'status-dipinjam',
                            'Terlambat' => 'status-terlambat',
                            'Sudah Dikembalikan' => 'status-dikembalikan',
                            default => 'status-dipinjam'
                        };
                    @endphp
                    <span class="status-badge {{ $badgeClass }}">
                        {{ $peminjaman->status }}
                    </span>
                </td>
                <td class="text-center">
                    {{ $peminjaman->kondisi_barang ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data peminjaman.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Additional Information -->
    @if($terlambat > 0)
    <div class="alert-box">
        <strong>Perhatian:</strong> Terdapat {{ $terlambat }} peminjaman yang terlambat. 
        Harap segera lakukan tindak lanjut untuk pengembalian barang.
    </div>
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ date('d F Y, H:i:s') }}</p>
        <p>Sistem Inventaris Barang</p>
    </div>
    
</body>
</html>
