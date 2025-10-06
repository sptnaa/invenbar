<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\User;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung jumlah data utama
        $jumlahBarang    = Barang::count();
        $jumlahKategori  = Kategori::count();
        $jumlahLokasi    = Lokasi::count();
        $jumlahUser      = User::count();

        // Data peminjaman
        $totalPeminjaman   = Peminjaman::count();
        $sedangDipinjam    = Peminjaman::where('status', 'Sedang Dipinjam')->count();
        $terlambat         = Peminjaman::where('status', 'Terlambat')->count();
        $sudahDikembalikan = Peminjaman::where('status', 'Sudah Dikembalikan')->count();

        // Barang dengan stok rendah
        $barangStokRendah = Barang::where('jumlah', '<=', 5)
            ->orderBy('jumlah', 'asc')
            ->take(5)
            ->get();

        // Barang terbaru
        $barangTerbaru = Barang::with(['kategori', 'lokasi'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Peminjaman terbaru
        $peminjamanTerbaru = Peminjaman::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Peminjaman akan jatuh tempo (3 hari ke depan)
        $akanJatuhTempo = Peminjaman::whereDate('tanggal_kembali_rencana', '>=', Carbon::now())
            ->whereDate('tanggal_kembali_rencana', '<=', Carbon::now()->addDays(3))
            ->whereNull('tanggal_kembali_aktual')
            ->orderBy('tanggal_kembali_rencana', 'asc')
            ->get();

        // Peminjaman terlambat
        $peminjamanTerlambat = Peminjaman::where('status', 'Terlambat')
            ->orderBy('tanggal_kembali_rencana', 'asc')
            ->take(5)
            ->get();

        // Statistik peminjaman bulan ini
        $bulanIni = Carbon::now();
        $peminjamanBulanIni = Peminjaman::whereMonth('created_at', $bulanIni->month)
            ->whereYear('created_at', $bulanIni->year)
            ->count();

        $pengembalianBulanIni = Peminjaman::whereMonth('tanggal_kembali_aktual', $bulanIni->month)
            ->whereYear('tanggal_kembali_aktual', $bulanIni->year)
            ->whereNotNull('tanggal_kembali_aktual')
            ->count();

        // Statistik kondisi barang
        $kondisiBaik        = Barang::sum('jumlah_baik');
        $kondisiRusakRingan = Barang::sum('jumlah_rusak_ringan');
        $kondisiRusakBerat  = Barang::sum('jumlah_rusak_berat');

        // Data chart peminjaman & pengembalian (6 bulan terakhir)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);

            $chartData[] = [
                'bulan'        => $bulan->format('M Y'),
                'peminjaman'   => Peminjaman::whereMonth('created_at', $bulan->month)
                    ->whereYear('created_at', $bulan->year)
                    ->count(),
                'pengembalian' => Peminjaman::whereMonth('tanggal_kembali_aktual', $bulan->month)
                    ->whereYear('tanggal_kembali_aktual', $bulan->year)
                    ->whereNotNull('tanggal_kembali_aktual')
                    ->count(),
            ];
        }

        // Kirim data ke view
        return view('dashboard', compact(
            'jumlahBarang',
            'jumlahKategori',
            'jumlahLokasi',
            'jumlahUser',
            'totalPeminjaman',
            'sedangDipinjam',
            'terlambat',
            'sudahDikembalikan',
            'barangStokRendah',
            'barangTerbaru',
            'peminjamanTerbaru',
            'akanJatuhTempo',
            'peminjamanTerlambat',
            'peminjamanBulanIni',
            'pengembalianBulanIni',
            'chartData',
            'kondisiBaik',
            'kondisiRusakRingan',
            'kondisiRusakBerat'
        ));
    }


    /**
     * API endpoint untuk data real-time
     */
    public function realtimeData()
    {
        return response()->json([
            'peminjaman' => [
                'total'            => Peminjaman::count(),
                'sedang_dipinjam'  => Peminjaman::where('status', 'Sedang Dipinjam')->count(),
                'terlambat'        => Peminjaman::where('status', 'Terlambat')->count(),
                'sudah_dikembalikan' => Peminjaman::where('status', 'Sudah Dikembalikan')->count(),
            ],
            'barang' => [
                'total'       => Barang::count(),
                'stok_rendah' => Barang::whereRaw('jumlah <= 5')->count(),
                'tersedia'    => Barang::whereRaw('jumlah > 0')->count(),
            ],
            'alerts' => [
                'akan_jatuh_tempo' => Peminjaman::whereDate('tanggal_kembali_rencana', '>=', Carbon::now())
                    ->whereDate('tanggal_kembali_rencana', '<=', Carbon::now()->addDays(3))
                    ->whereNull('tanggal_kembali_aktual')
                    ->count(),
                'terlambat' => Peminjaman::where('status', 'Terlambat')->count(),
            ]
        ]);
    }
}
