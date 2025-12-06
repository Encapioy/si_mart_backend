<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

                            $adminKasir = \App\Models\Admin::where('role', 'kasir')->first();
                            if ($adminKasir) {
                                $adminKasir->saldo += $totalJatahKasir;
                                $adminKasir->save();

                                $this->recordMutation($adminKasir, $totalJatahKasir, 'credit', 'sale_profit', 'Profit Simart: ' . $product->nama_produk);
                            }

                            $adminDev = \App\Models\Admin::where('role', 'developer')->first();
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

                $user->saldo -= $total_bayar;
                $user->save();

                $this->recordMutation($user, $transaction->total_bayar, 'debit', 'purchase', 'Pembayaran Belanja');

                $transaction->total_bayar = $total_bayar;
                $transaction->save();

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
    // METODE 2: MURID SCAN QR KASIR (BARU)
    // ==========================================

    // TAHAP A: Kasir Generate QR (Status Pending)
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

            // SETTING WAKTU EXPIRED (Misal: 5 Menit dari sekarang)
            $waktuExpired = now()->addMinutes(5);

            $transaction = Transaction::create([
                'transaction_code' => $qrCode,
                'status' => 'pending',
                'user_id' => null,
                'total_bayar' => 0,
                'tanggal_transaksi' => now(),
                'expired_at' => $waktuExpired,
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

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

    // TAHAP B: Murid Scan QR & Bayar
    public function payByQr(Request $request)
    {
        $request->validate([
            'qr_code_value' => 'required|string|exists:transactions,transaction_code',
            'pin' => 'required|string|size:6',
        ]);

        $user = $request->user();
        if ($user->pin !== $request->pin)
            return response()->json(['message' => 'PIN Salah'], 401);

        return DB::transaction(function () use ($request, $user) {
            $transaction = Transaction::where('transaction_code', $request->qr_code_value)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();


            if (!$transaction)
                throw new \Exception("Transaksi tidak valid atau sudah dibayar!");

            // Apakah waktu sekarang sudah melewati batas expired?
            if (now() > $transaction->expired_at) {
                // Opsional: Ubah status jadi cancelled biar gak menuhin query pending
                $transaction->status = 'cancelled';
                $transaction->save();

                throw new \Exception("QR Code sudah kadaluarsa! Silakan buat ulang transaksi.");
            }

            if ($user->saldo < $transaction->total_bayar)
                throw new \Exception("Saldo tidak cukup!");

            $user->saldo -= $transaction->total_bayar;
            $user->save();

            $this->recordMutation($user, $transaction->total_bayar, 'debit', 'purchase', 'Pembayaran Belanja');

            $items = TransactionItem::with('product.seller')->where('transaction_id', $transaction->id)->get();

            foreach ($items as $item) {
                $product = $item->product;
                if ($product->stok < $item->qty)
                    throw new \Exception("Stok {$product->nama_produk} habis saat anda mau bayar!");

                $product->stok -= $item->qty;
                $product->save();

                // --- LOGIKA BAGI HASIL (UPDATE 15%) ---
                $subtotal = $item->harga_saat_itu * $item->qty; // Definisikan Subtotal di sini

                if ($product->seller) {
                    if ($product->store_id == null) {
                        // SIMART: Potong 15%
                        $totalPotongan = $subtotal * 0.15;
                        $totalJatahDev = 100 * $item->qty;
                        $totalJatahKasir = max(0, $totalPotongan - $totalJatahDev);
                        $totalJatahMerchant = $subtotal - $totalPotongan;

                        $product->seller->saldo += $totalJatahMerchant;
                        $product->seller->save();

                        $this->recordMutation($product->seller, $totalJatahMerchant, 'credit', 'sale', 'Penjualan: ' . $product->nama_produk);

                        $adminKasir = \App\Models\Admin::where('role', 'kasir')->first();
                        if ($adminKasir) {
                            $adminKasir->saldo += $totalJatahKasir;
                            $adminKasir->save();

                            $this->recordMutation($adminKasir, $totalJatahKasir, 'credit', 'sale_profit', 'Profit Simart: ' . $product->nama_produk);
                        }

                        $adminDev = \App\Models\Admin::where('role', 'developer')->first();
                        if ($adminDev) {
                            $adminDev->saldo += $totalJatahDev;
                            $adminDev->save();

                            $this->recordMutation($adminDev, $totalJatahDev, 'credit', 'sale_tax', 'Tax Developer: ' . $product->nama_produk);
                        }
                    } else {
                        // TOKO SENDIRI
                        $product->seller->saldo += $subtotal;
                        $product->seller->save();
                    }
                }
            }

            $transaction->status = 'paid';
            $transaction->user_id = $user->id;
            $transaction->save();

            return response()->json([
                'message' => 'Pembayaran QR Berhasil!',
                'sisa_saldo' => $user->saldo
            ]);
        });
    }

    // TAHAP C: Bayar Transaksi Pending pakai KARTU di Kiosk (Hybrid)
    public function payByCardOnKiosk(Request $request)
    {
        $request->validate([
            'transaction_code' => 'required|exists:transactions,transaction_code',
            'identity_code' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        $user = User::where('member_id', $request->identity_code)
            ->orWhere('nfc_id', $request->identity_code)
            ->orWhere('username', $request->identity_code)
            ->first();

        if (!$user)
            return response()->json(['message' => 'User tidak ditemukan!'], 404);
        if ($user->pin !== $request->pin)
            return response()->json(['message' => 'PIN Salah'], 401);

        return DB::transaction(function () use ($request, $user) {
            $transaction = Transaction::where('transaction_code', $request->transaction_code)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$transaction)
                throw new \Exception("Transaksi tidak valid atau sudah dibayar!");

            // Apakah waktu sekarang sudah melewati batas expired?
            if (now() > $transaction->expired_at) {
                // Opsional: Ubah status jadi cancelled biar gak menuhin query pending
                $transaction->status = 'cancelled';
                $transaction->save();

                throw new \Exception("QR Code sudah kadaluarsa! Silakan buat ulang transaksi.");
            }

            if ($user->saldo < $transaction->total_bayar)
                throw new \Exception("Saldo tidak cukup!");

            $user->saldo -= $transaction->total_bayar;
            $user->save();

            $this->recordMutation($user, $transaction->total_bayar, 'debit', 'purchase', 'Pembayaran Belanja');

            $items = TransactionItem::with('product.seller')->where('transaction_id', $transaction->id)->get();

            foreach ($items as $item) {
                $product = $item->product;
                if ($product->stok < $item->qty)
                    throw new \Exception("Stok {$product->nama_produk} habis!");

                $product->stok -= $item->qty;
                $product->save();

                // --- LOGIKA BAGI HASIL (UPDATE 15%) ---
                $subtotal = $item->harga_saat_itu * $item->qty; // Definisikan Subtotal di sini

                if ($product->seller) {
                    if ($product->store_id == null) {
                        // SIMART: Potong 15%
                        $totalPotongan = $subtotal * 0.15;
                        $totalJatahDev = 100 * $item->qty;
                        $totalJatahKasir = max(0, $totalPotongan - $totalJatahDev);
                        $totalJatahMerchant = $subtotal - $totalPotongan;

                        $product->seller->saldo += $totalJatahMerchant;
                        $product->seller->save();

                        $this->recordMutation($product->seller, $totalJatahMerchant, 'credit', 'sale', 'Penjualan: ' . $product->nama_produk);

                        $adminKasir = \App\Models\Admin::where('role', 'kasir')->first();
                        if ($adminKasir) {
                            $adminKasir->saldo += $totalJatahKasir;
                            $adminKasir->save();

                            $this->recordMutation($adminKasir, $totalJatahKasir, 'credit', 'sale_profit', 'Profit Simart: ' . $product->nama_produk);
                        }

                        $adminDev = \App\Models\Admin::where('role', 'developer')->first();
                        if ($adminDev) {
                            $adminDev->saldo += $totalJatahDev;
                            $adminDev->save();

                            $this->recordMutation($adminDev, $totalJatahDev, 'credit', 'sale_tax', 'Tax Developer: ' . $product->nama_produk);
                        }
                    } else {
                        // TOKO SENDIRI
                        $product->seller->saldo += $subtotal;
                        $product->seller->save();
                    }
                }
            }

            $transaction->status = 'paid';
            $transaction->user_id = $user->id;
            $transaction->save();

            return response()->json([
                'message' => 'Pembayaran Kiosk Berhasil!',
                'sisa_saldo' => $user->saldo
            ]);
        });
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

                        $adminKasir = \App\Models\Admin::where('role', 'kasir')->first();
                        if ($adminKasir) {
                            $adminKasir->saldo += $totalJatahKasir;
                            $adminKasir->save();
                            $this->recordMutation($adminKasir, $totalJatahKasir, 'credit', 'sale_profit', 'Profit: ' . $product->nama_produk);
                        }

                        $adminDev = \App\Models\Admin::where('role', 'developer')->first();
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
}