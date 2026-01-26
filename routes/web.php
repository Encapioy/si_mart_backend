<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;

// Livewire Components
use App\Livewire\Login;
use App\Livewire\Dashboard;
use App\Livewire\HistoryPage;
use App\Livewire\ProfilePage;
use App\Livewire\AdDetail;
use App\Livewire\ScanPage;
use App\Livewire\PaymentPage;
use App\Livewire\PaymentSuccessPage;
use App\Livewire\TransferPage;
use App\Livewire\Register;
use App\Livewire\AdminMerchantDetail;
use App\Livewire\AdminMerchantList;
use App\Livewire\AdminDashboard;
use App\Livewire\AdminTransactionHistory;
use App\Livewire\AdminFinancialStats;
use App\Livewire\AdminManageCashier;
use App\Livewire\AdminTopupHistory;
use App\Livewire\AdminTopup;
use App\Livewire\AdminUserVerification;

// 1. Landing Page
Route::get('/', function () {
    // 1. Cek apakah ADMIN yang sedang login?
    if (Auth::guard('admin')->check()) {
        // Jika role admin punya dashboard khusus (misal kasir beda dengan admin pusat)
        // Kamu bisa tambahkan logika if($admin->role == 'kasir') disini jika perlu
        return redirect()->route('admin.dashboard');
    }

    // 2. Cek apakah USER BIASA (Siswa/Ortu) yang sedang login?
    if (Auth::guard('web')->check()) {
        return redirect()->route('dashboard');
    }

    // 3. Jika BELUM LOGIN, tampilkan Landing Page
    return view('landing');
})->name('home');
// 2. Login Page
Route::get('/register', Register::class)->name('register');
Route::get('/login', Login::class)->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. User Web Routes
Route::middleware(['auth:web'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/history', HistoryPage::class)->name('history');
    Route::get('/profile', ProfilePage::class)->name('profile');
    Route::get('/ads/{id}', AdDetail::class)->name('ads.show');
    Route::get('/scan', ScanPage::class)->name('scan');
    Route::get('/transfer/{memberId}', TransferPage::class)->name('transfer.user');
    Route::get('/pay/{storeId}', PaymentPage::class)->name('pay');
    Route::get('/payment/success/{code}', PaymentSuccessPage::class)->name('payment.success');
});

// 4. Admin Routes (Jangan diganggu)
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/topup-panel', AdminTopup::class)->name('admin.topup');
    Route::get('/admin/ajax/cashiers', [AdminController::class, 'getCashiers']);
    Route::get('/admin/ajax/search-user', [AdminController::class, 'webSearchUser']);

    // ADMIN PUSAT WEB DASHBOARD
    Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/admin/merchant/', AdminMerchantList::class)->name('admin.merchant.list');
    Route::get('/admin/merchant/{storeId}', AdminMerchantDetail::class)->name('admin.merchant.detail');
    Route::get('/admin/finance', AdminFinancialStats::class)->name('admin.finance.stats');
    Route::get('/admin/transactions', AdminTransactionHistory::class)->name('admin.transactions');
    Route::get('/admin/topups', AdminTopupHistory::class)->name('admin.topups');
    Route::get('/admin/manage-cashier', AdminManageCashier::class)->name('admin.manage.cashier');
    Route::get('/admin/verification', AdminUserVerification::class)->name('admin.verifikasi');
});