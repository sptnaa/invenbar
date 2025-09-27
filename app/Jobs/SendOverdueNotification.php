<?php

namespace App\Jobs;

use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOverdueNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Ambil peminjaman yang akan jatuh tempo dalam 1 hari
        $upcomingDue = Peminjaman::with(['barang'])
            ->whereDate('tanggal_kembali_rencana', Carbon::tomorrow())
            ->whereNull('tanggal_kembali_aktual')
            ->get();

        // Ambil peminjaman yang sudah terlambat
        $overdue = Peminjaman::with(['barang'])
            ->where('tanggal_kembali_rencana', '<', Carbon::now())
            ->whereNull('tanggal_kembali_aktual')
            ->get();

        // Update status yang terlambat
        Peminjaman::terlambat()->update(['status' => 'Terlambat']);

        // Log untuk monitoring
        Log::info('Overdue notification job executed', [
            'upcoming_due_count' => $upcomingDue->count(),
            'overdue_count' => $overdue->count(),
        ]);

        // Kirim notifikasi ke admin (jika ada email admin)
        $admins = User::role('admin')->whereNotNull('email')->get();
        
        foreach ($admins as $admin) {
            if ($upcomingDue->count() > 0 || $overdue->count() > 0) {
                try {
                    Mail::send('emails.overdue-notification', [
                        'admin' => $admin,
                        'upcomingDue' => $upcomingDue,
                        'overdue' => $overdue,
                    ], function ($message) use ($admin) {
                        $message->to($admin->email)
                                ->subject('Notifikasi Peminjaman Barang - ' . config('app.name'));
                    });
                } catch (\Exception $e) {
                    Log::error('Failed to send overdue notification', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Bisa ditambahkan notifikasi ke peminjam jika ada email
        foreach ($upcomingDue as $peminjaman) {
            if ($peminjaman->email_peminjam) {
                try {
                    Mail::send('emails.due-reminder', [
                        'peminjaman' => $peminjaman,
                    ], function ($message) use ($peminjaman) {
                        $message->to($peminjaman->email_peminjam)
                                ->subject('Pengingat Pengembalian Barang - ' . $peminjaman->nomor_transaksi);
                    });
                } catch (\Exception $e) {
                    Log::error('Failed to send due reminder to borrower', [
                        'peminjaman_id' => $peminjaman->id,
                        'email' => $peminjaman->email_peminjam,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}