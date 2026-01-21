<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\InformationController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\PreOrderController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\MerchantDashboardController;
use App\Http\Controllers\Api\Admin\MerchantVerificationController;
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CorrectionController;
use App\Http\Controllers\Api\AdvertisementController;

/*
|--------------------------------------------------------------------------
| API Routes - SI MART ERP SYSTEM (CLEANED)
|--------------------------------------------------------------------------
*/

// =================================================================
// GROUP 1: PUBLIC / AUTHENTICATION
// =================================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login/verify-device', [AuthController::class, 'verifyNewDevice']);
Route::post('/check-availability', [AuthController::class, 'checkAvailability']); // Cek email/username
Route::get('/informations', [InformationController::class, 'index']); // Berita/Info Publik

// Lupa Password & PIN
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);       // Kirim OTP ke Email
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']); // Reset Password
Route::post('/auth/reset-pin', [AuthController::class, 'resetPin']);           // Reset PIN

// =================================================================
// GROUP 2: PROTECTED ROUTES (Butuh Token)
// =================================================================
Route::middleware('auth:sanctum')->group(function () {

    // CEK TOKEN
    Route::get('/auth/check-token', function () {
        return response()->json(['status' => 'success', 'is_valid' => true, 'user' => auth()->user()]);
    });

    // --- A. USER PROFILE & SECURITY ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/users/profile', [UserController::class, 'updateProfile']);
    Route::post('/users/change-password', [UserController::class, 'changePassword']);
    Route::post('/users/change-pin', [UserController::class, 'changePin']);
    Route::post('/users/validate-pin', [UserController::class, 'validatePin']);
    Route::post('/users/lookup', [UserController::class, 'getUserPublicInfo']); // Cari user lain
    Route::get('/history', [HistoryController::class, 'index']); // History umum

    // Route Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // --- B. DASHBOARD & FAMILY ---
    Route::get('/home', [UserDashboardController::class, 'home']);
    Route::get('/infos', [UserDashboardController::class, 'infos']);
    Route::get('/summary', [UserDashboardController::class, 'getUserSummary']);
    Route::post('/users/pair', [UserController::class, 'pairChild']);
    Route::get('/users/children', [UserController::class, 'getMyChildren']);
    Route::post('/users/sync-contacts', [UserController::class, 'syncContacts']);
    Route::get('/ads', [AdvertisementController::class, 'index']);         // Lihat semua iklan aktif

    // --- C. MERCHANT REGISTRATION FLOW (Daftar Jadi Pedagang) ---
    Route::post('/users/verify-data', [UserController::class, 'uploadVerification']); // Upload KTP/KTM
    Route::post('/merchant/register', [MerchantController::class, 'register']); // Submit Form Daftar Merchant
    Route::get('/merchant/status', [MerchantController::class, 'checkStatus']); // Cek diacc/tidak
    Route::put('/merchant/update', [MerchantController::class, 'update']); // Revisi data pendaftaran jika ditolak

    // --- D. STORE MANAGEMENT (Operasional Toko - Setelah Jadi Merchant) ---
    Route::post('/stores', [StoreController::class, 'store']); // Buka Toko Baru
    Route::get('/stores/my', [StoreController::class, 'myStores']); // List Toko Saya
    Route::get('/stores/{id}/dashboard', [StoreController::class, 'myStoreDetail']); // Dashboard Toko
    Route::post('/stores/{id}/update', [StoreController::class, 'update']); // Update toko
    Route::get('/stores/{id}/income-report', [StoreController::class, 'getIncomeReport']); // Laporan pemasukan toko
    Route::get('/stores/{id}/qr', [StoreController::class, 'generateQrCode']); // Generate Qr toko

    Route::get('/merchant/qr', [MerchantController::class, 'generateQrCode']); // QR Toko
    Route::get('/merchant/income', [MerchantDashboardController::class, 'getIncomeStats']); // Total pendapatan
    Route::get('/merchant/dashboard/summary', [MerchantDashboardController::class, 'getDailySummary']); // Statistik Harian
    Route::get('/merchant/dashboard/financial', [MerchantDashboardController::class, 'getFinancialDashboard']); // Rekapitulasi pendapatan

    // --- E. PUBLIC STORE VIEW (User lihat Kantin) ---
    Route::get('/stores', [StoreController::class, 'index']);
    Route::get('/stores/{id}', [StoreController::class, 'show']);

    // --- F. PRODUCT MANAGEMENT ---
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/my-grouped', [ProductController::class, 'myGroupedProducts']); // Produk per kategori merchant
    Route::get('/products/{barcode}', [ProductController::class, 'getByBarcode']);
    Route::post('/products', [ProductController::class, 'store']); // Tambah Produk
    Route::put('/products/{id}', [ProductController::class, 'update']); // Edit Produk
    Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Hapus Produk

    // --- FITUR IKLAN (MERCHANT) ---
    Route::post('/ads', [AdvertisementController::class, 'store']);        // Pasang Iklan
    Route::post('/ads/{id}/renew', [AdvertisementController::class, 'renew']); // Perpanjang
    Route::get('/my-ads/history', [AdvertisementController::class, 'myAdsHistory']); // Cek semua iklan miliknya
    Route::get('/my-ads/active', [AdvertisementController::class, 'myActiveAds']); // Cek iklan aktif miliknya

    // --- G. TRANSACTIONS (Kasir & Pembayaran) ---
    Route::post('/checkout', [TransactionController::class, 'checkout']); // User bayar keranjang
    Route::get('/transactions', [TransactionController::class, 'history']);
    Route::get('/transactions/{code}', [TransactionController::class, 'getTransactionDetail']);
    Route::post('/transactions/confirm-payment', [TransactionController::class, 'confirmPaymentByUser']); // User konfirmasi

    // Fitur Kasir Merchant
    Route::post('/transactions/request-payment', [TransactionController::class, 'requestPaymentToUser']); // Tagih user
    Route::get('/transactions/pending-bills', [TransactionController::class, 'getUserPendingBills']); // Tagihan user
    Route::get('/merchant/sales', [TransactionController::class, 'salesHistory']); // Riwayat Penjualan

    // QR & Kiosk
    Route::post('/transactions/pay-kiosk', [TransactionController::class, 'payByQr']);
    Route::post('/transactions/pay-merchant', [TransactionController::class, 'payMerchantQr']);
    Route::post('/transactions/pay-store', [TransactionController::class, 'payStoreQr']);
    Route::post('/kiosk/check-balance', [UserController::class, 'checkBalance']);
    Route::post('/scan/check-store', [TransactionController::class, 'checkStoreQr']); // Cek QR

    // --- H. PRE-ORDER & FAVORITE ---
    Route::post('/po/checkout', [PreOrderController::class, 'store']);
    Route::get('/po/my-orders', [PreOrderController::class, 'myOrders']);
    Route::post('/po/{id}/cancel', [PreOrderController::class, 'cancel']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'myFavorites']);

    // --- I. WALLET / BALANCE ---
    Route::post('/balance/request-topup', [BalanceController::class, 'requestTopUp']);
    Route::post('/balance/withdraw', [BalanceController::class, 'withdraw']);
    Route::post('/balance/transfer', [BalanceController::class, 'transfer']);
    Route::get('/balance/recent-transfers', [BalanceController::class, 'getRecentTransfers']);

    // =============================================================
    // GROUP 3: ADMIN DASHBOARD (PUSAT)
    // =============================================================
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'getDashboardStats']);
        Route::post('/change-pin', [AdminController::class, 'changePin']);
        Route::get('/users', [AdminController::class, 'searchUsers']);
        Route::post('/fix-member-ids', [AdminController::class, 'generateOldMemberIds']);
        Route::get('/cashiers', [AdminController::class, 'getCashierList']);

        // Verifikasi User
        Route::get('/verifications', [AdminController::class, 'getPendingUsers']);
        Route::put('/verifications/{id}/approve', [AdminController::class, 'approveUser']);
        Route::put('/verifications/{id}/reject', [AdminController::class, 'rejectUser']);
        Route::put('/users/{id}/reset', [AdminController::class, 'resetUserAccess']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);

        // Verifikasi Merchant (Baru)
        Route::get('/merchants/pending', [MerchantVerificationController::class, 'listPending']);
        Route::get('/merchants/{id}', [MerchantVerificationController::class, 'show']);
        Route::post('/merchants/{id}/approve', [MerchantVerificationController::class, 'approve']);
        Route::post('/merchants/{id}/reject', [MerchantVerificationController::class, 'reject']);

        // Keuangan
        Route::get('/topups', [AdminController::class, 'getPendingTopUps']);
        Route::put('/topups/{id}/approve', [AdminController::class, 'approveTopUp']);
        Route::post('/balance/topup-manual', [AdminController::class, 'manualTopUp']);
        Route::get('/withdrawals', [AdminController::class, 'getPendingWithdrawals']);
        Route::post('/withdrawals/{id}/approve', [AdminController::class, 'approveWithdrawal']);
        Route::put('/withdrawals/{id}/reject', [AdminController::class, 'rejectWithdrawal']);
        Route::post('/correction/rollback-topup', [CorrectionController::class, 'rollbackTopUp']); // Batal topup
        Route::post('/correction/rollback-transaction', [CorrectionController::class, 'rollbackTransaction']); // Batal transaksi

        // Gudang & Info
        Route::get('/products/missing-rack', [AdminController::class, 'getProductsMissingRack']);
        Route::put('/products/{id}/rack', [AdminController::class, 'updateRackLocation']);
        Route::post('/po/take', [PreOrderController::class, 'markAsTaken']);
        Route::post('/informations', [InformationController::class, 'store']);
        Route::delete('/informations/{id}', [InformationController::class, 'destroy']);

        // Simart
        Route::post('/cashier/create-qr', [TransactionController::class, 'createQrForCashier']);
        Route::get('/cashier/check-qr/{code}', [TransactionController::class, 'showQrDetails']);
        Route::post('/transactions/pay-kiosk-card', [TransactionController::class, 'payByCardOnKiosk']);
    });
});