<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('permission:manage barang', except: ['destroy']),
            new Middleware('permission:delete barang', only: ['destroy']),
        ];
    }

    /**
     * Apply lokasi filter untuk petugas
     */
    private function applyLokasiFilter($query)
    {
        $user = Auth::user();
        
        if ($user->isPetugas() && $user->lokasi_id) {
            $query->where('lokasi_id', $user->lokasi_id);
        }
        
        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $barangs = Barang::with(['kategori', 'lokasi'])
            ->when($search, function ($query, $search) {
                $query->where('nama_barang', 'like', '%' . $search . '%')
                    ->orWhere('kode_barang', 'like', '%' . $search . '%');
            });

        // Filter berdasarkan lokasi untuk petugas
        $barangs = $this->applyLokasiFilter($barangs);

        $barangs = $barangs->latest()->paginate()->withQueryString();

        return view('barang.index', compact('barangs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori = Kategori::all();
        
        // Jika petugas, hanya tampilkan lokasi yang ditugaskan
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id) {
            $lokasi = Lokasi::where('id', $user->lokasi_id)->get();
        } else {
            $lokasi = Lokasi::all();
        }

        $barang = new Barang();

        return view('barang.create', compact('barang', 'kategori', 'lokasi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|max:50|unique:barangs,kode_barang',
            'nama_barang' => 'required|string|max:150',
            'kategori_id' => 'required|exists:kategoris,id',
            'lokasi_id' => 'required|exists:lokasis,id',
            'jumlah_baik' => 'required|integer|min:0',
            'jumlah_rusak_ringan' => 'required|integer|min:0',
            'jumlah_rusak_berat' => 'required|integer|min:0',
            'satuan' => 'required|string|max:20',
            'tanggal_pengadaan' => 'required|date',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_pinjaman' => 'nullable|boolean',
        ]);

        // Validasi lokasi untuk petugas
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $validated['lokasi_id'] != $user->lokasi_id) {
            return back()->with('error', 'Anda hanya dapat menambahkan barang di lokasi yang ditugaskan.');
        }

        // Hitung total jumlah
        $validated['jumlah'] = $validated['jumlah_baik']
            + $validated['jumlah_rusak_ringan']
            + $validated['jumlah_rusak_berat'];

        // Set kondisi dominan
        if (
            $validated['jumlah_baik'] >= $validated['jumlah_rusak_ringan']
            && $validated['jumlah_baik'] >= $validated['jumlah_rusak_berat']
        ) {
            $validated['kondisi'] = 'Baik';
        } elseif ($validated['jumlah_rusak_ringan'] >= $validated['jumlah_rusak_berat']) {
            $validated['kondisi'] = 'Rusak Ringan';
        } else {
            $validated['kondisi'] = 'Rusak Berat';
        }

        // Checkbox "barang bisa dipinjam"
        $validated['is_pinjaman'] = $request->has('is_pinjaman');

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store(null, 'gambar-barang');
        }

        Barang::create($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Data barang berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
    {
        // Check akses lokasi untuk petugas
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $barang->lokasi_id != $user->lokasi_id) {
            abort(403, 'Anda tidak memiliki akses ke barang di lokasi ini.');
        }

        $barang->load(['kategori', 'lokasi']);

        return view('barang.show', compact('barang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
        // Check akses lokasi untuk petugas
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $barang->lokasi_id != $user->lokasi_id) {
            abort(403, 'Anda tidak memiliki akses ke barang di lokasi ini.');
        }

        $kategori = Kategori::all();
        
        // Jika petugas, hanya tampilkan lokasi yang ditugaskan
        if ($user->isPetugas() && $user->lokasi_id) {
            $lokasi = Lokasi::where('id', $user->lokasi_id)->get();
        } else {
            $lokasi = Lokasi::all();
        }

        return view('barang.edit', compact('barang', 'kategori', 'lokasi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        // Check akses lokasi untuk petugas
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $barang->lokasi_id != $user->lokasi_id) {
            abort(403, 'Anda tidak memiliki akses ke barang di lokasi ini.');
        }

        $validated = $request->validate([
            'kode_barang'        => 'required|string|max:50|unique:barangs,kode_barang,' . $barang->id,
            'nama_barang'        => 'required|string|max:150',
            'kategori_id'        => 'required|exists:kategoris,id',
            'lokasi_id'          => 'required|exists:lokasis,id',
            'jumlah_baik'        => 'required|integer|min:0',
            'jumlah_rusak_ringan' => 'required|integer|min:0',
            'jumlah_rusak_berat' => 'required|integer|min:0',
            'satuan'             => 'required|string|max:20',
            'tanggal_pengadaan'  => 'required|date',
            'gambar'             => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_pinjaman'        => 'nullable|boolean',
        ]);

        // Validasi lokasi untuk petugas
        if ($user->isPetugas() && $user->lokasi_id && $validated['lokasi_id'] != $user->lokasi_id) {
            return back()->with('error', 'Anda hanya dapat memindahkan barang ke lokasi yang ditugaskan.');
        }

        // Hitung total jumlah
        $validated['jumlah'] = $validated['jumlah_baik']
            + $validated['jumlah_rusak_ringan']
            + $validated['jumlah_rusak_berat'];

        // Set kondisi dominan
        if (
            $validated['jumlah_baik'] >= $validated['jumlah_rusak_ringan']
            && $validated['jumlah_baik'] >= $validated['jumlah_rusak_berat']
        ) {
            $validated['kondisi'] = 'Baik';
        } elseif ($validated['jumlah_rusak_ringan'] >= $validated['jumlah_rusak_berat']) {
            $validated['kondisi'] = 'Rusak Ringan';
        } else {
            $validated['kondisi'] = 'Rusak Berat';
        }

        // Checkbox "barang bisa dipinjam"
        $validated['is_pinjaman'] = $request->has('is_pinjaman');

        if ($request->hasFile('gambar')) {
            if ($barang->gambar) {
                Storage::disk('gambar-barang')->delete($barang->gambar);
            }

            $validated['gambar'] = $request->file('gambar')->store(null, 'gambar-barang');
        }

        $barang->update($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        // Check akses lokasi untuk petugas
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $barang->lokasi_id != $user->lokasi_id) {
            abort(403, 'Anda tidak memiliki akses ke barang di lokasi ini.');
        }

        if ($barang->gambar) {
            Storage::disk('gambar-barang')->delete($barang->gambar);
        }

        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus.');
    }

    public function cetakLaporan()
    {
        $query = Barang::with(['kategori', 'lokasi']);
        
        // Filter berdasarkan lokasi untuk petugas
        $query = $this->applyLokasiFilter($query);
        
        $barangs = $query->get();

        $data = [
            'title' => 'Laporan Data Barang Inventaris',
            'date' => date('d F Y'),
            'barangs' => $barangs
        ];

        $pdf = Pdf::loadView('barang.laporan', $data);

        return $pdf->stream('laporan-inventaris-barang.pdf');
    }
}