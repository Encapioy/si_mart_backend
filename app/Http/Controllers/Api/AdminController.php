<?php

namespace App\Http\Controllers\Api;

use App\Services\NotificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\TopUp;
use App\Models\Admin;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\BalanceMutation;

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
        // -----------------------------------------------------------
        // PERBAIKAN UTAMA DI SINI:
        // Gunakan $request->user(), bukan $request->admin()
        // -----------------------------------------------------------
        $currentAdmin = $request->user();

        // 1. Cek Permission (Tambah 'dreamland')
        $allowedRoles = ['kasir', 'keuangan', 'developer', 'pusat', 'dreamland'];

        if (!in_array($currentAdmin->role, $allowedRoles)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // 2. Validasi Dinamis
        // Kita siapkan rule dasar dulu
        $rules = [
            'identity_code' => 'required|string',
            'amount' => 'required|integer|min:1000',
        ];

        // LOGIC KHUSUS DREAMLAND
        // Jika yang login Dreamland, WAJIB input cashier_id dan cashier_pin
        if ($currentAdmin->role === 'dreamland') {
            $rules['cashier_id'] = 'required|exists:admins,id'; // Asumsi tabel admins
            $rules['cashier_pin'] = 'required|string';
        } else {
            // Jika admin biasa, cukup PIN dia sendiri
            $rules['pin'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // 3. TENTUKAN EKSEKUTOR (SIAPA YANG TTD?)
        $executor = null;

        if ($currentAdmin->role === 'dreamland') {
            // CASE A: DREAMLAND (Mode Kiosk/Terminal)
            // Cari Kasir yang dipilih
            $cashier = Admin::find($request->cashier_id);

            // Validasi 1: Apakah data ada?
            if (!$cashier) {
                return response()->json(['message' => 'Data Kasir tidak ditemukan.'], 404);
            }

            // Validasi 2: [PENTING] Apakah role-nya benar-benar 'kasir'?
            // Jangan sampai dia memilih ID milik Developer atau Admin Pusat
            if ($cashier->role !== 'kasir') {
                return response()->json(['message' => 'ID yang dipilih bukan Kasir!'], 403);
            }

            // Cek PIN Kasir tersebut
            // Kita konversi ke string dulu biar aman perbandingannya
            if ((string) $cashier->pin !== (string) $request->cashier_pin) {
                return response()->json(['message' => 'Verifikasi Kasir Gagal! PIN Salah.'], 401);
            }

            // Eksekutornya adalah Kasir tersebut (Bukan akun Dreamland)
            $executor = $cashier;

        } else {
            // CASE B: ADMIN BIASA (Mode Mandiri)
            // Cek PIN Admin yang sedang login
            if ((string) $currentAdmin->pin !== (string) $request->pin) {
                return response()->json(['message' => 'PIN Anda Salah!'], 401);
            }

            // Eksekutornya adalah Admin yang login
            $executor = $currentAdmin;
        }

        // 4. Cari User Target
        $kode = $request->identity_code;
        $targetUser = User::where('member_id', $kode)
            ->orWhere('username', $kode)
            ->orWhere('nfc_id', $kode)
            ->orWhere('no_hp', $kode)
            ->first();

        if (!$targetUser)
            return response()->json(['message' => 'User tidak ditemukan'], 404);

        // --- MULAI TRANSAKSI ---
        DB::beginTransaction();
        try {
            // A. Update Saldo User
            $targetUser->saldo += $request->amount;
            $targetUser->save();

            // B. Simpan TopUp History
            // 'admin_id' diisi ID Eksekutor (Kasir Asli), bukan akun Dreamland
            TopUp::create([
                'user_id' => $targetUser->id,
                'amount' => $request->amount,
                'status' => 'approved',
                'admin_id' => $executor->id,
                'bukti_transfer' => 'MANUAL_CASH',
            ]);

            // C. Simpan Mutasi
            BalanceMutation::create([
                'user_id' => $targetUser->id,
                'type' => 'credit',
                'amount' => $request->amount,
                'current_balance' => $targetUser->saldo,
                'category' => 'topup',
                // Deskripsi mencatat nama kasir asli
                'description' => 'Setoran Tunai via Kasir ' . explode(' ', $executor->nama_lengkap)[0],
                'related_user_id' => $executor->id
            ]);

            // D. Kirim Notifikasi
            NotificationService::send(
                $targetUser->id,
                'Top Up Berhasil',
                'Saldo Rp ' . number_format($request->amount, 0, ',', '.') . ' masuk via Kasir ' . $executor->nama_lengkap,
                'topup',
                [
                    'amount' => $request->amount,
                    'admin_id' => $executor->id,
                    'kiosk_mode' => ($currentAdmin->role === 'dreamland') // Info tambahan buat debug
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Top Up Berhasil',
                'executor' => $executor->nama_lengkap, // Kirim balik nama kasir buat konfirmasi di app
                'data' => [
                    'saldo_baru' => $targetUser->saldo,
                    'penerima' => $targetUser->nama_lengkap,
                    'formatted_amount' => number_format($request->amount, 0, ',', '.')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // 5. TOP UP SALDO USER (Web Version)
    // FUNGSI 1: KHUSUS MENAMPILKAN HALAMAN (GET)
    // Ini yang dipanggil saat redirect login berhasil
    public function showTopUpPage()
    {
        return view('admin.topup');
    }
    public function webTopUp(Request $request) // Gunakan nama topUp agar sesuai dengan logic sebelumnya
    {
        // ... (Paste kodingan validasi & transaksi kamu yang panjang tadi di sini) ...

        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'target_username' => 'required|string',
            'amount' => 'required|integer|min:100000',
            'cashier_id' => 'required|exists:admins,id',
            'cashier_pin' => 'required|string',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // 2. Cari User
        $user = User::where('username', $request->target_username)->first();
        if (!$user)
            return response()->json(['message' => 'User tidak ditemukan!'], 404);

        // 3. Cek Kasir
        $cashier = Admin::find($request->cashier_id);
        if ($cashier->pin !== $request->cashier_pin) {
            return response()->json(['message' => 'PIN Kasir Salah!'], 401);
        }

        // 4. Eksekusi
        return DB::transaction(function () use ($user, $cashier, $request) {
            $user->saldo += $request->amount;
            $user->save();

            // Record Mutation (Pastikan fungsi ini ada)
            $this->recordMutation($user, $request->amount, 'credit', 'topup', 'Top Up via Kasir: ' . $cashier->nama_admin); // Cek apakah nama_admin atau nama_lengkap

            return response()->json([
                'message' => 'Top Up Berhasil!',
                'data' => [
                    'penerima' => $user->nama_lengkap,
                    'nominal' => $request->amount,
                    'kasir_bertugas' => $cashier->nama_admin // Cek nama kolom ini di model Admin
                ]
            ]);
        });
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

        // --- VALIDASI PIN ADMIN ---
        // Kita cek manual inputan PIN dari request
        if (!$request->filled('pin')) {
            return response()->json(['message' => 'PIN Admin wajib diisi!'], 400);
        }

        if ($request->user()->pin !== $request->pin) {
            return response()->json(['message' => 'PIN Admin Salah!'], 401);
        }

        $withdrawal = Withdrawal::find($id);
        if (!$withdrawal)
            return response()->json(['message' => 'Not found'], 404);

        $admin = $request->user(); // Admin yang sedang login (Keuangan)

        // Tambahkan nominal penarikan ke saldo Admin
        $admin->saldo += $withdrawal->amount;
        $admin->save();

        // Catat Mutasi Masuk untuk Admin
        $this->recordMutation(
            $admin,
            $withdrawal->amount,
            'credit',
            'withdraw_in',
            'Dana masuk dari penarikan user ID: ' . $withdrawal->user_id
        );

        // Jika metode transfer, Admin wajib upload bukti & boleh isi fee
        if ($withdrawal->bank_name != null) {
            $request->validate([
                'bukti_transfer_admin' => 'required|image',
                'admin_fee' => 'nullable|integer',
            ]);

            $path = $request->file('bukti_transfer_admin')->store('withdraw_proofs', 'public');
            $withdrawal->bukti_transfer_admin = $path;

            // Logika Biaya Admin (Fee)
            $fee = $request->input('admin_fee', 0);
            if ($fee > 0) {
                $user = User::find($withdrawal->user_id);
                if ($user->saldo < $fee) {
                    // Rollback saldo admin dulu kalau gagal
                    $admin->saldo -= $withdrawal->amount;
                    $admin->save();
                    return response()->json(['message' => 'Gagal! Saldo user kurang untuk biaya admin.'], 400);
                }

                $user->saldo -= $fee;
                $user->save();

                // Catat fee user
                $this->recordMutation($user, $fee, 'debit', 'admin_fee', 'Biaya Admin Penarikan');

                // Fee juga masuk ke Admin (Opsional, biasanya jadi profit)
                $admin->saldo += $fee;
                $admin->save();
                $this->recordMutation($admin, $fee, 'credit', 'admin_fee_income', 'Pendapatan Biaya Admin');

                $withdrawal->admin_fee = $fee;
            }
        }

        $withdrawal->status = 'approved';
        $withdrawal->save();

        return response()->json([
            'message' => 'Penarikan disetujui. Saldo kembali ke Admin.',
            'sisa_saldo_admin' => $admin->saldo
        ]);
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
        if (!in_array($request->user()->role, ['pusat', 'developer'])) {
            return response()->json([
                'message' => 'Akses Ditolak. Hanya Admin Pusat atau Developer yang boleh mengubah data identitas user.'
            ], 403);
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

    // 12. ADMIN GANTI PIN SENDIRI
    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|string|size:6',
            'new_pin' => 'required|string|size:6|confirmed', // butuh new_pin_confirmation
        ]);

        $admin = $request->user(); // Admin yang login

        // Cek PIN Lama
        if ($admin->pin !== $request->current_pin) {
            return response()->json(['message' => 'PIN lama salah!'], 400);
        }

        // Simpan PIN Baru
        $admin->pin = $request->new_pin;
        $admin->save();

        return response()->json(['message' => 'PIN Admin berhasil diperbarui']);
    }

    // 13. CARI USER (GLOBAL SEARCH)
    public function searchUsers(Request $request)
    {
        // Pastikan Admin
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $keyword = $request->query('search');

        if (!$keyword) {
            return response()->json(['data' => []]);
        }

        $users = User::where('nama_lengkap', 'like', "%{$keyword}%")
            ->orWhere('username', 'like', "%{$keyword}%")
            ->orWhere('email', 'like', "%{$keyword}%")
            ->orWhere('member_id', 'like', "%{$keyword}%")
            ->orWhere('no_hp', 'like', "%{$keyword}%")
            ->limit(20) // Batasi 20 hasil biar gak berat
            ->get();

        return response()->json(['data' => $users]);
    }

    // 14. API UNTUK MENGAMBIL DAFTAR KASIR (Buat Dropdown)
    public function getCashiers()
    {
        $cashiers = Admin::where('role', 'kasir') // Pakai 'where' untuk satu nilai spesifik
            ->select('id', 'username as nama_admin') // Alias sudah benar
            ->get();

        return response()->json($cashiers);
    }

    // 15. AUTOCOMPLETE USERNAME
    public function webSearchUser(Request $request)
    {
        $query = $request->get('q'); // Sesuaikan dengan JS kamu (?q=...)

        // Cek biar gak error kalau kosong
        if (!$query) {
            return response()->json([]);
        }

        $users = User::query()
            ->where(function ($q) use ($query) {
                $q->where('username', 'LIKE', "%{$query}%")
                    // Pastikan nama kolom di database kamu benar 'nama_lengkap' ya
                    ->orWhere('nama_lengkap', 'LIKE', "%{$query}%");
            })
            ->select('id', 'username', 'nama_lengkap')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    // API untuk mengisi Dropdown di HP Dreamland
    public function getCashierList()
    {
        // Ambil ID dan Nama saja, jangan bawa PIN/Password
        $cashiers = Admin::where('role', 'kasir')
            ->select('id', 'nama_lengkap', 'username')
            ->get();

        return response()->json($cashiers);
    }
}