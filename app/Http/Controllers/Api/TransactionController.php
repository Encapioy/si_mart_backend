<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\NotificationService;
use App\Models\Admin;

class TransactionController extends Controller
{
    // ==========================================
    // METODE 1: KASIR SCAN KARTU MURID (LAMA)
    // ==========================================
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity_code' => 'required|string',
            'pin' => 'required|string|size:6',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $user = User::where('member_id', $request->identity_code)
            ->orWhere('nfc_id', $request->identity_code)
            ->orWhere('username', $request->identity_code)
            ->first();

        if (!$user)
            return response()->json(['message' => 'User tidak ditemukan!'], 404);
        if ($user->pin !== $request->pin)
            return response()->json(['message' => 'PIN Salah!'], 401);

        try {
            return DB::transaction(function () use ($request, $user) {
                $total_bayar = 0;
                $trxCode = 'TRX-DIRECT-' . strtoupper(Str::random(8));

                $transaction = Transaction::create([
                    'transaction_code' => $trxCode,
                    'user_id' => $user->id,
                    'total_bayar' => 0,
                    'status' => 'paid',
                    'tanggal_transaksi' => now(),
                ]);

                foreach ($request->items as $item) {
                    $product = Product::with('seller')->lockForUpdate()->find($item['product_id']);

                    if ($product->stok < $item['qty'])
                        throw new \Exception("Stok {$product->nama_produk} habis!");

                    // Hitung Harga Akhir (Sudah termasuk Diskon & Margin 15% Simart jika ada)
                    $hargaAkhir = $product->harga_akhir;
                    $subtotal = $hargaAkhir * $item['qty'];
                    $total_bayar += $subtotal;

                    // --- LOGIKA BAGI HASIL (UPDATE 15%) ---
                    if ($product->seller) {

                        $pendapatanBersih = 0;

                        if ($product->store_id == null) {
                            // KASUS SIMART (Titipan): Potongan 15%
                            $totalPotongan = $subtotal * 0.15;

                            // Jatah Developer (100 per qty)
                            $totalJatahDev = 100 * $item['qty'];

                            // Jatah Admin Kasir (Sisa Potongan)
                            $totalJatahKasir = max(0, $totalPotongan - $totalJatahDev);

                            // Jatah Merchant (Sisanya)
                            $totalJatahMerchant = $subtotal - $totalPotongan;

                            // Transfer
                            $product->seller->saldo += $totalJatahMerchant;
                            $product->seller->save();

                            $this->recordMutation($product->seller, $totalJatahMerchant, 'credit', 'sale', 'Penjualan: ' . $product->nama_produk);

                            $adminKasir = Admin::where('role', 'kasir')->first();
                            if ($adminKasir) {
                                $adminKasir->saldo += $totalJatahKasir;
                                $adminKasir->save();

                                $this->recordMutation($adminKasir, $totalJatahKasir, 'credit', 'sale_profit', 'Profit Simart: ' . $product->nama_produk);
                            }

                            $adminDev = Admin::where('role', 'developer')->first();
                            if ($adminDev) {
                                $adminDev->saldo += $totalJatahDev;
                                $adminDev->save();

                                $this->recordMutation($adminDev, $totalJatahDev, 'credit', 'sale_tax', 'Tax Developer: ' . $product->nama_produk);
                            }
                        } else {
                            // KASUS TOKO SENDIRI: Full buat Merchant
                            $product->seller->saldo += $subtotal;
                            $product->seller->save();
                        }

                        // ==========================================
                        // 1. NOTIFIKASI KE PENJUAL (Per Produk)
                        // ==========================================
                        NotificationService::send(
                            $product->seller->id,
                            'Produk Terjual',
                            "{$item['qty']}x {$product->nama_produk} terjual. Pendapatan bersih: Rp " . number_format($pendapatanBersih, 0, ',', '.'),
                            'transaction',
                            [
                                'product_id' => $product->id,
                                'qty' => $item['qty'],
                                'income' => $pendapatanBersih
                            ]
                        );
                    }

                    // Kurangi Stok
                    $product->stok -= $item['qty'];
                    $product->save();

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'qty' => $item['qty'],
                        'harga_saat_itu' => $hargaAkhir
                    ]);
                }

                if ($user->saldo < $total_bayar)
                    throw new \Exception("Saldo tidak cukup!");

                if ($user->saldo < 0)
                    throw new \Exception("Akun Anda memiliki tunggakan (Saldo Minus). Silakan Top Up untuk melunasi.");



                $user->saldo -= $total_bayar;
                $user->save();

                $this->recordMutation($user, $transaction->total_bayar, 'debit', 'purchase', 'Pembayaran Belanja');

                $transaction->total_bayar = $total_bayar;
                $transaction->save();

                // ==========================================
                // 2. NOTIFIKASI KE PEMBELI (User)
                // ==========================================
                NotificationService::send(
                    $user->id,
                    'Pembayaran Berhasil',
                    'Pembayaran belanja sebesar Rp ' . number_format($total_bayar, 0, ',', '.') . ' berhasil.',
                    'transaction',
                    [
                        'transaction_id' => $transaction->id,
                        'total_bayar' => $total_bayar,
                        'trx_code' => $trxCode
                    ]
                );

                return response()->json([
                    'message' => 'Transaksi Berhasil (Metode Scan Kartu)!',
                    'total_bayar' => $total_bayar,
                    'sisa_saldo' => $user->saldo
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // ==========================================
    // 2. GENERATE QR (Oleh Kasir)
    // ==========================================
    public function createQrForCashier(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $total_bayar = 0;
            $qrCode = 'TRX-QR-' . strtoupper(Str::random(8));
            $waktuExpired = now()->addMinutes(5);

            // [PERBAIKAN]: Simpan ID Kasir yang membuat QR ini
            $kasirId = $request->user()->id;

            $transaction = Transaction::create([
                'transaction_code' => $qrCode,
                'status' => 'pending',
                'user_id' => null,
                'admin_id' => $kasirId, // <--- PENTING: Biar tau siapa yang dapet komisi
                'total_bayar' => 0,
                'tanggal_transaksi' => now(),
                'expired_at' => $waktuExpired,
                'type' => 'purchase', // Tandai ini belanja toko
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']); // Lock biar aman

                if ($product->stok < $item['qty'])
                    throw new \Exception("Stok {$product->nama_produk} kurang!");

                $hargaAkhir = $product->harga_akhir;
                $subtotal = $hargaAkhir * $item['qty'];
                $total_bayar += $subtotal;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'harga_saat_itu' => $hargaAkhir
                ]);
            }

            $transaction->total_bayar = $total_bayar;
            $transaction->save();

            return response()->json([
                'message' => 'QR Code Siap Scan',
                'qr_code_value' => $qrCode,
                'total_bayar' => $total_bayar
            ]);
        });
    }

