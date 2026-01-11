<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Information;
use App\Models\BalanceMutation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    // 1. DATA HOMEPAGE (Nama, Saldo, Foto, Notifikasi Belum Dibaca)
    public function home(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Data Homepage',
            'data' => [
                'nama_lengkap' => $user->nama_lengkap,
                'saldo' => $user->saldo, // Saldo Utama
                'profile_photo_url' => $user->profile_photo_url, // URL Foto yang tadi kita buat

                // (Opsional) Data tambahan biar Homepage makin kaya
                'member_id' => $user->member_id,
                'status_verified' => $user->status_verifikasi,
            ]
        ]);
    }

    // 2. DATA HALAMAN INFO (Promo, Pondok, Sistem)
    public function infos(Request $request)
    {
        // Fitur Filter Kategori ( ?kategori=promo )
        $query = Information::query();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Ambil data terbaru
        $infos = $query->latest()->get()->map(function ($info) {
            return [
                'id' => $info->id,
                'judul' => $info->judul,
                'kategori' => $info->kategori,
                'tanggal' => $info->created_at->format('d M Y'), // Format tanggal cantik
                'gambar' => $info->gambar,
                'konten' => $info->konten,
                // Khusus Promo
                'kode_promo' => $info->kode_promo,
                'berlaku_sampai' => $info->berlaku_sampai,
            ];
        });

        return response()->json([
            'message' => 'List Informasi',
            'data' => $infos
        ]);
    }

    // 3. SUMMARY FINANSIAL
    public function getUserSummary(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();

        // --- BAGIAN 1: STATISTIK KEUANGAN (Hari Ini & Bulan Ini) ---
        // Kita gunakan satu query base untuk bulan ini agar hemat,
        // lalu filter by collection untuk data hari ini.

        // Ambil semua mutasi bulan ini
        $mutationsThisMonth = BalanceMutation::where('user_id', $user->id)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->get();

        // Hitung Bulan Ini
        $incomeMonth = $mutationsThisMonth->where('type', 'credit')->sum('amount');
        $expenseMonth = $mutationsThisMonth->where('type', 'debit')->sum('amount');

        // Filter lagi untuk Hari Ini
        $mutationsToday = $mutationsThisMonth->filter(function ($item) use ($now) {
            return $item->created_at->isToday();
        });

        $incomeToday = $mutationsToday->where('type', 'credit')->sum('amount');
        $expenseToday = $mutationsToday->where('type', 'debit')->sum('amount');


        // --- BAGIAN 2: LANGGANAN (Top 5 Frequent Contacts) ---
        // Cari user_id lain yang paling sering muncul di history 'transfer'
        // Kita cari di kolom 'related_user_id' pada tabel BalanceMutation
        $frequentContacts = BalanceMutation::where('user_id', $user->id)
            ->whereNotNull('related_user_id') // Hanya transaksi yang ada lawan mainnya
            ->select('related_user_id', DB::raw('count(*) as total'))
            ->groupBy('related_user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('relatedUser:id,nama_lengkap,username,profile_photo') // Load data usernya
            ->get()
            ->map(function ($item) {
                // Kita rapikan strukturnya agar frontend enak bacanya
                $friend = $item->relatedUser;
                return [
                    'user_id' => $friend->id,
                    'nama' => $friend->nama_lengkap, // Misal: "Udin"
                    'username' => $friend->username,
                    'initial' => substr($friend->nama_lengkap, 0, 1), // Untuk Icon bulat (UI)
                    'profile_photo_url' => $friend->profile_photo_url, // Accessor foto
                    'interaction_count' => $item->total
                ];
            });


        // --- BAGIAN 3: AKTIVITAS TERBARU (Limit 5) ---
        // Kita ambil langsung dari tabel mutation agar mencakup semua jenis (Topup, Trf, Bayar)
        $recentActivity = BalanceMutation::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'title' => $this->formatTitle($m), // Helper function biar judulnya cantik
                    'description' => $m->description,
                    'amount' => (int) $m->amount,
                    'type' => $m->type, // 'credit' (hijau) atau 'debit' (merah/orange)
                    'date' => $m->created_at->format('d M, H:i'), // "11 Jan, 14:48"
                    'category' => $m->category // 'payment', 'transfer', 'topup'
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                // Header Saldo
                'balance' => (int) $user->saldo,

                // Kotak Statistik (Frontend tinggal switch tab Hari Ini / Bulan Ini)
                'stats' => [
                    'today' => [
                        'income' => (int) $incomeToday,
                        'expense' => (int) $expenseToday
                    ],
                    'this_month' => [
                        'income' => (int) $incomeMonth,
                        'expense' => (int) $expenseMonth
                    ]
                ],

                // List Lingkaran (Langganan)
                'frequent_contacts' => $frequentContacts,

                // List Bawah (Aktivitas)
                'recent_activity' => $recentActivity
            ]
        ]);
    }

    // Helper kecil untuk merapikan judul aktivitas
    private function formatTitle($mutation)
    {
        if ($mutation->category == 'topup')
            return 'Top Up Saldo';
        if ($mutation->category == 'payment')
            return 'Pembayaran Toko';
        if ($mutation->category == 'withdraw')
            return 'Penarikan Saldo';

        // Khusus Transfer
        if ($mutation->category == 'transfer') {
            return $mutation->type == 'debit' ? 'Transfer Keluar' : 'Transfer Masuk';
        }

        return 'Transaksi';
    }
}