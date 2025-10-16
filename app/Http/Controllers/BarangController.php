<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    private function applyLokasiFilter($query)
    {
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id) {
            $query->where('lokasi_id', $user->lokasi_id);
        }
        return $query;
    }

    public function index(Request $request)
    {
        $search = $request->search;

        // Query untuk mengambil hanya parent barang
        $query = Barang::with(['kategori', 'lokasi'])
            ->where(function ($q) {
                // Mode masal
                $q->where('mode_input', 'masal')
                    // Atau mode unit yang merupakan parent (kode_dasar = kode_barang atau masih null)
                    ->orWhere(function ($subQ) {
                        $subQ->where('mode_input', 'unit')
                            ->where(function ($nested) {
                                $nested->whereColumn('kode_barang', 'kode_dasar')
                                    ->orWhereNull('kode_dasar');
                            });
                    });
            });

        // Tambahkan child units untuk barang mode unit
        $query->with(['childUnits' => function ($q) {
            $q->with(['kategori', 'lokasi']);
        }]);

        // Jika ada pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        $barangs = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return view('barang.index', compact('barangs'));
    }


    public function create()
    {
        $kategori = Kategori::all();

        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id) {
            $lokasi = Lokasi::where('id', $user->lokasi_id)->get();
        } else {
            $lokasi = Lokasi::all();
        }

        $barang = new Barang();

        return view('barang.create', compact('barang', 'kategori', 'lokasi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mode_input' => 'nullable|in:masal,unit',
            'kode_barang' => 'required|string|max:50',
            'nama_barang' => 'required|string|max:150',
            'kategori_id' => 'required|exists:kategoris,id',
            'lokasi_id' => 'required|exists:lokasis,id',
            'jumlah_baik' => 'required|integer|min:0',
            'jumlah_rusak_ringan' => 'required|integer|min:0',
            'jumlah_rusak_berat' => 'required|integer|min:0',
            'satuan' => 'required|string|max:20',
            'tanggal_pengadaan' => 'required|date',
            'sumber' => 'nullable|string|max:100',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_pinjaman' => 'nullable|boolean',
        ]);

        // default mode_input = masal
        $validated['mode_input'] = $validated['mode_input'] ?? 'masal';

        // Validasi lokasi jika user petugas
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $validated['lokasi_id'] != $user->lokasi_id) {
            return back()->with('error', 'Anda hanya dapat menambahkan barang di lokasi yang ditugaskan.');
        }

        $validated['is_pinjaman'] = $request->has('is_pinjaman');

        // Upload gambar
        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store(null, 'gambar-barang');
        }

        // 🔧 Tambahkan logika kode_dasar otomatis
        if ($validated['mode_input'] === 'unit') {
            $validated['kode_dasar'] = $validated['kode_barang'];
        } else {
            $validated['kode_dasar'] = null;
        }

        DB::beginTransaction();
        try {
            if ($validated['mode_input'] === 'unit') {
                $this->storePerUnit($validated);
            } else {
                $this->storeMasal($validated);
            }

            DB::commit();
            return redirect()->route('barang.index')
                ->with('success', 'Data barang berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function storeMasal(array $data)
    {
        $data['jumlah'] = $data['jumlah_baik'] + $data['jumlah_rusak_ringan'] + $data['jumlah_rusak_berat'];
        $data['kondisi'] = $this->determineKondisi($data);
        $data['kode_dasar'] = null;

        Barang::create($data);
    }

    private function storePerUnit(array $data)
    {
        $totalUnit = $data['jumlah_baik'];
        $kodeBase = $this->extractKodeBase($data['kode_barang']);
        $startNumber = $this->extractStartNumber($data['kode_barang']);

        // Simpan parent barang sebagai unit pertama
        $parentKode = $kodeBase . str_pad($startNumber, 2, '0', STR_PAD_LEFT);

        $parent = Barang::create([
            'mode_input' => 'unit',
            'kode_barang' => $parentKode,
            'kode_dasar' => $parentKode, // kode_dasar = kode_barang untuk parent
            'nama_barang' => $data['nama_barang'],
            'kategori_id' => $data['kategori_id'],
            'lokasi_id' => $data['lokasi_id'],
            'jumlah_baik' => 1,
            'jumlah_rusak_ringan' => 0,
            'jumlah_rusak_berat' => 0,
            'jumlah' => 1,
            'kondisi' => 'Baik',
            'satuan' => $data['satuan'],
            'tanggal_pengadaan' => $data['tanggal_pengadaan'],
            'sumber' => $data['sumber'],
            'gambar' => $data['gambar'] ?? null,
            'is_pinjaman' => $data['is_pinjaman'],
        ]);

        // Simpan child units (dimulai dari unit ke-2)
        for ($i = 1; $i < $totalUnit; $i++) {
            $unitNumber = $startNumber + $i;
            $kodeUnit = $kodeBase . str_pad($unitNumber, 2, '0', STR_PAD_LEFT);

            Barang::create([
                'mode_input' => 'unit',
                'kode_barang' => $kodeUnit,
                'kode_dasar' => $parentKode, // mengacu ke parent
                'nama_barang' => $data['nama_barang'],
                'kategori_id' => $data['kategori_id'],
                'lokasi_id' => $data['lokasi_id'],
                'jumlah_baik' => 1,
                'jumlah_rusak_ringan' => 0,
                'jumlah_rusak_berat' => 0,
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'satuan' => $data['satuan'],
                'tanggal_pengadaan' => $data['tanggal_pengadaan'],
                'sumber' => $data['sumber'],
                'gambar' => $data['gambar'] ?? null,
                'is_pinjaman' => $data['is_pinjaman'],
            ]);
        }
    }

    private function extractKodeBase(string $kode): string
    {
        return preg_replace('/\d+$/', '', $kode);
    }

    private function extractStartNumber(string $kode): int
    {
        preg_match('/\d+$/', $kode, $matches);
        return $matches[0] ?? 1;
    }

    private function determineKondisi(array $data): string
    {
        $baik = $data['jumlah_baik'];
        $ringan = $data['jumlah_rusak_ringan'];
        $berat = $data['jumlah_rusak_berat'];

        if ($baik >= $ringan && $baik >= $berat) {
            return 'Baik';
        } elseif ($ringan >= $berat) {
            return 'Rusak Ringan';
        }
        return 'Rusak Berat';
    }

    public function show(Barang $barang)
    {
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $barang->lokasi_id != $user->lokasi_id) {
            abort(403, 'Anda tidak memiliki akses ke barang di lokasi ini.');
        }

        $barang->load(['kategori', 'lokasi', 'childUnits']);
        return view('barang.show', compact('barang'));
    }

    public function edit(Barang $barang)
    {
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $barang->lokasi_id != $user->lokasi_id) {
            abort(403, 'Anda tidak memiliki akses ke barang di lokasi ini.');
        }

        $kategori = Kategori::all();
        if ($user->isPetugas() && $user->lokasi_id) {
            $lokasi = Lokasi::where('id', $user->lokasi_id)->get();
        } else {
            $lokasi = Lokasi::all();
        }

        return view('barang.edit', compact('barang', 'kategori', 'lokasi'));
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'mode_input' => 'nullable|in:masal,unit',
            'kode_barang' => 'required|string|max:50',
            'nama_barang' => 'required|string|max:150',
            'kategori_id' => 'required|exists:kategoris,id',
            'lokasi_id' => 'required|exists:lokasis,id',
            'jumlah_baik' => 'required|integer|min:0',
            'jumlah_rusak_ringan' => 'required|integer|min:0',
            'jumlah_rusak_berat' => 'required|integer|min:0',
            'satuan' => 'required|string|max:20',
            'tanggal_pengadaan' => 'required|date',
            'sumber' => 'nullable|string|max:100',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_pinjaman' => 'nullable|boolean',
        ]);

        $validated['mode_input'] = $validated['mode_input'] ?? 'masal';
        $validated['is_pinjaman'] = $request->has('is_pinjaman');

        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $validated['lokasi_id'] != $user->lokasi_id) {
            return back()->with('error', 'Anda hanya dapat mengubah barang di lokasi yang ditugaskan.');
        }

        // Cegah perubahan mode_input dari masal → unit
        if ($barang->mode_input === 'masal' && $request->mode_input === 'unit') {
            return back()->with('error', 'Barang dengan mode input "Masal" tidak dapat diubah menjadi "Per Unit".');
        }

        // Upload gambar baru jika ada
        if ($request->hasFile('gambar')) {
            if ($barang->gambar && \Storage::disk('gambar-barang')->exists($barang->gambar)) {
                \Storage::disk('gambar-barang')->delete($barang->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store(null, 'gambar-barang');
        }

        // Logika mode_input dan kode_dasar
        if ($validated['mode_input'] === 'unit' && empty($barang->kode_dasar)) {
            $validated['kode_dasar'] = $barang->kode_barang;
        } elseif ($validated['mode_input'] === 'masal') {
            $validated['kode_dasar'] = null;
        }

        $barang->update($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }


    public function destroy(Barang $barang)
    {
        $user = Auth::user();
        if ($user->isPetugas() && $user->lokasi_id && $barang->lokasi_id != $user->lokasi_id) {
            abort(403, 'Anda tidak memiliki akses ke barang di lokasi ini.');
        }

        DB::beginTransaction();
        try {
            // Jika barang adalah parent unit, hapus semua child units juga
            if ($barang->mode_input === 'unit' && $barang->kode_barang === $barang->kode_dasar) {
                $childUnits = Barang::where('kode_dasar', $barang->kode_barang)
                    ->where('id', '!=', $barang->id)
                    ->get();

                foreach ($childUnits as $child) {
                    if ($child->gambar) {
                        Storage::disk('gambar-barang')->delete($child->gambar);
                    }
                    $child->delete();
                }
            }

            // Hapus gambar parent
            if ($barang->gambar) {
                Storage::disk('gambar-barang')->delete($barang->gambar);
            }

            // Hapus parent barang
            $barang->delete();

            DB::commit();
            return redirect()->route('barang.index')
                ->with('success', 'Data barang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cetakLaporan()
    {
        $query = Barang::with(['kategori', 'lokasi']);
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
