<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Peminjaman extends Model
{
    protected $fillable = [
        'nomor_transaksi',
        'nama_peminjam',
        'email_peminjam',
        'telepon_peminjam',
        'barang_id',
        'jumlah_pinjam',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'status',
        'keperluan',
        'keterangan',
        'denda'
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_kembali_rencana' => 'date',
        'tanggal_kembali_aktual' => 'date',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    // Generate nomor transaksi otomatis
    public static function generateNomorTransaksi()
    {
        $lastTransaction = self::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastTransaction ? intval(substr($lastTransaction->nomor_transaksi, -3)) + 1 : 1;
        
        return 'TXN-' . Carbon::now()->format('Ymd') . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    // Hitung durasi peminjaman
    public function getDurasiPeminjamanAttribute()
    {
        if ($this->tanggal_kembali_aktual) {
            return $this->tanggal_pinjam->diffInDays($this->tanggal_kembali_aktual) + 1;
        }
        return $this->tanggal_pinjam->diffInDays(Carbon::now()) + 1;
    }

    // Check apakah terlambat
    public function getTerlambatAttribute()
    {
        if ($this->status === 'Sudah Dikembalikan') {
            return false;
        }
        return Carbon::now()->greaterThan($this->tanggal_kembali_rencana);
    }

    // Hitung hari keterlambatan
    public function getHariTerlambatAttribute()
    {
        if (!$this->terlambat) {
            return 0;
        }
        
        $tanggalKembali = $this->tanggal_kembali_aktual ?: Carbon::now();
        return max(0, $this->tanggal_kembali_rencana->diffInDays($tanggalKembali));
    }

    // Update status berdasarkan tanggal
    public function updateStatus()
    {
        if ($this->tanggal_kembali_aktual) {
            $this->status = 'Sudah Dikembalikan';
        } elseif ($this->terlambat) {
            $this->status = 'Terlambat';
        } else {
            $this->status = 'Sedang Dipinjam';
        }
        $this->save();
    }

    // Scope untuk mendapatkan peminjaman yang terlambat
    public function scopeTerlambat($query)
    {
        return $query->where('tanggal_kembali_rencana', '<', Carbon::now())
                    ->whereNull('tanggal_kembali_aktual');
    }

    // Scope untuk peminjaman aktif
    public function scopeAktif($query)
    {
        return $query->whereNull('tanggal_kembali_aktual');
    }
}