<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdatePeminjamanStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'peminjaman:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status peminjaman yang terlambat';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai update status peminjaman...');
        
        // Update status peminjaman yang terlambat
        $overdueCount = Peminjaman::where('tanggal_kembali_rencana', '<', Carbon::now())
            ->whereNull('tanggal_kembali_aktual')
            ->where('status', '!=', 'Terlambat')
            ->update(['status' => 'Terlambat']);
        
        $this->info("Berhasil update {$overdueCount} peminjaman menjadi status 'Terlambat'");
        
        // Tampilkan statistik
        $stats = [
            'Total Peminjaman' => Peminjaman::count(),
            'Sedang Dipinjam' => Peminjaman::where('status', 'Sedang Dipinjam')->count(),
            'Terlambat' => Peminjaman::where('status', 'Terlambat')->count(),
            'Sudah Dikembalikan' => Peminjaman::where('status', 'Sudah Dikembalikan')->count(),
        ];
        
        $this->table(
            ['Status', 'Jumlah'],
            collect($stats)->map(fn($count, $status) => [$status, $count])->toArray()
        );
        
        $this->info('Update status selesai!');
        
        return Command::SUCCESS;
    }
}