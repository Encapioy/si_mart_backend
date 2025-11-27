<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BalanceMutation;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Query Mutasi milik user ini
        $query = BalanceMutation::where('user_id', $user->id);

        // --- FILTER 3 TAB ---
        // ?filter=income  -> Tab Pemasukan
        // ?filter=expense -> Tab Pengeluaran
        // Kosong         -> Tab Semua

        if ($request->filter == 'income') {
            $query->where('type', 'credit');
        } elseif ($request->filter == 'expense') {
            $query->where('type', 'debit');
        }

        // Urutkan dari yang terbaru & Pagination 15
        $history = $query->latest()->paginate(15);

        return response()->json([
            'message' => 'Riwayat Saldo',
            'saldo_saat_ini' => $user->saldo,
            'data' => $history
        ]);
    }
}