<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Merchant;

class MerchantVerificationController extends Controller
{
    // 1. List Semua Pengajuan Pending
    public function listPending()
    {
        $pendingMerchants = Merchant::with('user:id,name,email,username') // Eager load data user
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pendingMerchants);
    }

    // 2. Detail Pengajuan (Lihat Foto KTP dll)
    public function show($id)
    {
        $merchant = Merchant::with('user')->findOrFail($id);
        return response()->json($merchant);
    }

    // 3. Approve (Terima)
    public function approve($id)
    {
        $merchant = Merchant::findOrFail($id);

        if ($merchant->status === 'approved') {
            return response()->json(['message' => 'Toko ini sudah aktif sebelumnya.'], 400);
        }

        $merchant->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(), // Admin yang login
            'rejection_reason' => null
        ]);

        // Opsional: Kirim notifikasi fcm/email ke user

        return response()->json([
            'message' => 'Toko berhasil disetujui. User sekarang bisa berjualan.',
            'data' => $merchant
        ]);
    }

    // 4. Reject (Tolak)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5'
        ]);

        $merchant = Merchant::findOrFail($id);

        $merchant->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'approved_at' => null,
            'approved_by' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Pengajuan toko ditolak.',
            'data' => $merchant
        ]);
    }
}