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

/*
|--------------------------------------------------------------------------
| API Routes - SI MART ERP SYSTEM
|--------------------------------------------------------------------------
*/

// =================================================================
// 1. PUBLIC ROUTES (Tanpa Token)
// =================================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login/verify-device', [AuthController::class, 'verifyNewDevice']); // Tahap 2 Login
Route::get('/informations', [InformationController::class, 'index']); // List Info/Promo

// =================================================================
// 2. PROTECTED ROUTES (Butuh Token: User / Merchant / Admin)
// =================================================================

Route::middleware('auth:sanctum')->group(function () {

    // --- AUTH & PROFILE ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']); // isi profile
    Route::post('/users/change-password', [UserController::class, 'changePassword']); // ganti password
    Route::post('/users/change-pin', [UserController::class, 'changePin']); // ganti pin
    Route::post('/users/validate-pin', [UserController::class, 'validatePin']); // validasi pin
    Route::post('/users/profile', [UserController::class, 'updateProfile']); // update profile
    Route::post('/users/lookup', [UserController::class, 'getUserPublicInfo']); // tujuan transfer
    Route::get('/history', [App\Http\Controllers\Api\HistoryController::class, 'index']); // riwayat semua transaksi
    Route::get('/home', [App\Http\Controllers\Api\UserDashboardController::class, 'home']); // Tab Home
    Route::get('/infos', [App\Http\Controllers\Api\UserDashboardController::class, 'infos']); // Tab Info

    // --- FITUR KELUARGA ---
    Route::post('/users/pair', [UserController::class, 'pairChild']);
    Route::get('/users/children', [UserController::class, 'getMyChildren']);

    // --- MANAJEMEN TOKO (MERCHANT) ---
    Route::post('/users/verify-data', [UserController::class, 'uploadVerification']); // Upload KTP
    Route::post('/stores', [StoreController::class, 'store']);
    Route::get('/stores/my', [StoreController::class, 'myStores']);
    Route::put('/stores/{id}', [StoreController::class, 'update']);

    // --- PRODUK ---
    Route::get('/products', [ProductController::class, 'index']); // Support ?toko=simart&search=...
    Route::post('/products', [ProductController::class, 'store']); // Logic Margin 15% & PO
    Route::get('/products/{barcode}', [ProductController::class, 'getByBarcode']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // --- FAVORIT ---
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'myFavorites']);

    // --- TRANSAKSI (KASIR & PEMBELIAN) ---
    Route::post('/checkout', [TransactionController::class, 'checkout']); // Metode 1: Direct NFC
    Route::get('/transactions', [TransactionController::class, 'history']); // Riwayat Belanja User
    Route::post('/transactions/request-payment', [TransactionController::class, 'requestPaymentToUser']); // 1. Kasir kirim tagihan
    Route::get('/transactions/pending-bills', [TransactionController::class, 'getUserPendingBills']); // 2. User cek ada tagihan gak?
    Route::post('/transactions/confirm-payment', [TransactionController::class, 'confirmPaymentByUser']); // 3. User bayar
    Route::get('/merchant/sales', [TransactionController::class, 'salesHistory']); // Dashboard Merchant

    // Fitur Transaksi QR (Metode 2)
    Route::post('/transactions/generate-qr', [TransactionController::class, 'createQrForCashier']); // Kasir
    Route::post('/transactions/pay-qr', [TransactionController::class, 'payByQr']); // Murid Scan
    Route::post('/transactions/pay-kiosk-card', [TransactionController::class, 'payByCardOnKiosk']); // Murid Tap di Kiosk

    // --- PRE-ORDER ---
    Route::post('/po/checkout', [PreOrderController::class, 'store']); // Beli PO
    Route::get('/po/my-orders', [PreOrderController::class, 'myOrders']);
    Route::post('/po/{id}/cancel', [PreOrderController::class, 'cancel']); // Batal (Kena Pinalti)

    // --- KEUANGAN (USER) ---
    Route::post('/balance/request-topup', [BalanceController::class, 'requestTopUp']); // Upload Bukti
    Route::post('/balance/withdraw', [BalanceController::class, 'withdraw']); // Request Tarik
    Route::post('/balance/transfer', [BalanceController::class, 'transfer']); // P2P

    // --- KIOSK ---
    Route::post('/kiosk/check-balance', [UserController::class, 'checkBalance']); // NFC/QR Member

    // =============================================================
    // 3. ADMIN DASHBOARD (Role Protected inside Controller)
    // =============================================================

    // Dashboard Utama
    Route::get('/admin/stats', [AdminController::class, 'getDashboardStats']);
    Route::post('/admin/change-pin', [AdminController::class, 'changePin']); // ganti pin

    // Manajemen User (Admin Pusat/Verifikator)
    Route::get('/admin/verifications', [AdminController::class, 'getPendingUsers']);
    Route::put('/admin/verifications/{id}/approve', [AdminController::class, 'approveUser']);
    Route::put('/admin/verifications/{id}/reject', [AdminController::class, 'rejectUser']);
    Route::put('/admin/users/{id}/reset', [AdminController::class, 'resetUserAccess']); // Reset Pass/PIN
    Route::post('/admin/fix-member-ids', [AdminController::class, 'generateOldMemberIds']); // Maintenance
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser']); // update user

    // CMS Informasi (Admin Pusat)
    Route::post('/admin/informations', [InformationController::class, 'store']);
    Route::delete('/admin/informations/{id}', [InformationController::class, 'destroy']);

    // Keuangan (Admin Keuangan)
    Route::get('/admin/topups', [AdminController::class, 'getPendingTopUps']);
    Route::put('/admin/topups/{id}/approve', [AdminController::class, 'approveTopUp']);
    Route::post('/admin/balance/topup-manual', [AdminController::class, 'manualTopUp']); // Tunai

    Route::get('/admin/withdrawals', [AdminController::class, 'getPendingWithdrawals']);
    // Gunakan POST untuk approve withdrawal karena ada upload bukti transfer admin
    Route::post('/admin/withdrawals/{id}/approve', [AdminController::class, 'approveWithdrawal']);
    Route::put('/admin/withdrawals/{id}/reject', [AdminController::class, 'rejectWithdrawal']);

    // Gudang & PO (Admin Kasir)
    Route::get('/admin/products/missing-rack', [AdminController::class, 'getProductsMissingRack']);
    Route::put('/admin/products/{id}/rack', [AdminController::class, 'updateRackLocation']);
    Route::post('/admin/po/take', [PreOrderController::class, 'markAsTaken']); // Scan Pengambilan Barang PO

});