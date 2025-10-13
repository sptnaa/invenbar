<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\User;
use App\Models\Peminjaman;
use App\Models\Perbaikan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Jika admin => semua data, jika petugas => filter berdasarkan lokasi_id
        $isAdmin = $user->hasRole('admin');
        $lokasiId = $user->lokasi_id ?? null;

        // Hitung jumlah data utama
        $jumlahBarang    = $isAdmin ? Barang::count() : Barang::where('lokasi_id', $lokasiId)->count();
        $jumlahKategori  = Kategori::count(); // kategori bisa dilihat semua
        $jumlahLokasi    = Lokasi::count();
        $jumlahUser      = User::count();

        $totalPerbaikan  = $isAdmin
            ? Perbaikan::count()
            : Perbaikan::whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            })->count();

        // Data peminjaman
        $totalPeminjaman = $isAdmin
            ? Peminjaman::count()
            : Peminjaman::whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            })->count();

        $sedangDipinjam = $isAdmin
            ? Peminjaman::where('status', 'Sedang Dipinjam')->count()
            : Peminjaman::where('status', 'Sedang Dipinjam')->whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            })->count();

        $terlambat = $isAdmin
            ? Peminjaman::where('status', 'Terlambat')->count()
            : Peminjaman::where('status', 'Terlambat')->whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            })->count();

        $sudahDikembalikan = $isAdmin
            ? Peminjaman::where('status', 'Sudah Dikembalikan')->count()
            : Peminjaman::where('status', 'Sudah Dikembalikan')->whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            })->count();

        // Barang dengan stok rendah
        $barangStokRendah = ($isAdmin
            ? Barang::query()
            : Barang::where('lokasi_id', $lokasiId))
            ->where('jumlah', '<=', 5)
            ->orderBy('jumlah', 'asc')
            ->take(5)
            ->get();

        // Barang terbaru
        $barangTerbaru = ($isAdmin
            ? Barang::with(['kategori', 'lokasi'])
            : Barang::with(['kategori', 'lokasi'])->where('lokasi_id', $lokasiId))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Peminjaman terbaru
        $peminjamanTerbaru = ($isAdmin
            ? Peminjaman::query()
            : Peminjaman::whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            }))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Peminjaman akan jatuh tempo
        $akanJatuhTempo = ($isAdmin
            ? Peminjaman::query()
            : Peminjaman::whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            }))
            ->whereDate('tanggal_kembali_rencana', '>=', Carbon::now())
            ->whereDate('tanggal_kembali_rencana', '<=', Carbon::now()->addDays(3))
            ->whereNull('tanggal_kembali_aktual')
            ->orderBy('tanggal_kembali_rencana', 'asc')
            ->get();

        // Peminjaman terlambat
        $peminjamanTerlambat = ($isAdmin
            ? Peminjaman::where('status', 'Terlambat')
            : Peminjaman::where('status', 'Terlambat')->whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            }))
            ->orderBy('tanggal_kembali_rencana', 'asc')
            ->take(5)
            ->get();

        // Statistik bulan ini
        $bulanIni = Carbon::now();

        $peminjamanBulanIni = ($isAdmin
            ? Peminjaman::query()
            : Peminjaman::whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            }))
            ->whereMonth('created_at', $bulanIni->month)
            ->whereYear('created_at', $bulanIni->year)
            ->count();

        $pengembalianBulanIni = ($isAdmin
            ? Peminjaman::query()
            : Peminjaman::whereHas('barang', function ($q) use ($lokasiId) {
                $q->where('lokasi_id', $lokasiId);
            }))
            ->whereMonth('tanggal_kembali_aktual', $bulanIni->month)
            ->whereYear('tanggal_kembali_aktual', $bulanIni->year)
            ->whereNotNull('tanggal_kembali_aktual')
            ->count();

        // Statistik kondisi barang
        $kondisiBaik = $isAdmin
            ? Barang::sum('jumlah_baik')
            : Barang::where('lokasi_id', $lokasiId)->sum('jumlah_baik');

        $kondisiRusakRingan = $isAdmin
            ? Barang::sum('jumlah_rusak_ringan')
            : Barang::where('lokasi_id', $lokasiId)->sum('jumlah_rusak_ringan');

        $kondisiRusakBerat = $isAdmin
            ? Barang::sum('jumlah_rusak_berat')
            : Barang::where('lokasi_id', $lokasiId)->sum('jumlah_rusak_berat');

        // Data chart 6 bulan terakhir
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);

            $peminjamanQuery = $isAdmin
                ? Peminjaman::query()
                : Peminjaman::whereHas('barang', function ($q) use ($lokasiId) {
                    $q->where('lokasi_id', $lokasiId);
                });

            $chartData[] = [
                'bulan' => $bulan->format('M Y'),
                'peminjaman' => $peminjamanQuery->whereMonth('created_at', $bulan->month)
                    ->whereYear('created_at', $bulan->year)
                    ->count(),
                'pengembalian' => $peminjamanQuery->whereMonth('tanggal_kembali_aktual', $bulan->month)
                    ->whereYear('tanggal_kembali_aktual', $bulan->year)
                    ->whereNotNull('tanggal_kembali_aktual')
                    ->count(),
            ];
        }

        // Kirim ke view
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
            'kondisiRusakBerat',
            'totalPerbaikan'
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
