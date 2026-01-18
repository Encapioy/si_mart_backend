<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // 1. AMBIL LIST NOTIFIKASI
    public function index(Request $request)
    {
        // Ambil notif milik user yang login, urutkan dari terbaru
        // Pakai paginate biar ringan kalau datanya ribuan
        $notifs = $request->user()->notifikasi()->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $notifs
        ]);
    }

    // 2. TANDAI SUDAH DIBACA
    public function markAsRead(Request $request, $id)
    {
        $notif = $request->user()->notifikasi()->find($id);

        if ($notif) {
            $notif->update(['is_read' => true]);
            return response()->json(['status' => 'success', 'message' => 'Ditandai sudah dibaca']);
        }

        return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
    }
}