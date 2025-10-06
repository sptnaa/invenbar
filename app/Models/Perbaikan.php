<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Perbaikan extends Model
{
    protected $fillable = [
        'nomor_perbaikan',
        'barang_id',
        'peminjaman_id',
        'jumlah_rusak',
        'tingkat_kerusakan',
        'keterangan_kerusakan',
        'tanggal_masuk',
        'tanggal_selesai',
        'status',
        'catatan_perbaikan',
        'biaya_perbaikan'
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_selesai' => 'date',
        'biaya_perbaikan' => 'decimal:2'
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    // Generate nomor perbaikan otomatis
    public static function generateNomorPerbaikan()
    {
        $lastPerbaikan = self::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastPerbaikan ? intval(substr($lastPerbaikan->nomor_perbaikan, -3)) + 1 : 1;

        return 'PBK-' . Carbon::now()->format('Ymd') . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    // Hitung durasi perbaikan
    public function getDurasiPerbaikanAttribute()
    {
        if (!$this->tanggal_selesai) {
            $endDate = Carbon::now();
        } else {
            $endDate = $this->tanggal_selesai;
        }

        $diff = $this->tanggal_masuk->diff($endDate);
        
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' tahun';
        if ($diff->m > 0) $parts[] = $diff->m . ' bulan';
        if ($diff->d > 0) $parts[] = $diff->d . ' hari';

        return implode(' ', $parts) ?: '0 hari';
    }

    // Scope untuk perbaikan yang belum selesai
    public function scopeBelumSelesai($query)
    {
        return $query->whereIn('status', ['Menunggu', 'Dalam Perbaikan']);
    }

    // Scope untuk perbaikan yang sudah selesai
    public function scopeSelesai($query)
    {
        return $query->where('status', 'Selesai');
    }

    // Badge class untuk status
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'Menunggu' => 'bg-warning text-dark',
            'Dalam Perbaikan' => 'bg-info text-white',
            'Selesai' => 'bg-success text-white',
            default => 'bg-secondary text-white'
        };
    }

    // Badge class untuk tingkat kerusakan
    public function getKerusakanBadgeClassAttribute()
    {
        return match($this->tingkat_kerusakan) {
            'Rusak Ringan' => 'bg-warning text-dark',
            'Rusak Berat' => 'bg-danger text-white',
            default => 'bg-secondary text-white'
        };
    }
}