    // ==========================================
    // 2. USER SCAN QR (Bayar Pakai HP)
    // ==========================================
    public function payByQr(Request $request)
    {
        $request->validate([
            'qr_code_value' => 'required|string|exists:transactions,transaction_code',
            'pin' => 'required|string|size:6',
        ]);

        $user = $request->user();
        if ($user->pin !== $request->pin)
            return response()->json(['message' => 'PIN Salah'], 401);

        try {
            return DB::transaction(function () use ($request, $user) {
                // Panggil logika inti
                return $this->processTransaction($request->qr_code_value, $user, 'QR App');
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // ==========================================
    // 3. BAYAR PAKAI KARTU (Di Kiosk/Kasir)
    // ==========================================
    public function payByCardOnKiosk(Request $request)
    {
        $request->validate([
            'transaction_code' => 'required|exists:transactions,transaction_code',
            'identity_code' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        // Cari user fleksibel
        $user = User::where('member_id', $request->identity_code)
            ->orWhere('nfc_id', $request->identity_code)
            ->orWhere('username', $request->identity_code)
            ->first();

        if (!$user)
            return response()->json(['message' => 'User tidak ditemukan!'], 404);
        if ($user->pin !== $request->pin)
            return response()->json(['message' => 'PIN Salah'], 401);

        try {
            return DB::transaction(function () use ($request, $user) {
                // Panggil logika inti yang sama
                return $this->processTransaction($request->transaction_code, $user, 'NFC Kiosk');
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // ==========================================
    // PRIVATE: CORE LOGIC (JANTUNGNYA)
    // ==========================================
    private function processTransaction($code, $user, $method)
    {
        // 1. Ambil & Kunci Transaksi
        $transaction = Transaction::where('transaction_code', $code)
            ->where('status', 'pending')
            ->lockForUpdate()
            ->first();

        if (!$transaction)
            throw new \Exception("Transaksi tidak valid atau sudah dibayar!");

        if (now() > $transaction->expired_at) {
            $transaction->status = 'cancelled';
            $transaction->save();
            throw new \Exception("Kode Pembayaran sudah kadaluarsa!");
        }

        // 2. Cek Saldo User
        if ($user->saldo < $transaction->total_bayar)
            throw new \Exception("Saldo tidak cukup!");
        if ($user->saldo < 0)
            throw new \Exception("Akun Anda memiliki tunggakan.");

        // 3. Potong Saldo User
        $user->saldo -= $transaction->total_bayar;
        $user->save();

        // 4. Catat Mutasi User (Pembayaran)
        // Pastikan function recordMutation ada (atau copy dari helper kamu)
        $this->recordMutation($user->id, $transaction->total_bayar, 'debit', 'purchase', "Belanja via $method");

        // 5. Proses Item, Stok & Bagi Hasil
        $items = TransactionItem::with('product.seller')->where('transaction_id', $transaction->id)->get();

        // --- AMBIL AKUN PENAMPUNG PROFIT (ROLE SIMART) ---
        $adminSimart = Admin::where('role', 'simart')->first();

        // --- AMBIL AKUN DEVELOPER (UNTUK PAJAK) ---
        $adminDev = Admin::where('role', 'developer')->first();

        foreach ($items as $item) {
            $product = $item->product;

            // Cek Stok
            if ($product->stok < $item->qty)
                throw new \Exception("Stok {$product->nama_produk} habis saat proses bayar!");

            $product->stok -= $item->qty;
            $product->save();

            // --- LOGIKA BAGI HASIL ---
            $subtotal = $item->harga_saat_itu * $item->qty;

            if ($product->seller) {
                // LOGIKA 1: BARANG TITIPAN (STORE ID NULL)
                if ($product->store_id == null) {

                    // A. Hitung Potongan
                    $totalPotongan = $subtotal * 0.15; // 15% dari harga jual diambil sistem

                    // B. Hitung Jatah Developer (Pajak Aplikasi)
                    // Misal: Rp 100 per pcs
                    $totalJatahDev = 100 * $item->qty;

                    // C. Hitung Jatah Toko Simart (Sisa potongan dikurangi jatah dev)
                    // Contoh: Potongan 1500. Dev 100. Simart dapat 1400.
                    $totalJatahSimart = max(0, $totalPotongan - $totalJatahDev);

                    // D. Hitung Jatah Pemilik Barang (Sisa 85%)
                    $totalJatahMerchant = $subtotal - $totalPotongan;

                    // --- EKSEKUSI DISTRIBUSI DANA ---

                    // 1. Ke Pemilik Barang (Masuk Merchant Balance)
                    $product->seller->merchant_balance += $totalJatahMerchant;
                    $product->seller->save();
                    // $this->recordMutation($product->seller->id, $totalJatahMerchant, 'credit', 'sale', 'Penjualan: ' . $product->nama_produk);

                    // 2. Ke Manager Toko Simart (Profit Perusahaan)
                    if ($adminSimart) {
                        $adminSimart->saldo += $totalJatahSimart;
                        $adminSimart->save();
                        // $this->recordMutation($adminSimart->id, $totalJatahSimart, 'credit', 'sale_profit', 'Profit Toko: ' . $product->nama_produk);
                    }

                    // 3. Ke Developer (Pajak)
                    if ($adminDev) {
                        $adminDev->saldo += $totalJatahDev;
                        $adminDev->save();
                        // $this->recordMutation($adminDev->id, $totalJatahDev, 'credit', 'sale_tax', 'Tax App: ' . $product->nama_produk);
                    }

                } else {
                    // LOGIKA 2: OFFICIAL STORE / TOKO SENDIRI
                    // Asumsi: Tidak kena potongan 15%, masuk semua ke Merchant Balance
                    $product->seller->merchant_balance += $subtotal;
                    $product->seller->save();
                    // $this->recordMutation($product->seller->id, $subtotal, 'credit', 'sale', 'Penjualan Official: ' . $product->nama_produk);
                }
            }
        }

        // 6. Update Status Transaksi
        $transaction->status = 'paid';
        $transaction->user_id = $user->id; // Simpan siapa yang bayar
        $transaction->save();

        // 7. Kirim Notifikasi
        NotificationService::send(
            $user->id,
            'Pembayaran Berhasil',
            "Pembayaran Rp " . number_format($transaction->total_bayar) . " via $method berhasil.",
            'purchase'
        );

        return response()->json([
            'message' => "Pembayaran $method Berhasil!",
            'sisa_saldo' => $user->saldo
        ]);
    }

    /**
     * Helper protected agar sesuai dengan parent Controller
     * Parameter harus URUT dan LENGKAP sesuai definisi di Controller.php
     */
    protected function recordMutation($user, $amount, $type, $category, $description, $relatedUserId = null)
    {
        // 1. Ambil ID User
        // Jika inputnya object User, ambil ->id. Jika inputnya sudah ID (int/string), langsung pakai.
        $userId = (is_object($user)) ? $user->id : $user;

        // 2. Ambil Current Balance (Saldo Terakhir)
        // Kita query ulang saldo user agar data di mutasi akurat (real-time)
        $currentUser = User::find($userId);
        $currentBalance = $currentUser ? $currentUser->saldo : 0;

        // 3. Simpan ke Database
        \App\Models\BalanceMutation::create([
            'user_id' => $userId,
            'type' => $type,             // 'debit' atau 'credit'
            'amount' => $amount,
            'current_balance' => $currentBalance,   // Saldo setelah transaksi
            'category' => $category,         // 'purchase', 'topup', 'transfer', dll
            'description' => $description,
            'related_user_id' => $relatedUserId     // Opsional (bisa null)
        ]);
    }

    // ==========================================
    // METODE 3: PUSH PAYMENT (Kasir Scan, User PIN di HP)
    // ==========================================

    // TAHAP A: Kasir Tembak Tagihan ke User
    public function requestPaymentToUser(Request $request)
    {
        // Validasi: Butuh Identity User (QR Member) + Barang
        $request->validate([
            'identity_code' => 'required|string', // Hasil Scan Kasir
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        // 1. Cari User
        $user = User::where('member_id', $request->identity_code)
            ->orWhere('nfc_id', $request->identity_code)
            ->orWhere('username', $request->identity_code)
            ->first();

        if (!$user)
            return response()->json(['message' => 'User tidak ditemukan!'], 404);

        // 2. Buat Transaksi Pending
        return DB::transaction(function () use ($request, $user) {
            $total_bayar = 0;
            // Kode unik PUSH payment
            $trxCode = 'TRX-PUSH-' . strtoupper(Str::random(8));

            $transaction = Transaction::create([
                'transaction_code' => $trxCode,
                'status' => 'waiting_confirmation', // Status Baru!
                'user_id' => $user->id, // Kita sudah tau user-nya siapa
                'total_bayar' => 0,
                'tanggal_transaksi' => now(),
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                // Cek Stok (Booking stok dulu)
                if ($product->stok < $item['qty'])
                    throw new \Exception("Stok {$product->nama_produk} kurang!");

                $hargaAkhir = $product->harga_akhir;
                $subtotal = $hargaAkhir * $item['qty'];
                $total_bayar += $subtotal;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'harga_saat_itu' => $hargaAkhir
                ]);
            }

            $transaction->total_bayar = $total_bayar;
            $transaction->save();

            // TODO: Di sini nanti bisa pasang Kode Notifikasi (FCM) ke HP User
            // "Halo Udin, Kasir minta pembayaran Rp 15.000. Klik untuk bayar."

            return response()->json([
                'message' => 'Tagihan terkirim ke user. Menunggu PIN User...',
                'transaction_code' => $trxCode,
                'user_name' => $user->nama_lengkap
            ]);
        });
    }

    // TAHAP B: User Cek Tagihan Masuk (Polling / List)
    public function getUserPendingBills(Request $request)
    {
        $user = $request->user();

        $bills = Transaction::where('user_id', $user->id)
            ->where('status', 'waiting_confirmation')
            ->with('items.product') // Biar tau beli apa aja
            ->latest()
            ->get();

        return response()->json(['data' => $bills]);
    }

    // TAHAP C: User Konfirmasi Bayar (Input PIN di HP)
    public function confirmPaymentByUser(Request $request)
    {
        $request->validate([
            'transaction_code' => 'required|exists:transactions,transaction_code',
            'pin' => 'required|string|size:6',
        ]);

        $user = $request->user();

        // 1. Cek PIN
        if ($user->pin !== $request->pin)
            return response()->json(['message' => 'PIN Salah'], 401);

        // 2. Cari Transaksi
        $transaction = Transaction::where('transaction_code', $request->transaction_code)
            ->where('user_id', $user->id) // Pastikan punya dia sendiri
            ->where('status', 'waiting_confirmation')
            ->first();

        if (!$transaction)
            return response()->json(['message' => 'Tagihan tidak ditemukan atau sudah dibayar'], 404);

        if ($user->saldo < $transaction->total_bayar)
            return response()->json(['message' => 'Saldo tidak cukup'], 400);

        if ($user->saldo < 0)
            return response()->json(['message' => 'Akun Anda memiliki tunggakan (Saldo Minus). Silakan Top Up untuk melunasi.'], 400);

        // 3. Eksekusi Bayar
        return DB::transaction(function () use ($transaction, $user) {

            // Potong Saldo
            $user->saldo -= $transaction->total_bayar;
            $user->save();

            // Rekam Mutasi Pembeli
            $this->recordMutation($user, $transaction->total_bayar, 'debit', 'purchase', 'Pembayaran di Kasir (Push)');

            // Proses Barang & Bagi Hasil
            $items = TransactionItem::with('product.seller')->where('transaction_id', $transaction->id)->get();

            foreach ($items as $item) {
                $product = $item->product;

                // Kurangi Stok
                if ($product->stok < $item->qty)
                    throw new \Exception("Stok habis saat konfirmasi!");
                $product->stok -= $item->qty;
                $product->save();

                // Hitung Subtotal
                $subtotal = $item->harga_saat_itu * $item->qty;

                // Transfer Uang (Logic 15%)
                if ($product->seller) {
                    if ($product->store_id == null) {
                        // Simart
                        $totalPotongan = $subtotal * 0.15;
                        $totalJatahDev = 100 * $item->qty;
                        $totalJatahKasir = max(0, $totalPotongan - $totalJatahDev);
                        $totalJatahMerchant = $subtotal - $totalPotongan;

                        $product->seller->saldo += $totalJatahMerchant;
                        $product->seller->save();
                        $this->recordMutation($product->seller, $totalJatahMerchant, 'credit', 'sale', 'Penjualan: ' . $product->nama_produk);

                        $adminKasir = Admin::where('role', 'kasir')->first();
                        if ($adminKasir) {
                            $adminKasir->saldo += $totalJatahKasir;
                            $adminKasir->save();
                            $this->recordMutation($adminKasir, $totalJatahKasir, 'credit', 'sale_profit', 'Profit: ' . $product->nama_produk);
                        }

                        $adminDev = Admin::where('role', 'developer')->first();
                        if ($adminDev) {
                            $adminDev->saldo += $totalJatahDev;
                            $adminDev->save();
                            $this->recordMutation($adminDev, $totalJatahDev, 'credit', 'sale_tax', 'Tax: ' . $product->nama_produk);
                        }
                    } else {
                        // Toko Sendiri
                        $product->seller->saldo += $subtotal;
                        $product->seller->save();
                        $this->recordMutation($product->seller, $subtotal, 'credit', 'sale', 'Penjualan: ' . $product->nama_produk);
                    }
                }
            }

            // Update Status
            $transaction->status = 'paid';
            $transaction->save();

            return response()->json([
                'message' => 'Pembayaran Berhasil!',
                'sisa_saldo' => $user->saldo
            ]);
        });
    }

    // 2. LIHAT RIWAYAT TRANSAKSI
    public function history(Request $request)
    {
        $user = $request->user();
        if ($user->parent_id == null) {
            $my_family_ids = $user->children()->pluck('id')->toArray();
            $my_family_ids[] = $user->id;
            $transactions = Transaction::whereIn('user_id', $my_family_ids)
                ->with(['items.product', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $transactions = Transaction::where('user_id', $user->id)
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->get();
        }
        return response()->json([
            'message' => 'Riwayat Transaksi',
            'data' => $transactions
        ]);
    }

    // 3. RIWAYAT PENJUALAN MERCHANT
    public function salesHistory(Request $request)
    {
        $user = $request->user();
        $batasWaktu = now()->subDays(7);

        $terjual = TransactionItem::whereHas('product', function ($query) use ($user) {
            $query->where('seller_id', $user->id)
                ->where('seller_type', get_class($user));
        })
            ->where('created_at', '>=', $batasWaktu)
            ->with(['product', 'transaction.user'])
            ->latest()
            ->get();

        $totalPendapatanMingguan = $terjual->sum(function ($item) {
            return $item->qty * $item->harga_saat_itu;
        });

        return response()->json([
            'message' => 'Laporan Penjualan Merchant (7 Hari Terakhir)',
            'data' => [
                'saldo_dompet_saat_ini' => $user->saldo,
                'omzet_7_hari' => $totalPendapatanMingguan,
                'riwayat_item' => $terjual
            ]
        ]);
    }

    // 4. LIHAT DETAIL TRANSAKSI (STRUK)
    public function getTransactionDetail(Request $request, $code)
    {
        $user = $request->user();

        // Cari transaksi berdasarkan CODE atau ID
        // Pastikan transaksi itu milik user yang sedang login (keamanan)
        $transaction = Transaction::where('transaction_code', $code)
            ->where('user_id', $user->id)
            ->with(['items.product']) // Load barang-barangnya
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail Transaksi',
            'data' => $transaction
        ]);
    }

    // 5. BAYAR KE MERCHANT DENGAN QR
    public function payMerchantQr(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'merchant_id' => 'required|exists:merchants,id',
            'amount' => 'required|numeric|min:500', // Minimal bayar 500 perak
            'pin' => 'required|digits:6', // Wajib PIN biar aman
        ]);

        $user = auth()->user();
        $amount = $request->amount;

        // 2. Cek PIN User
        // (Asumsi kamu pake hash buat pin, sesuaikan logic validasimu)
        if (!Hash::check($request->pin, $user->pin)) {
            return response()->json(['message' => 'PIN Salah!'], 401);
        }

        // 3. Cek Saldo User
        if ($user->saldo < $amount) {

            // LOGIC TAMBAHAN: Cek apakah minus?
            if ($user->saldo < 0) {
                return response()->json([
                    'message' => 'Akun Anda memiliki tunggakan (Saldo Minus). Silakan Top Up untuk melunasi.'
                ], 400);
            }

            return response()->json(['message' => 'Saldo tidak cukup!'], 400);
        }

        // 4. Mulai Transaksi Database (Atomic)
        DB::beginTransaction();
        try {
            $merchant = Merchant::findOrFail($request->merchant_id);

            // A. Potong Saldo User
            $user->saldo -= $amount;
            $user->save();

            // B. Tambah Saldo Merchant (Masuk ke Wallet Toko, bukan Wallet Pribadi User)
            $merchant->saldo += $amount;
            $merchant->save();

            // C. Catat Riwayat Transaksi
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'type' => 'PAYMENT', // Tipe transaksi
                'amount' => $amount,
                'status' => 'paid',
                'description' => 'Pembayaran ke ' . $merchant->shop_name,
                'reference_id' => 'TRX-' . time() . rand(100, 999)
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran Berhasil!',
                'data' => $transaction
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Transaksi Gagal: ' . $e->getMessage()], 500);
        }
    }

    // 6. IDENTIFICATION PAYMENT
    public function checkStoreQr(Request $request)
    {
        // 1. Ambil QR
        $qrPayload = $request->input('qr_payload');

        // 2. Validasi Format
        if (!$qrPayload || !str_starts_with($qrPayload, 'SIPAY:STORE:')) {
            return response()->json(['message' => 'QR Code tidak valid'], 400);
        }

        try {
            // 3. Pecah Data
            $parts = explode(':', $qrPayload);
            if (count($parts) < 3)
                throw new \Exception("Struktur QR rusak");

            $storeId = $parts[2];

            // 4. Cari Toko (PENTING: Ganti 'user' jadi 'owner')
            $store = Store::with('owner')->find($storeId);

            if (!$store) {
                return response()->json(['message' => 'Toko tidak ditemukan'], 404);
            }

            // 5. Ambil Nama Merchant (PENTING: Panggil lewat relation 'owner')
            // Kita pakai null check (?) jaga-jaga kalau usernya udah dihapus admin
            $merchantName = $store->owner ? $store->owner->nama_lengkap : 'Merchant Tidak Diketahui';

            // 6. Handle Gambar
            $imageUrl = $store->gambar
                ? asset('storage/' . $store->gambar)
                : 'https://ui-avatars.com/api/?name=' . urlencode($store->nama_toko) . '&background=random';

            return response()->json([
                'status' => 'success',
                'message' => 'Toko ditemukan',
                'data' => [
                    'store_id' => $store->id,
                    'store_name' => $store->nama_toko,  // Sesuai DB
                    'category' => $store->kategori,   // Sesuai DB
                    'description' => $store->deskripsi,  // Sesuai DB
                    'location' => $store->lokasi,     // Sesuai DB
                    'is_open' => $store->is_open,    // Sesuai DB

                    'merchant_name' => $merchantName,      // <-- INI YANG SUDAH DIBENERIN
                    'store_image' => $imageUrl,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // 7. BAYAR KE TOKO MERCHANT DENGAN QR
    public function payStoreQr(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'amount' => 'required|numeric|min:500',
            'pin' => 'required|string',
        ]);

        $user = $request->user();


        // 2. CEK PIN
        if ($request->pin != $user->pin) {
            return response()->json(['message' => 'PIN Salah!'], 401);
        }

        // 3. CEK SALDO
        if ($user->saldo < $request->amount) {

            // LOGIC TAMBAHAN: Cek apakah minus?
            if ($user->saldo < 0) {
                return response()->json([
                    'message' => 'Akun Anda memiliki tunggakan (Saldo Minus). Silakan Top Up untuk melunasi.'
                ], 400);
            }

            return response()->json(['message' => 'Saldo tidak cukup!'], 400);
        }

        // Cari Toko & Pemilik
        $store = Store::find($request->store_id);

        if ($store->user_id == $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak bisa melakukan pembayaran ke toko sendiri'
            ], 403);
        }

        $merchantUser = User::find($store->user_id);

        if (!$merchantUser) {
            return response()->json(['message' => 'Merchant toko ini tidak valid'], 404);
        }

        // 4. Proses Transaksi
        \DB::beginTransaction();
        try {
            // A. Kurangi Saldo Pembeli
            $user->saldo -= $request->amount;
            $user->save();

            // B. Tambah Saldo Merchant
            $merchantUser->merchant_balance += $request->amount;
            $merchantUser->save();

            // C. Catat Struk (Tabel Transactions - Untuk Laporan Admin)
            $trx = Transaction::create([
                'transaction_code' => 'TRX-' . time() . rand(100, 999),
                'user_id' => $user->id,
                'store_id' => $store->id,
                'total_bayar' => $request->amount,
                'status' => 'paid',
                'type' => 'payment', // Tipe Payment
                'description' => 'Pembayaran ke ' . $store->nama_toko,
                'tanggal_transaksi' => now(),
            ]);

            // --- TAMBAHAN BARU: BALANCE MUTATION ---

            // D. Catat Mutasi Pembeli (Uang Keluar / Debit)
            \App\Models\BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'debit', // Merah
                'amount' => $request->amount,
                'current_balance' => $user->saldo, // Saldo setelah dipotong
                'category' => 'payment',
                'description' => 'Pembayaran ke ' . $store->nama_toko,
                'related_user_id' => $merchantUser->id
            ]);

            // E. Catat Mutasi Penjual (Uang Masuk / Credit)
            \App\Models\BalanceMutation::create([
                'user_id' => $merchantUser->id,
                'type' => 'credit', // Hijau
                'amount' => $request->amount,
                'current_balance' => $merchantUser->merchant_balance, // Saldo setelah ditambah
                'category' => 'payment',
                'description' => 'Pembayaran diterima dari ' . $user->nama_lengkap,
                'related_user_id' => $user->id
            ]);

            // ---------------------------------------

            \DB::commit();

            // Cek apakah yang minta data adalah API/Mobile App (Ingin JSON)?
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Pembayaran berhasil ke ' . $store->nama_toko,
                    'data' => $trx,
                    'sisa_saldo' => $user->saldo
                ]);
            }

            // Jika bukan API (berarti Web Browser/Livewire), maka REDIRECT ke halaman struk
            return redirect()->route('payment.success', ['code' => $trx->transaction_code]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Transaksi gagal: ' . $e->getMessage()], 500);
        }
    }


}