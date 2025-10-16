<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Barang;
use App\Models\Perbaikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PeminjamanController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('permission:view peminjaman', only: ['index', 'show']),
            new Middleware('permission:manage peminjaman', except: ['index', 'show', 'destroy']),
            new Middleware('permission:delete peminjaman', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = Peminjaman::with(['barang.lokasi', 'barang.kategori']);
        $user = Auth::user();

        //  Jika role petugas, hanya tampilkan data dari lokasi petugas
        if ($user->hasRole('petugas') && $user->lokasi_id) {
            $query->whereHas('barang', function ($q) use ($user) {
                $q->where('lokasi_id', $user->lokasi_id);
            });
        }

        // Pencarian
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_peminjam', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_transaksi', 'like', '%' . $request->search . '%');
            });
        }

        // Ambil data paginasi
        $peminjamans = $query->orderByDesc('created_at')->paginate(10);

        // Hitung statistik sesuai role
        if ($user->hasRole('petugas') && $user->lokasi_id) {
            // Statistik hanya untuk lokasi petugas
            $baseQuery = Peminjaman::whereHas('barang', function ($q) use ($user) {
                $q->where('lokasi_id', $user->lokasi_id);
            });
        } else {
            // Admin: semua data
            $baseQuery = Peminjaman::query();
        }

        $total = (clone $baseQuery)->count();
        $sedangDipinjam = (clone $baseQuery)->where('status', 'Sedang Dipinjam')->count();
        $terlambat = (clone $baseQuery)->where('status', 'Terlambat')->count();
        $sudahDikembalikan = (clone $baseQuery)->where('status', 'Sudah Dikembalikan')->count();


        // Status dropdown di tampilan
        $statusOptions = ['Dipinjam', 'Dikembalikan', 'Terlambat'];

        return view('peminjaman.index', compact(
            'peminjamans',
            'statusOptions',
            'total',
            'sedangDipinjam',
            'sudahDikembalikan',
            'terlambat'
        ));
    }


    public function create()
{
    $peminjaman = new Peminjaman();
    $user = Auth::user();

    // Base query barang yang bisa dipinjam
    $barangQuery = Barang::with(['kategori', 'lokasi', 'childUnits'])
        ->where('is_pinjaman', true);

    // Jika user adalah petugas, tampilkan hanya barang di lokasi-nya
    if ($user->hasRole('petugas') && $user->lokasi_id) {
        $barangQuery->where('lokasi_id', $user->lokasi_id);
    }

    // Ambil semua barang
    $barangs = $barangQuery->get();

    // Pisahkan barang berdasarkan mode input
    $barangsMasal = $barangs->where('mode_input', 'masal');
    $barangsParentUnit = $barangs->where('mode_input', 'unit')->whereNull('kode_dasar');

    return view('peminjaman.create', compact('peminjaman', 'barangs', 'barangsMasal', 'barangsParentUnit'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_peminjam' => 'required|string|max:100',
            'email_peminjam' => 'nullable|email|max:100',
            'telepon_peminjam' => 'nullable|string|max:20',
            'barang_id' => 'required|exists:barangs,id',
            'jumlah_pinjam' => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after:tanggal_pinjam',
            'keperluan' => 'nullable|string|max:500',
        ]);

        $barang = Barang::findOrFail($validated['barang_id']);
        if (!$barang->canBeBorrowed($validated['jumlah_pinjam'])) {
            return back()->withErrors([
                'jumlah_pinjam' => 'Stok barang tidak mencukupi. Stok tersedia: ' . $barang->stok_tersedia
            ])->withInput();
        }

        $validated['nomor_transaksi'] = Peminjaman::generateNomorTransaksi();
        $peminjaman = Peminjaman::create($validated);

        return redirect()->route('peminjaman.index')
            ->with('success', 'Peminjaman berhasil ditambahkan dengan nomor transaksi: ' . $peminjaman->nomor_transaksi);
    }

    public function show(Peminjaman $peminjaman)
    {
        $peminjaman->load(['barang', 'barang.kategori', 'barang.lokasi']);
        $peminjaman->updateStatus();

        return view('peminjaman.show', compact('peminjaman'));
    }

    public function edit(Peminjaman $peminjaman)
    {
        $barangs = Barang::with(['kategori', 'lokasi'])
            ->where('is_pinjaman', true)
            ->get();

        return view('peminjaman.edit', compact('peminjaman', 'barangs'));
    }

    public function update(Request $request, Peminjaman $peminjaman)
    {
        $validated = $request->validate([
            'nama_peminjam' => 'required|string|max:100',
            'email_peminjam' => 'nullable|email|max:100',
            'telepon_peminjam' => 'nullable|string|max:20',
            'barang_id' => 'required|exists:barangs,id',
            'jumlah_pinjam' => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after:tanggal_pinjam',
            'keperluan' => 'nullable|string|max:500',
        ]);

        if (
            $peminjaman->barang_id != $validated['barang_id'] ||
            $peminjaman->jumlah_pinjam != $validated['jumlah_pinjam']
        ) {
            $barang = Barang::findOrFail($validated['barang_id']);
            $stokTersedia = $barang->stok_tersedia + $peminjaman->jumlah_pinjam;

            if ($stokTersedia < $validated['jumlah_pinjam']) {
                return back()->withErrors([
                    'jumlah_pinjam' => 'Stok barang tidak mencukupi. Stok tersedia: ' . $stokTersedia
                ])->withInput();
            }
        }

        $peminjaman->update($validated);
        $peminjaman->updateStatus();

        return redirect()->route('peminjaman.index')
            ->with('success', 'Data peminjaman berhasil diperbarui.');
    }

    public function destroy(Peminjaman $peminjaman)
    {
        $peminjaman->delete();

        return redirect()->route('peminjaman.index')
            ->with('success', 'Data peminjaman berhasil dihapus.');
    }

    public function pengembalian(Request $request, Peminjaman $peminjaman)
    {
        if ($peminjaman->status === 'Sudah Dikembalikan') {
            return back()->with('error', 'Barang sudah dikembalikan sebelumnya.');
        }

        $request->validate([
            'kondisi_barang' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
        ]);

        // Update status peminjaman
        $peminjaman->update([
            'tanggal_kembali_aktual' => now(),
            'status' => 'Sudah Dikembalikan',
            'kondisi_barang' => $request->kondisi_barang,
        ]);

        $barang = $peminjaman->barang;

        // Update stok sesuai kondisi
        if ($request->kondisi_barang !== 'Baik') {
            // Kurangi stok baik
            $barang->jumlah_baik = max(0, $barang->jumlah_baik - $peminjaman->jumlah_pinjam);

            // Tambah stok rusak sesuai kondisi
            if ($request->kondisi_barang === 'Rusak Ringan') {
                $barang->jumlah_rusak_ringan += $peminjaman->jumlah_pinjam;
            } else {
                $barang->jumlah_rusak_berat += $peminjaman->jumlah_pinjam;
            }

            $barang->kondisi = $barang->kondisi_dominan;
            $barang->save();

            // Tandai bahwa barang butuh perbaikan (tanpa buat entri di tabel perbaikan)
            $peminjaman->update([
                'butuh_perbaikan' => true,
            ]);

            return redirect()->route('peminjaman.index')
                ->with('warning', 'Barang berhasil dikembalikan dengan kondisi ' . $request->kondisi_barang . '. Barang ini memerlukan perbaikan, silakan tambahkan secara manual di menu Perbaikan.');
        }

        return redirect()->route('peminjaman.index')
            ->with('success', 'Barang berhasil dikembalikan dalam kondisi baik.');
    }



    public function getBarangData(Request $request)
    {
        $barang = Barang::with(['kategori', 'lokasi'])
            ->find($request->barang_id);

        if (!$barang) {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }

        return response()->json([
            'kode_barang' => $barang->kode_barang,
            'nama_barang' => $barang->nama_barang,
            'kategori' => $barang->kategori->nama_kategori,
            'lokasi' => $barang->lokasi->nama_lokasi,
            'stok_tersedia' => $barang->stok_tersedia,
            'satuan' => $barang->satuan
        ]);
    }

    private function updateOverdueLoans()
    {
        // Ambil semua peminjaman yang masih sedang dipinjam
        $peminjamans = Peminjaman::where('status', 'Sedang Dipinjam')->get();

        foreach ($peminjamans as $peminjaman) {
            // Jika waktu sekarang lebih besar dari tanggal kembali rencana
            if (Carbon::now()->gt(Carbon::parse($peminjaman->tanggal_kembali_rencana))) {
                $peminjaman->update(['status' => 'Terlambat']);
            }
        }
    }

    public function Laporan()
    {
        $user = Auth::user();

        $query = Peminjaman::with(['barang.lokasi']);

        // Filter lokasi untuk petugas
        if ($user->isPetugas() && $user->lokasi_id) {
            $query->whereHas('barang', fn($q) => $q->where('lokasi_id', $user->lokasi_id));
        }

        $peminjamans = $query->get();

        $data = [
            'title' => 'Laporan Data Peminjaman Barang',
            'date' => date('d F Y'),
            'peminjamans' => $peminjamans,
        ];

        $pdf = Pdf::loadView('peminjaman.laporan', $data);
        return $pdf->stream('laporan-peminjaman.pdf');
    }

    public function dashboardData()
    {
        $data = [
            'total_peminjaman' => Peminjaman::count(),
            'sedang_dipinjam' => Peminjaman::where('status', 'Sedang Dipinjam')->count(),
            'terlambat' => Peminjaman::where('status', 'Terlambat')->count(),
            'sudah_dikembalikan' => Peminjaman::where('status', 'Sudah Dikembalikan')->count(),
        ];

        return response()->json($data);
    }
}
