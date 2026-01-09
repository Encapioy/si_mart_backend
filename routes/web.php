<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;

// Livewire Components
use App\Livewire\Login;
use App\Livewire\Dashboard;
use App\Livewire\ScanQr;
use App\Livewire\PaymentPage;
use App\Livewire\Register;

// 1. Landing Page
Route::get('/', function () {
    return view('landing'); })->name('home');

// 2. Login Page
Route::get('/register', Register::class)->name('register');
Route::get('/login', Login::class)->name('login');
Route::get('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

// 3. User Web Routes
Route::middleware(['auth:web'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/scan', ScanQr::class)->name('scan');
    Route::get('/pay/{storeId}', PaymentPage::class)->name('pay');
});

// 4. Admin Routes (Jangan diganggu)
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin-panel/topup', [AdminController::class, 'showTopUpPage'])->name('admin.topup');
    Route::get('/admin/ajax/cashiers', [AdminController::class, 'getCashiers']);
    Route::get('/admin/ajax/search-user', [AdminController::class, 'webSearchUser']);
});