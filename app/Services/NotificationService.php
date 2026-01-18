<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Kirim notifikasi ke User (Simpan DB + Siap Kirim FCM)
     *
     * @param int $userId ID User penerima
     * @param string $title Judul Notif
     * @param string $body Isi Pesan
     * @param string $type Jenis (transaction, info, alert)
     * @param array $data Data tambahan (opsional)
     */
    public static function send($userId, $title, $body, $type = 'info', $data = [])
    {
        // 1. SIMPAN KE DATABASE (Agar muncul di list notifikasi aplikasi)
        $notif = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);

        // 2. KIRIM KE FIREBASE (PUSH NOTIF) - Tahap 3 Nanti
        // Nanti kita isi kodingan Firebase di sini.
        // Jadi kamu gak perlu ubah codingan di Controller lain. Cukup ubah di sini saja.

        return $notif;
    }
}