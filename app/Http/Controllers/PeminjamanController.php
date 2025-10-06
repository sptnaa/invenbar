<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Barang;
use App\Models\Perbaikan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $search = $request->search;
        $status = $request->status;

        $peminjamans = Peminjaman::with(['barang', 'barang.kategori', 'barang.lokasi'])
            ->when($search, function ($query, $search) {
                $query->where('nama_peminjam', 'like', '%' . $search . '%')
                    ->orWhere('nomor_transaksi', 'like', '%' . $search . '%')
                    ->orWhereHas('barang', function ($q) use ($search) {
                        $q->where('nama_barang', 'like', '%' . $search . '%')
                          ->orWhere('kode_barang', 'like', '%' . $search . '%');
                    });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate()
            ->withQueryString();

        $this->updateOverdueLoans();
        $statusOptions = ['Sedang Dipinjam', 'Sudah Dikembalikan', 'Terlambat'];

        return view('peminjaman.index', compact('peminjamans', 'statusOptions'));
    }

    public function create()
    {
        $peminjaman = new Peminjaman();
        $barangs = Barang::with(['kategori', 'lokasi'])
            ->where('is_pinjaman', true)
            ->get();

        return view('peminjaman.create', compact('peminjaman', 'barangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_peminjam' => 'required|string|max:100',
            'email_peminjam' => 'nullable|email|max:100',
            'telepon_peminjam' => 'nullable|string|max:20',
            'barang_id' => 'required|exists:barangs,id',
            'jumlah_pinjam' => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date|after_or_equal:today',
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
            'tanggal_pinjam' => 'required|date|after_or_equal:today',
            'tanggal_kembali_rencana' => 'required|date|after:tanggal_pinjam',
            'keperluan' => 'nullable|string|max:500',
        ]);

        if ($peminjaman->barang_id != $validated['barang_id'] ||
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

        $peminjaman->tanggal_kembali_aktual = now();
        $peminjaman->status = 'Sudah Dikembalikan';
        $peminjaman->kondisi_barang = $request->kondisi_barang;
        $peminjaman->save();

        $barang = $peminjaman->barang;
        
        // Jika kondisi rusak, buat otomatis data perbaikan dan update stok
        if ($request->kondisi_barang !== 'Baik') {
            // Kurangi jumlah_baik
            $barang->jumlah_baik = max(0, $barang->jumlah_baik - $peminjaman->jumlah_pinjam);
            
            // Tambahkan ke rusak sesuai tingkat
            if ($request->kondisi_barang === 'Rusak Ringan') {
                $barang->jumlah_rusak_ringan += $peminjaman->jumlah_pinjam;
            } else {
                $barang->jumlah_rusak_berat += $peminjaman->jumlah_pinjam;
            }
            
            // Update kondisi dominan
            $barang->kondisi = $barang->kondisi_dominan;
            $barang->save();

            // Buat data perbaikan otomatis
            Perbaikan::create([
                'nomor_perbaikan' => Perbaikan::generateNomorPerbaikan(),
                'barang_id' => $barang->id,
                'peminjaman_id' => $peminjaman->id,
                'jumlah_rusak' => $peminjaman->jumlah_pinjam,
                'tingkat_kerusakan' => $request->kondisi_barang,
                'keterangan_kerusakan' => 'Barang rusak setelah peminjaman oleh ' . $peminjaman->nama_peminjam,
                'tanggal_masuk' => now(),
                'status' => 'Menunggu'
            ]);

            return redirect()->route('peminjaman.index')
                ->with('warning', 'Barang berhasil dikembalikan. PERHATIAN: Barang dikembalikan dalam kondisi ' . $request->kondisi_barang . ' dan telah otomatis masuk ke menu Perbaikan.');
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
        Peminjaman::terlambat()->update(['status' => 'Terlambat']);
    }

    public function laporan()
    {
        $peminjamans = Peminjaman::with(['barang', 'barang.kategori', 'barang.lokasi'])
            ->latest()
            ->get();

        $data = [
            'title' => 'Laporan Data Peminjaman Barang',
            'date' => date('d F Y'),
            'peminjamans' => $peminjamans
        ];

        $pdf = Pdf::loadView('peminjaman.laporan', $data);
        return $pdf->stream('laporan-peminjaman-barang.pdf');
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