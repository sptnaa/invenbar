<!DOCTYPE html>
<html>
<head>
    <title>Notifikasi Peminjaman Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
            padding: 15px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Notifikasi Peminjaman Barang</h2>
        <p>{{ config('app.name') }}</p>
    </div>

    <div class="content">
        <p>Halo {{ $admin->name }},</p>
        
        <p>Berikut adalah ringkasan status peminjaman barang per tanggal {{ date('d F Y') }}:</p>

        @if($upcomingDue->count() > 0)
        <div class="alert alert-warning">
            <h4>⚠️ Akan Jatuh Tempo Besok ({{ $upcomingDue->count() }} item)</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Peminjam</th>
                        <th>Barang</th>
                        <th>Tanggal Kembali</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingDue as $peminjaman)
                    <tr>
                        <td>{{ $peminjaman->nomor_transaksi }}</td>
                        <td>{{ $peminjaman->nama_peminjam }}</td>
                        <td>{{ $peminjaman->barang->nama_barang }}</td>
                        <td>{{ $peminjaman->tanggal_kembali_rencana->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($overdue->count() > 0)
        <div class="alert alert-danger">
            <h4>🚨 Sudah Terlambat ({{ $overdue->count() }} item)</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Peminjam</th>
                        <th>Barang</th>
                        <th>Terlambat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdue as $peminjaman)
                    <tr>
                        <td>{{ $peminjaman->nomor_transaksi }}</td>
                        <td>{{ $peminjaman->nama_peminjam }}</td>
                        <td>{{ $peminjaman->barang->nama_barang }}</td>
                        <td>{{ $peminjaman->hari_terlambat }} hari</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($upcomingDue->count() == 0 && $overdue->count() == 0)
        <div class="alert" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
            <h4>✅ Semua Peminjaman Dalam Status Normal</h4>
            <p>Tidak ada peminjaman yang akan jatuh tempo atau terlambat hari ini.</p>
        </div>
        @endif

        <p>Silakan login ke sistem untuk menindaklanjuti peminjaman yang memerlukan perhatian.</p>
        
        <p>
            <a href="{{ url('/') }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">
                Login ke Sistem
            </a>
        </p>
    </div>

    <div class="footer">
        <p>Email ini dikirim otomatis oleh sistem pada {{ date('d F Y, H:i') }}</p>
        <p>{{ config('app.name') }} - Sistem Inventaris Barang</p>
    </div>
</body>
</html>