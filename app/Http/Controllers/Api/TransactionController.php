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

                            $adminKasir = \App\Models\Admin::where('role', 'kasir')->first();
                            if ($adminKasir) {
                                $adminKasir->saldo += $totalJatahKasir;
                                $adminKasir->save();
                            }

                            $adminDev = \App\Models\Admin::where('role', 'developer')->first();
                            if ($adminDev) {
                                $adminDev->saldo += $totalJatahDev;
                                $adminDev->save();
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

            $transaction = Transaction::create([
                'transaction_code' => $qrCode,
                'status' => 'pending',
                'user_id' => null,
                'total_bayar' => 0,
                'tanggal_transaksi' => now(),
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
                throw new \Exception("QR Code kadaluarsa atau sudah dibayar!");
            if ($user->saldo < $transaction->total_bayar)
                throw new \Exception("Saldo tidak cukup!");

            $user->saldo -= $transaction->total_bayar;
            $user->save();

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

                        $adminKasir = \App\Models\Admin::where('role', 'kasir')->first();
                        if ($adminKasir) {
                            $adminKasir->saldo += $totalJatahKasir;
                            $adminKasir->save();
                        }

                        $adminDev = \App\Models\Admin::where('role', 'developer')->first();
                        if ($adminDev) {
                            $adminDev->saldo += $totalJatahDev;
                            $adminDev->save();
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
            if ($user->saldo < $transaction->total_bayar)
                throw new \Exception("Saldo tidak cukup!");

            $user->saldo -= $transaction->total_bayar;
            $user->save();

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

                        $adminKasir = \App\Models\Admin::where('role', 'kasir')->first();
                        if ($adminKasir) {
                            $adminKasir->saldo += $totalJatahKasir;
                            $adminKasir->save();
                        }

                        $adminDev = \App\Models\Admin::where('role', 'developer')->first();
                        if ($adminDev) {
                            $adminDev->saldo += $totalJatahDev;
                            $adminDev->save();
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
}