<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // --- FUNGSI BANTUAN CATAT MUTASI ---
    protected function recordMutation($user, $amount, $type, $category, $description)
    {
        // Pastikan user/admin valid sebelum catat
        if ($user) {
            \App\Models\BalanceMutation::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => $type, // 'credit' (Masuk) atau 'debit' (Keluar)
                'current_balance' => $user->saldo, // Saldo TERBARU setelah ditambah/dikurang
                'category' => $category,
                'description' => $description
            ]);
        }
    }
}
