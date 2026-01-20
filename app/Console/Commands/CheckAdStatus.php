<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Advertisement;
use App\Services\NotificationService; // Pastikan Service Notifikasimu ada/siap
use Carbon\Carbon;

class CheckAdStatus extends Command
{
    /**
     * Nama perintah saat dijalankan di terminal.
     */
    protected $signature = 'ads:check-status';

    /**
     * Deskripsi perintah.
     */
    protected $description = 'Cek status iklan: Kirim notif grace period & matikan iklan expired';

    /**
     * Eksekusi perintah.
     */
    public function handle()
    {
        $now = Carbon::now();

        // ======================================================
        // TUGAS 1: DETEKSI GRACE PERIOD (SISA WAKTU < 10 MENIT)
        // ======================================================

        // Cari iklan yang statusnya ACTIVE, belum dinotif,
        // dan waktu habisnya tinggal 10 menit lagi (atau kurang).
        $adsToNotify = Advertisement::where('status', 'active')
            ->where('is_notified', false)
            ->where('end_time', '<=', $now->copy()->addMinutes(10))
            ->get();

        foreach ($adsToNotify as $ad) {
            // 1. Ubah status jadi Grace Period
            $ad->update([
                'status' => 'grace_period',
                'is_notified' => true
            ]);

            // 2. Kirim Notifikasi ke User
            // Sesuaikan dengan sistem notifikasi kamu (Firebase/Database Notif)
            try {
                // Contoh pakai class buatanmu sendiri (sesuaikan)
                NotificationService::send(
                    $ad->user_id,
                    'Iklan Segera Berakhir!',
                    "Iklan toko {$ad->store->nama_toko} akan habis dalam 10 menit. Perpanjang sekarang agar tetap tayang!",
                    'ads_renew' // Tipe notif khusus biar bisa diklik lari ke halaman renew
                );

                $this->info("Notif dikirim ke User ID: {$ad->user_id}");
            } catch (\Exception $e) {
                $this->error("Gagal kirim notif: " . $e->getMessage());
            }
        }

        // ======================================================
        // TUGAS 2: MATIKAN IKLAN EXPIRED (LEWAT 10 MENIT DARI END_TIME)
        // ======================================================

        // Cari iklan yang statusnya 'grace_period'
        // TAPI waktu sekarang sudah LEBIH BESAR dari (end_time + 10 menit)
        $graceLimit = $now->copy()->subMinutes(10);

        // Logika: Jika end_time < (Now - 10 menit), berarti sudah telat 10 menit.
        $expiredAds = Advertisement::where('status', 'grace_period')
            ->where('end_time', '<', $graceLimit)
            ->get();

        foreach ($expiredAds as $ad) {
            $ad->update(['status' => 'expired']);
            $this->info("Iklan ID {$ad->id} telah expired.");
        }

        $this->info('Pengecekan selesai.');
    }
}