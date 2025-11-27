<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\TopUp;
use App\Models\Admin;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    // FITUR KEUANGAN (TOP UP)

    // 1. Lihat Request Top Up
    public function getPendingTopUps(Request $request)
    {
        if ($request->user()->role !== 'keuangan')
            return response()->json(['message' => 'Unauthorized'], 403);

        $data = TopUp::with('user')->where('status', 'pending')->latest()->get();
        return response()->json(['data' => $data]);
    }

    // 2. Approve Top Up (Input Nominal Real)
    public function approveTopUp(Request $request, $id)
    {
        if ($request->user()->role !== 'keuangan')
            return response()->json(['message' => 'Unauthorized'], 403);

        $topup = TopUp::find($id);
        if (!$topup)
            return response()->json(['message' => 'Not found'], 404);

        // Admin bisa mengoreksi nominal (misal user transfer 50.001 tapi input 50.000)
        $realAmount = $request->input('real_amount', $topup->amount);

        // Tambah Saldo User
        $user = User::find($topup->user_id);
        $user->saldo += $realAmount;
        $user->save();

        $this->recordMutation($user, $realAmount, 'credit', 'topup', 'Top Up Saldo');

        // Update Status
        $topup->status = 'approved';
        $topup->amount = $realAmount; // Update angka final
        $topup->admin_id = $request->user()->id;
        $topup->save();

        return response()->json(['message' => 'Top Up disetujui. Saldo user bertambah.', 'saldo_baru' => $user->saldo]);
    }

    // 3. Top Up Manual (tunai)
    // Admin menerima uang cash -> Tembak saldo user langsung
    public function manualTopUp(Request $request)
    {
        // Pastikan yang akses adalah Admin Keuangan
        if ($request->user()->role !== 'keuangan') {
            return response()->json(['message' => 'Unauthorized. Hanya Admin Keuangan.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            // Bisa pakai Username, NFC, atau Member ID (Pencarian Pintar)
            'identity_code' => 'required|string',
            'amount' => 'required|integer|min:1000',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // Cari User (Logic Pintar: Username / NFC / Member ID)
        $kode = $request->identity_code;
        $targetUser = User::where('username', $kode)
            ->orWhere('nfc_id', $kode)
            ->orWhere('member_id', $kode)
            ->first();

        if (!$targetUser)
            return response()->json(['message' => 'User tidak ditemukan'], 404);

        // Eksekusi Top Up
        $targetUser->saldo += $request->amount;
        $targetUser->save();

        $this->recordMutation($targetUser, $request->amount, 'credit', 'topup', 'Top Up Saldo');

        // Opsional: Catat di tabel TopUp juga dengan status 'approved' otomatis
        // Biar masuk laporan keuangan
        TopUp::create([
            'user_id' => $targetUser->id,
            'amount' => $request->amount,
            'bukti_transfer' => 'CASH_DEPOSIT', // Penanda kalau ini tunai
            'status' => 'approved',
            'admin_id' => $request->user()->id
        ]);

        return response()->json([
            'message' => 'Top Up Tunai Berhasil',
            'user' => $targetUser->nama_lengkap,
            'saldo_baru' => $targetUser->saldo
        ]);
    }

    // --- FITUR KEUANGAN (WITHDRAW UPDATE) ---

    // 1. LIHAT LIST PENGAJUAN (Hanya status Pending)
    public function getPendingWithdrawals(Request $request)
    {
        // Pastikan yang akses adalah Admin
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Ambil data withdraw yang pending + info usernya
        $list = Withdrawal::where('status', 'pending')
            ->with('user') // Biar admin tau ini punya siapa
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'List pengajuan penarikan',
            'data' => $list
        ]);
    }

    // 2. SETUJUI PENARIKAN (Admin kasih uang -> Klik Approve)
    public function approveWithdrawal(Request $request, $id)
    {
        if ($request->user()->role !== 'keuangan')
            return response()->json(['message' => 'Unauthorized'], 403);

        $withdrawal = Withdrawal::find($id);
        if (!$withdrawal)
            return response()->json(['message' => 'Not found'], 404);

        // Jika metode transfer, Admin wajib upload bukti & boleh isi fee
        if ($withdrawal->bank_name != null) {
            $request->validate([
                'bukti_transfer_admin' => 'required|image',
                'admin_fee' => 'nullable|integer',
            ]);

            // Upload Bukti Admin
            $path = $request->file('bukti_transfer_admin')->store('withdraw_proofs', 'public');
            $withdrawal->bukti_transfer_admin = $path;

            // Logika Biaya Admin (Potong saldo user LAGI)
            $fee = $request->input('admin_fee', 0);
            if ($fee > 0) {
                $user = User::find($withdrawal->user_id);

                // Cek saldo user cukup gak buat bayar fee?
                if ($user->saldo < $fee) {
                    return response()->json(['message' => 'Gagal! Saldo user tidak cukup untuk bayar biaya admin.'], 400);
                }

                $user->saldo -= $fee; // Potong fee
                $user->save();
                $this->recordMutation($user, $fee, 'debit', 'admin_fee', 'Biaya Admin Penarikan');

                $withdrawal->admin_fee = $fee;
            }
        }

        $withdrawal->status = 'approved';
        $withdrawal->save();

        return response()->json(['message' => 'Penarikan disetujui.']);
    }

    // 3. TOLAK PENARIKAN (Opsional: Kalau ternyata merchant batal tarik)
    public function rejectWithdrawal(Request $request, $id)
    {
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $withdrawal = Withdrawal::find($id);
        if (!$withdrawal)
            return response()->json(['message' => 'Data tidak ditemukan'], 404);

        if ($withdrawal->status != 'pending') {
            return response()->json(['message' => 'Hanya status pending yang bisa ditolak'], 400);
        }

        // KEMBALIKAN SALDO USER (Refund)
        $user = User::find($withdrawal->user_id);
        $user->saldo += $withdrawal->amount;
        $user->save();

        // Update status jadi Rejected
        $withdrawal->status = 'rejected';
        $withdrawal->save();

        return response()->json([
            'message' => 'Penarikan ditolak. Saldo dikembalikan ke user.',
        ]);
    }

    // --- FITUR VERIFIKASI USER (MERCHANT) ---

    // 4. LIHAT LIST USER PENDING (Yang baru upload KTP)
    public function getPendingUsers(Request $request)
    {
        // Cek Admin
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Ambil user yang statusnya 'pending'
        $pendingUsers = User::where('status_verifikasi', 'pending')
            ->orderBy('updated_at', 'asc') // Urutkan dari yang paling lama nunggu
            ->get();

        return response()->json([
            'message' => 'List user menunggu verifikasi',
            'data' => $pendingUsers
        ]);
    }

    // 5. APPROVE USER (Terima jadi Merchant)
    public function approveUser(Request $request, $id)
    {
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::find($id);
        if (!$user)
            return response()->json(['message' => 'User tidak ditemukan'], 404);

        $user->status_verifikasi = 'verified'; // SAH!
        $user->save();

        return response()->json([
            'message' => 'User berhasil diverifikasi. Sekarang bisa berjualan.',
            'data' => $user
        ]);
    }

    // 6. REJECT USER (Tolak KTP buram/salah)
    public function rejectUser(Request $request, $id)
    {
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::find($id);
        if (!$user)
            return response()->json(['message' => 'User tidak ditemukan'], 404);

        $user->status_verifikasi = 'rejected'; // Ditolak
        // Opsional: Hapus foto biar dia upload ulang
        $user->save();

        return response()->json([
            'message' => 'Verifikasi user ditolak.',
            'data' => $user
        ]);
    }

    // 7. GENERATE MEMBER ID UNTUK USER LAMA (FIXING)
    public function generateOldMemberIds(Request $request)
    {
        // Cek Admin
        if (!($request->user() instanceof Admin))
            return response()->json(['message' => 'Unauthorized'], 403);

        // Ambil semua user yang member_id-nya masih kosong
        $users = User::whereNull('member_id')->get();
        $count = 0;
        $tahun = date('Y');

        foreach ($users as $user) {
            // Generate (tanpa loop while biar cepat, kemungkinan tabrakan kecil)
            $user->member_id = $tahun . mt_rand(10000000, 99999999);
            $user->save();
            $count++;
        }

        return response()->json(['message' => "Berhasil men-generate Member ID untuk $count user lama."]);
    }

    // 8. LIHAT BARANG SIMART YANG BELUM ADA RAKNYA (TODO LIST ADMIN KASIR)
    public function getProductsMissingRack(Request $request)
    {
        if ($request->user()->role !== 'kasir' && $request->user()->role !== 'pusat') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Cari Produk yang:
        // 1. Dijual di Simart (store_id NULL)
        // 2. Bukan Pre-Order (Ready Stock)
        // 3. Lokasi Rak-nya Masih Kosong
        $products = Product::whereNull('store_id')
            ->where('is_preorder', false)
            ->whereNull('lokasi_rak')
            ->with('seller:id,nama_lengkap') // Biar tau ini barang siapa
            ->get();

        return response()->json(['data' => $products]);
    }

    // 9. UPDATE LOKASI RAK (KERJAAN ADMIN KASIR)
    public function updateRackLocation(Request $request, $id)
    {
        if ($request->user()->role !== 'kasir' && $request->user()->role !== 'pusat') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['lokasi_rak' => 'required|string']);

        $product = Product::find($id);
        if (!$product)
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);

        $product->lokasi_rak = $request->lokasi_rak;
        $product->save();

        return response()->json(['message' => 'Lokasi rak berhasil diupdate', 'product' => $product]);
    }

    // 10. DASHBOARD STATISTIK (GLOBAL VIEW)
    public function getDashboardStats(Request $request)
    {
        // Pastikan yang akses adalah Admin (Semua tipe admin boleh lihat)
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 1. Statistik User
        $totalUsers = User::count();
        $totalMerchants = User::where('status_verifikasi', 'verified')->count();

        // 2. Statistik Produk
        $totalProducts = Product::count();
        // Barang yang stoknya 0 atau minus
        $outOfStock = Product::where('stok', '<=', 0)->count();

        // 3. Statistik Keuangan (Snapshot)
        // Total uang fisik yang 'mengendap' di sistem (Saldo User)
        // Ini penting buat Admin Keuangan tau berapa uang tunai yang harus dia pegang
        $totalUserBalance = User::sum('saldo');

        // 4. Statistik Transaksi Harian (Hari Ini)
        $today = now()->today();
        $trxTodayCount = Transaction::whereDate('created_at', $today)->where('status', 'paid')->count();
        $trxTodayVolume = Transaction::whereDate('created_at', $today)->where('status', 'paid')->sum('total_bayar');

        // 5. Statistik Tugas Admin (Pending Tasks)
        // Ini untuk menampilkan "Badge Merah" notifikasi di menu admin
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $pendingTopUps = TopUp::where('status', 'pending')->count();
        $pendingVerifications = User::where('status_verifikasi', 'pending')->count();
        $missingRack = Product::whereNull('store_id')->where('is_preorder', false)->whereNull('lokasi_rak')->count();

        return response()->json([
            'message' => 'Statistik Dashboard',
            'data' => [
                'users' => [
                    'total' => $totalUsers,
                    'merchants' => $totalMerchants
                ],
                'inventory' => [
                    'total_products' => $totalProducts,
                    'out_of_stock' => $outOfStock,
                    'missing_rack' => $missingRack // Tugas Admin Kasir
                ],
                'finance' => [
                    'system_liabilities' => $totalUserBalance, // Hutang sistem ke user (Total Saldo)
                ],
                'activity_today' => [
                    'transaction_count' => $trxTodayCount,
                    'gross_volume' => $trxTodayVolume // Total uang masuk hari ini
                ],
                'todo_list' => [
                    'withdraw_requests' => $pendingWithdrawals, // Tugas Admin Keuangan
                    'topup_requests' => $pendingTopUps,         // Tugas Admin Keuangan
                    'merchant_approvals' => $pendingVerifications // Tugas Admin Pusat
                ]
            ]
        ]);
    }

    // 11. ADMIN EDIT DATA USER (Termasuk NFC)
    public function updateUser(Request $request, $id)
    {
        // Pastikan yang akses adalah Admin Pusat
        if ($request->user()->role !== 'pusat') {
            return response()->json(['message' => 'Unauthorized. Hanya Admin Pusat.'], 403);
        }

        $targetUser = User::find($id);
        if (!$targetUser)
            return response()->json(['message' => 'User tidak ditemukan'], 404);

        // Validasi
        // Perhatikan 'unique:users,email,'.$id. Ini artinya:
        // "Email harus unik, TAPI kecualikan untuk ID user ini sendiri" (biar gak error kalau gak ganti email)
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|unique:users,username,' . $id,
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'nfc_id' => 'nullable|string|unique:users,nfc_id,' . $id, // <--- Ini untuk update Kartu
            'member_id' => 'nullable|string|unique:users,member_id,' . $id,
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // Update Data (Hanya field yang dikirim saja yang diupdate)
        $targetUser->update($request->only([
            'nama_lengkap',
            'username',
            'email',
            'nfc_id',
            'member_id'
        ]));

        return response()->json([
            'message' => 'Data user berhasil diperbarui',
            'data' => $targetUser
        ]);
    }
}