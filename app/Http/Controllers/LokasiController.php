<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LokasiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view lokasi', only: ['index', 'show']),
            new Middleware('permission:manage lokasi', except: ['index', 'show']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;

        $lokasis = Lokasi::withSum('barang', 'jumlah') // 'jumlah' = nama kolom stok
            ->when($search, function ($query, $search) {
                $query->where('nama_lokasi', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate()
            ->withQueryString();


        return view('lokasi.index', compact('lokasis'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lokasi = new Lokasi();

        return view('lokasi.create', compact('lokasi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:100|unique:lokasis,nama_lokasi',
        ]);

        Lokasi::create($validated);

        return redirect()->route('lokasi.index')
            ->with('success', 'Lokasi baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lokasi $lokasi)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lokasi $lokasi)
    {
        return view('lokasi.edit', compact('lokasi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lokasi $lokasi)
    {
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:100|unique:lokasis,nama_lokasi,' . $lokasi->id,
        ]);

        $lokasi->update($validated);

        return redirect()->route('lokasi.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lokasi $lokasi)
    {
        // Pastikan ada relasi barang() di model Lokasi
        if ($lokasi->barang()->exists()) {
            return redirect()->route('lokasi.index')
                ->with('error', 'Lokasi tidak dapat dihapus karena masih memiliki barang terkait.');
        }

        $lokasi->delete();

        return redirect()->route('lokasi.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}
