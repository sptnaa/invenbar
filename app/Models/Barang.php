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
    ];

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

    // Hitung stok tersedia (jumlah - yang sedang dipinjam)
    public function getStokTersediaAttribute()
    {
        $jumlahDipinjam = $this->peminjamans()
            ->aktif()
            ->sum('jumlah_pinjam');
        
        return $this->jumlah - $jumlahDipinjam;
    }

    // Check apakah barang bisa dipinjam
    public function canBeBorrowed($jumlahPinjam = 1)
    {
        return $this->stok_tersedia >= $jumlahPinjam;
    }

    // Scope untuk barang yang tersedia untuk dipinjam
    public function scopeTersedia($query, $jumlahMin = 1)
    {
        return $query->whereHas('peminjamans', function($q) use ($jumlahMin) {
            $q->selectRaw('barang_id, COALESCE(SUM(CASE WHEN tanggal_kembali_aktual IS NULL THEN jumlah_pinjam ELSE 0 END), 0) as total_dipinjam')
              ->groupBy('barang_id')
              ->havingRaw('(barangs.jumlah - total_dipinjam) >= ?', [$jumlahMin]);
        }, '=', 0)
        ->orWhereDoesntHave('peminjamans')
        ->where('jumlah', '>=', $jumlahMin);
    }
}