<!DOCTYPE html>
<html>
<head>
    <title>Pengingat Pengembalian Barang</title>
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
            background-color: #ffc107;
            color: #212529;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 15px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 10px;
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
        <h2>⏰ Pengingat Pengembalian Barang</h2>
        <p>{{ config('app.name') }}</p>
    </div>

    <div class="content">
        <p>Halo <strong>{{ $peminjaman->nama_peminjam }}</strong>,</p>
        
        <p>Ini adalah pengingat bahwa barang yang Anda pinjam akan jatuh tempo <strong>besok</strong>.</p>

        <div class="info-box">
            <h4>Detail Peminjaman:</h4>
            <table class="table">
                <tr>
                    <th style="width: 30%;">No. Transaksi</th>
                    <td>{{ $peminjaman->nomor_transaksi }}</td>
                </tr>
                <tr>
                    <th>Nama Barang</th>
                    <td>{{ $peminjaman->barang->nama_barang }}</td>
                </tr>
                <tr>
                    <th>Jumlah</th>
                    <td>{{ $peminjaman->jumlah_pinjam }} {{ $peminjaman->barang->satuan }}</td>
                </tr>
                <tr>
                    <th>Tanggal Pinjam</th>
                    <td>{{ $peminjaman->tanggal_pinjam->format('d F Y') }}</td>
                </tr>
                <tr>
                    <th>Tanggal Kembali</th>
                    <td><strong>{{ $peminjaman->tanggal_kembali_rencana->format('d F Y') }}</strong></td>
                </tr>
            </table>
        </div>

        <p>Mohon untuk mengembalikan barang tepat waktu agar tidak dikenakan denda keterlambatan.</p>
        
        <p><strong>Informasi Penting:</strong></p>
        <ul>
            <li>Pastikan barang dikembalikan dalam kondisi baik</li>
            <li>Keterlambatan akan dikenakan denda Rp 5.000 per hari</li>
            <li>Hubungi admin jika ada kendala dalam pengembalian</li>
        </ul>

        <p>Terima kasih atas perhatian Anda.</p>
    </div>

    <div class="footer">
        <p>Email ini dikirim otomatis oleh sistem pada {{ date('d F Y, H:i') }}</p>
        <p>{{ config('app.name') }} - Sistem Inventaris Barang</p>
    </div>
</body>
</html>