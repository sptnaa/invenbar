<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_pengadaan' => 'date',
        'is_pinjaman' => 'boolean',
    ];

    // ==========================
    // RELASI
    // ==========================
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function peminjamans(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'barang_id');
    }

    public function perbaikans(): HasMany
    {
        return $this->hasMany(Perbaikan::class, 'barang_id');
    }

    // ==========================
    // RELASI PARENT & CHILD UNIT
    // ==========================
    public function childUnits()
    {
        return $this->hasMany(Barang::class, 'kode_dasar', 'kode_barang')
            ->whereColumn('kode_barang', '!=', 'kode_dasar')
            ->orderBy('kode_barang');
    }

    public function parent()
    {
        return $this->belongsTo(Barang::class, 'kode_dasar', 'kode_barang');
    }

    public function isParentUnit()
    {
        return $this->mode_input === 'unit' &&
            ($this->kode_dasar === $this->kode_barang || $this->kode_dasar === null);
    }

    // ==========================
    // JUMLAH UNIT
    // ==========================
    public function getTotalUnitsAttribute()
    {
        if ($this->isParentUnit()) {
            return $this->childUnits()->count() + 1; // +1 termasuk parent
        }
        return 1;
    }

    public function getTotalUnitsLabelAttribute()
    {
        $jumlah = $this->total_units;
        $satuan = $jumlah > 1 ? 'Unit' : 'Unit';
        return "{$jumlah} {$satuan}";
    }

    // ==========================
    // KONDISI DOMINAN
    // ==========================
    public function getKondisiDominanAttribute()
    {
        $baik = $this->jumlah_baik ?? 0;
        $ringan = $this->jumlah_rusak_ringan ?? 0;
        $berat = $this->jumlah_rusak_berat ?? 0;

        if ($baik >= $ringan && $baik >= $berat) {
            return 'Baik';
        } elseif ($ringan >= $berat) {
            return 'Rusak Ringan';
        } else {
            return 'Rusak Berat';
        }
    }

    // ==========================
    // KONDISI TOTAL (parent + child)
    // ==========================
    public function getKondisiSummaryAttribute()
    {
        // ambil semua anak
        $childUnits = $this->childUnits;

        // gabungkan parent dan anak
        $allUnits = collect([$this])->merge($childUnits);

        // hitung jumlah berdasarkan kondisi
        return $allUnits->groupBy('kondisi')->map->count();
    }

    // ==========================
    // KONDISI UNTUK BADGE
    // ==========================
    public function getKondisiArrayAttribute()
    {
        $kondisi = [];

        if ($this->jumlah_baik > 0) {
            $kondisi[] = [
                'label' => "Baik ({$this->jumlah_baik})",
                'class' => 'condition-baik'
            ];
        }
        if ($this->jumlah_rusak_ringan > 0) {
            $kondisi[] = [
                'label' => "R. Ringan ({$this->jumlah_rusak_ringan})",
                'class' => 'condition-rusak-ringan'
            ];
        }
        if ($this->jumlah_rusak_berat > 0) {
            $kondisi[] = [
                'label' => "R. Berat ({$this->jumlah_rusak_berat})",
                'class' => 'condition-rusak-berat'
            ];
        }

        return $kondisi;
    }

    // ==========================
    // STOK & PERBAIKAN
    // ==========================
    public function getJumlahAttribute($value)
    {
        if (isset($this->attributes['jumlah'])) {
            return $this->attributes['jumlah'];
        }

        return ($this->jumlah_baik ?? 0)
            + ($this->jumlah_rusak_ringan ?? 0)
            + ($this->jumlah_rusak_berat ?? 0);
    }

    public function getStokTersediaAttribute()
    {
        $jumlahDipinjam = $this->peminjamans()
            ->aktif()
            ->sum('jumlah_pinjam');

        return $this->jumlah_baik - $jumlahDipinjam;
    }

    public function getJumlahDalamPerbaikanAttribute()
    {
        return $this->perbaikans()
            ->belumSelesai()
            ->sum('jumlah_rusak');
    }

    // ==========================
    // STATUS PINJAM & PERBAIKAN
    // ==========================
    public function canBeBorrowed($jumlahPinjam = 1)
    {
        return $this->is_pinjaman && $this->stok_tersedia >= $jumlahPinjam;
    }

    public function getSedangDipinjamAttribute()
    {
        return $this->peminjamans()->aktif()->exists();
    }

    public function getSedangDiperbaikiAttribute()
    {
        return $this->perbaikans()->belumSelesai()->exists();
    }

    // ==========================
    // MODE INPUT
    // ==========================
    public function getIsUnitModeAttribute()
    {
        return $this->mode_input === 'unit';
    }

    public function getIsMasalModeAttribute()
    {
        return $this->mode_input === 'masal';
    }

    // ==========================
    // SCOPE
    // ==========================
    public function scopeTersedia($query, $jumlahMin = 1)
    {
        return $query->whereRaw(
            '(jumlah_baik - (SELECT COALESCE(SUM(jumlah_pinjam),0) 
              FROM peminjamans 
              WHERE peminjamans.barang_id = barangs.id 
              AND tanggal_kembali_aktual IS NULL)) >= ?',
            [$jumlahMin]
        )->where('is_pinjaman', true);
    }

    public function scopePerluPerbaikan($query)
    {
        return $query->where(function ($q) {
            $q->where('jumlah_rusak_ringan', '>', 0)
                ->orWhere('jumlah_rusak_berat', '>', 0);
        });
    }

    public function scopeParentOnly($query)
    {
        return $query->where(function ($q) {
            $q->where('mode_input', 'masal')
                ->orWhere(function ($subQ) {
                    $subQ->where('mode_input', 'unit')
                        ->whereColumn('kode_barang', 'kode_dasar');
                })
                ->orWhere(function ($subQ) {
                    $subQ->where('mode_input', 'unit')
                        ->whereNull('kode_dasar')
                        ->orWhere('kode_dasar', '');
                });
        });
    }
}
