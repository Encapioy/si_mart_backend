<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.submit'); // Pakai fungsi loginWeb
Route::get('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

// Proteksi Halaman Admin (Harus Login)
// --- ROUTE KHUSUS ADMIN (Jaga dengan guard admin) ---
Route::middleware(['auth:admin'])->group(function () {

    Route::get('/admin-panel/topup', [\App\Http\Controllers\Api\AdminController::class, 'showTopUpPage'])->name('admin.topup');

    Route::get('/admin/ajax/cashiers', [\App\Http\Controllers\Api\AdminController::class, 'getCashiers']);
    Route::get('/admin/ajax/search-user', [\App\Http\Controllers\Api\AdminController::class, 'webSearchUser']);
});


// --- ROUTE KHUSUS USER (Jaga dengan guard web biasa) ---
Route::middleware(['auth:web'])->group(function () {

    Route::get('/user/home', function () {
        return "Halo User!";
    })->name('user.home');

});
