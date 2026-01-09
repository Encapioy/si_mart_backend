<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Merchant;
use Illuminate\Support\Facades\Storage;

class MerchantController extends Controller
{
    // 1. User Mengajukan Pembukaan Toko
    public function register(Request $request)
    {
        // Cek apakah user sudah punya toko (apapun statusnya)
        $existingMerchant = Merchant::where('user_id', auth()->id())->first();
        if ($existingMerchant) {
            return response()->json([
                'message' => 'Anda sudah terdaftar sebagai merchant.',
                'status' => $existingMerchant->status
            ], 400);
        }

        $request->validate([
            'shop_name' => 'required|string|unique:merchants,shop_name|max:50',
            'description' => 'nullable|string',
            'ktp_image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        // Upload Foto
        $path = null;
        if ($request->hasFile('ktp_image')) {
            $path = $request->file('ktp_image')->store('merchant_verifications', 'public');
        }

        $merchant = Merchant::create([
            'user_id' => auth()->id(),
            'shop_name' => $request->shop_name,
            'description' => $request->description,
            'ktp_image' => $path,
            'status' => 'pending',
            'balance' => 0
        ]);

        return response()->json([
            'message' => 'Pengajuan toko berhasil dikirim. Mohon tunggu verifikasi admin.',
            'data' => $merchant
        ], 201);
    }

    // 2. Cek Status Pengajuan Saya
    public function checkStatus()
    {
        $merchant = Merchant::where('user_id', auth()->id())->first();

        if (!$merchant) {
            return response()->json(['status' => 'not_registered', 'data' => null]);
        }

        return response()->json([
            'status' => $merchant->status,
            'data' => $merchant
        ]);
    }

    // 3. Update Data (Misal disuruh revisi foto)
    public function update(Request $request)
    {
        $merchant = Merchant::where('user_id', auth()->id())->firstOrFail();

        // Hanya boleh update kalau status pending atau rejected
        if (!in_array($merchant->status, ['pending', 'rejected'])) {
            return response()->json(['message' => 'Data toko yang sudah aktif tidak bisa diubah sembarangan.'], 403);
        }

        $request->validate([
            'shop_name' => 'sometimes|required|unique:merchants,shop_name,' . $merchant->id,
            'ktp_image' => 'nullable|image|max:2048'
        ]);

        $dataToUpdate = $request->only(['shop_name', 'description']);

        if ($request->hasFile('ktp_image')) {
            // Hapus foto lama
            if ($merchant->ktp_image) {
                Storage::disk('public')->delete($merchant->ktp_image);
            }
            $dataToUpdate['ktp_image'] = $request->file('ktp_image')->store('merchant_verifications', 'public');
        }

        // Kalau habis direvisi, kembalikan status jadi pending biar dicek admin lagi
        $dataToUpdate['status'] = 'pending';
        $dataToUpdate['rejection_reason'] = null;

        $merchant->update($dataToUpdate);

        return response()->json(['message' => 'Data pengajuan berhasil diperbarui.', 'data' => $merchant]);
    }

    // 4. GENERATE QR CODE
    public function generateQrCode(Request $request)
    {
        $user = $request->user();

        // 1. Cari data Merchant milik user ini
        // Asumsi: relasi user ke merchant sudah ada
        $merchant = Merchant::where('user_id', $user->id)->first();

        if (!$merchant) {
            return response()->json(['message' => 'Anda bukan merchant'], 404);
        }

        // 2. Racik String Payload
        // Format: SIPAY:MERCHANT:{ID_MERCHANT}:{NAMA_TOKO}
        // Kita tambahkan base64 encode biar keliatan "rahasia" dikit dan aman dari spasi
        $rawData = "SIPAY:MERCHANT:" . $merchant->id . ":" . $merchant->name;

        // Opsi A: Kirim Raw String (Gampang dibaca manusia)
        // $payload = $rawData;

        // Opsi B: Encode Base64 (Lebih rapi url-safe)
        $payload = base64_encode($rawData);

        return response()->json([
            'status' => 'success',
            'data' => [
                'merchant_name' => $merchant->name,
                'qr_payload' => $payload, // <--- INI YANG PENTING
                'description' => 'Tunjukkan QR ini ke pelanggan untuk menerima pembayaran.'
            ]
        ]);
    }
}