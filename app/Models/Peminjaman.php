<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Peminjaman extends Model
{
    /**
     * Nama tabel yang digunakan model ini.
     */
    protected $table = 'peminjaman';

    /**
     * Kolom yang bisa diisi massal.
     */
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
        'kondisi_barang',
    ];

    /**
     * Konversi otomatis ke instance Carbon.
     */
    protected $casts = [
        'tanggal_pinjam' => 'datetime',
        'tanggal_kembali_rencana' => 'datetime',
        'tanggal_kembali_aktual' => 'datetime',
    ];

    /**
     * Relasi ke model Barang.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Generate nomor transaksi otomatis.
     */
    public static function generateNomorTransaksi()
    {
        $lastTransaction = self::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction ? intval(substr($lastTransaction->nomor_transaksi, -3)) + 1 : 1;

        return 'TXN-' . Carbon::now()->format('Ymd') . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung durasi peminjaman dalam format manusiawi.
     */
    public function getDurasiPeminjamanAttribute()
    {
        $endDate = $this->tanggal_kembali_aktual ?: Carbon::now();
        $diff = $this->tanggal_pinjam->diff($endDate);

        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' tahun';
        if ($diff->m > 0) $parts[] = $diff->m . ' bulan';
        if ($diff->d > 0) $parts[] = $diff->d . ' hari';
        if ($diff->h > 0) $parts[] = $diff->h . ' jam';
        if ($diff->i > 0) $parts[] = $diff->i . ' menit';

        return implode(' ', $parts) ?: '0 menit';
    }

    /**
     * Menentukan apakah peminjaman terlambat.
     */
    public function getTerlambatAttribute()
    {
        if (!$this->tanggal_kembali_rencana) return false;
        if ($this->tanggal_kembali_aktual) return false;

        return Carbon::now()->greaterThan($this->tanggal_kembali_rencana);
    }

    /**
     * Hitung berapa hari keterlambatan.
     */
    public function getHariTerlambatAttribute()
    {
        if (!$this->terlambat) return 0;

        $tanggalKembali = $this->tanggal_kembali_aktual ?: Carbon::now();
        return max(0, $this->tanggal_kembali_rencana->diffInDays($tanggalKembali));
    }

    /**
     * Update status peminjaman berdasarkan tanggal dan kondisi.
     */
    public function updateStatus($autoSave = true)
    {
        if ($this->tanggal_kembali_aktual) {
            $this->status = 'Sudah Dikembalikan';
        } elseif ($this->terlambat) {
            $this->status = 'Terlambat';
        } else {
            $this->status = 'Sedang Dipinjam';
        }

        if ($autoSave) {
            $this->save();
        }
    }

    /**
     * Scope untuk peminjaman yang terlambat.
     */
    public function scopeTerlambat($query)
    {
        return $query->where('tanggal_kembali_rencana', '<', Carbon::now())
            ->whereNull('tanggal_kembali_aktual');
    }

    /**
     * Scope untuk peminjaman yang masih aktif.
     */
    public function scopeAktif($query)
    {
        return $query->whereNull('tanggal_kembali_aktual');
    }

    /**
     * Detail keterlambatan dalam format string.
     */
    public function getTerlambatDetailAttribute()
    {
        if (!$this->terlambat) return null;

        $tanggalKembali = $this->tanggal_kembali_aktual ?: Carbon::now();
        $diff = $this->tanggal_kembali_rencana->diff($tanggalKembali);

        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' tahun';
        if ($diff->m > 0) $parts[] = $diff->m . ' bulan';
        if ($diff->d > 0) $parts[] = $diff->d . ' hari';
        if ($diff->h > 0) $parts[] = $diff->h . ' jam';
        if ($diff->i > 0) $parts[] = $diff->i . ' menit';

        return implode(' ', $parts);
    }

    /**
     * Accessor untuk format tanggal agar tampilan lebih rapi di view.
     */
    public function getTanggalPinjamFormattedAttribute()
    {
        return $this->tanggal_pinjam ? $this->tanggal_pinjam->format('d M Y H:i') : '-';
    }

    public function getTanggalKembaliRencanaFormattedAttribute()
    {
        return $this->tanggal_kembali_rencana ? $this->tanggal_kembali_rencana->format('d M Y H:i') : '-';
    }

    public function getTanggalKembaliAktualFormattedAttribute()
    {
        return $this->tanggal_kembali_aktual ? $this->tanggal_kembali_aktual->format('d M Y H:i') : '-';
    }

    /**
     * Update status otomatis setiap kali model disimpan.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($peminjaman) {
            $peminjaman->updateStatus(false);
        });
    }
}
