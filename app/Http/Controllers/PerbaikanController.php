<?php

namespace App\Http\Controllers;

use App\Models\Perbaikan;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;

class PerbaikanController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('permission:view perbaikan', only: ['index', 'show']),
            new Middleware('permission:manage perbaikan', except: ['index', 'show', 'destroy']),
            new Middleware('permission:delete perbaikan', only: ['destroy']),
        ];
    }

    public function index(Request $request)
{
    $user = Auth::user();

    $statusOptions = ['Menunggu', 'Sedang Diperbaiki', 'Selesai'];
    $tingkatOptions = ['Ringan', 'Sedang', 'Berat'];

    $query = Perbaikan::with(['barang.kategori', 'barang.lokasi', 'peminjaman'])
        ->orderBy('created_at', 'desc');

    if ($user->hasRole('Teknisi')) {
        $query->whereHas('barang.lokasi', function ($q) use ($user) {
            $q->where('id', $user->lokasi_id);
        });
    }

    $perbaikans = $query->get();

    // === GROUPING PER LOKASI ===
    $groupedPerbaikans = $perbaikans->groupBy(function ($item) {
        return optional($item->barang->lokasi)->nama_lokasi ?? 'Lokasi Tidak Diketahui';
    });

    return view('perbaikan.index', compact('groupedPerbaikans', 'statusOptions', 'tingkatOptions'));
}


    public function create()
    {
        $perbaikan = new Perbaikan();
        $barangs = Barang::with(['kategori', 'lokasi'])
            ->where(function ($q) {
                $q->where('jumlah_rusak_ringan', '>', 0)
                    ->orWhere('jumlah_rusak_berat', '>', 0);
            })
            ->get();

        return view('perbaikan.create', compact('perbaikan', 'barangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'jumlah_rusak' => 'required|integer|min:1',
            'tingkat_kerusakan' => 'required|in:Rusak Ringan,Rusak Berat',
            'keterangan_kerusakan' => 'nullable|string|max:1000',
            'tanggal_masuk' => 'required|date',
        ]);

        $barang = Barang::findOrFail($validated['barang_id']);

        // Pastikan jumlah rusak tidak melebihi stok
        if ($validated['tingkat_kerusakan'] === 'Rusak Ringan' && $barang->jumlah_rusak_ringan < $validated['jumlah_rusak']) {
            return back()->withErrors(['jumlah_rusak' => 'Jumlah melebihi stok rusak ringan: ' . $barang->jumlah_rusak_ringan])->withInput();
        } elseif ($validated['tingkat_kerusakan'] === 'Rusak Berat' && $barang->jumlah_rusak_berat < $validated['jumlah_rusak']) {
            return back()->withErrors(['jumlah_rusak' => 'Jumlah melebihi stok rusak berat: ' . $barang->jumlah_rusak_berat])->withInput();
        }

        $validated['nomor_perbaikan'] = Perbaikan::generateNomorPerbaikan();
        $validated['status'] = 'Menunggu';

        $perbaikan = Perbaikan::create($validated);

        // 🔧 Pindahkan stok rusak ke stok sedang diperbaiki
        if ($validated['tingkat_kerusakan'] === 'Rusak Ringan') {
            $barang->jumlah_rusak_ringan -= $validated['jumlah_rusak'];
        } else {
            $barang->jumlah_rusak_berat -= $validated['jumlah_rusak'];
        }

        $barang->jumlah_diperbaiki += $validated['jumlah_rusak'];
        $barang->save();

        return redirect()->route('perbaikan.index')
            ->with('success', 'Data perbaikan berhasil ditambahkan dan stok barang diperbarui.');
    }

    public function show(Perbaikan $perbaikan)
    {
        $barangs = Barang::all();
        return view('perbaikan.show', compact('perbaikan', 'barangs'));
    }

    public function edit(Perbaikan $perbaikan)
    {
        $barangs = Barang::with(['kategori', 'lokasi'])->get();
        return view('perbaikan.edit', compact('perbaikan', 'barangs'));
    }

    public function update(Request $request, Perbaikan $perbaikan)
    {
        $validated = $request->validate([
            'status' => 'required|in:Menunggu,Dalam Perbaikan,Selesai',
            'catatan_perbaikan' => 'nullable|string|max:1000',
            'biaya_perbaikan' => 'nullable|numeric|min:0',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_masuk',
        ]);

        $barang = $perbaikan->barang;

        // Jika status diubah ke Selesai
        if ($validated['status'] === 'Selesai' && $perbaikan->status !== 'Selesai') {
            $validated['tanggal_selesai'] = $validated['tanggal_selesai'] ?? now();

            // Pindahkan dari stok sedang diperbaiki ke stok baik
            $barang->jumlah_diperbaiki -= $perbaikan->jumlah_rusak;
            $barang->jumlah_baik += $perbaikan->jumlah_rusak;

            $barang->save();
        }

        $perbaikan->update($validated);

        return redirect()->route('perbaikan.index')
            ->with('success', 'Data perbaikan berhasil diperbarui.');
    }

    public function destroy(Perbaikan $perbaikan)
    {
        $barang = $perbaikan->barang;

        // Kembalikan stok jika perbaikan belum selesai
        if ($perbaikan->status !== 'Selesai') {
            $barang->jumlah_diperbaiki -= $perbaikan->jumlah_rusak;

            if ($perbaikan->tingkat_kerusakan === 'Rusak Ringan') {
                $barang->jumlah_rusak_ringan += $perbaikan->jumlah_rusak;
            } else {
                $barang->jumlah_rusak_berat += $perbaikan->jumlah_rusak;
            }

            $barang->save();
        }

        $perbaikan->delete();

        return redirect()->route('perbaikan.index')
            ->with('success', 'Data perbaikan berhasil dihapus dan stok dikembalikan.');
    }

    public function Laporan()
    {
        $user = Auth::user();

        $query = Perbaikan::with(['barang.lokasi']);

        // Filter lokasi untuk petugas
        if ($user->isPetugas() && $user->lokasi_id) {
            $query->whereHas('barang', fn($q) => $q->where('lokasi_id', $user->lokasi_id));
        }

        $perbaikans = $query->get();

        $data = [
            'title' => 'Laporan Data Perbaikan Barang',
            'date' => date('d F Y'),
            'perbaikans' => $perbaikans,
        ];

        $pdf = Pdf::loadView('perbaikan.laporan', $data);
        return $pdf->stream('laporan-perbaikan.pdf');
    }

    public function prosesPerbaikan(Request $request, Perbaikan $perbaikan)
    {
        if ($perbaikan->status === 'Selesai') {
            return back()->with('error', 'Perbaikan sudah selesai.');
        }

        $validated = $request->validate([
            'status' => 'required|in:Dalam Perbaikan,Selesai',
            'catatan_perbaikan' => 'nullable|string|max:1000',
            'biaya_perbaikan' => 'required|numeric|min:1',
        ]);

        $barang = $perbaikan->barang;

        if ($validated['status'] === 'Selesai') {
            $validated['tanggal_selesai'] = now();

            // Pindahkan dari sedang diperbaiki ke stok baik
            $barang->jumlah_diperbaiki -= $perbaikan->jumlah_rusak;
            $barang->jumlah_baik += $perbaikan->jumlah_rusak;
            $barang->save();
        }

        $perbaikan->update($validated);

        return redirect()->route('perbaikan.index')
            ->with('success', 'Status perbaikan berhasil diperbarui.');
    }
}
