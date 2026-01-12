<div class="p-6 md:p-8 min-h-screen bg-slate-50">

    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Overview</h1>
            <p class="text-slate-500 text-sm mt-1">Pantau performa SI PAY secara real-time.</p>
        </div>
        <div class="text-right hidden md:block">
            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">
                {{ now()->format('d M Y') }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition group">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-emerald-100 p-3 rounded-xl group-hover:bg-emerald-200 transition">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg">+Active</span>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Uang Beredar</p>
            <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($uang_beredar, 0, ',', '.') }}</h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition group">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-blue-100 p-3 rounded-xl group-hover:bg-blue-200 transition">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Total Topup</p>
            <h3 class="text-xl font-bold text-slate-800">{{ number_format($jumlah_topup) }} <span class="text-sm font-medium text-slate-400">kali</span></h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition group">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-purple-100 p-3 rounded-xl group-hover:bg-purple-200 transition">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </div>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Transaksi</p>
            <h3 class="text-xl font-bold text-slate-800">{{ number_format($jumlah_transaksi) }} <span class="text-sm font-medium text-slate-400">trx</span></h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition group">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-orange-100 p-3 rounded-xl group-hover:bg-orange-200 transition">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Total Merchant</p>
            <h3 class="text-xl font-bold text-slate-800">{{ number_format($jumlah_merchant) }} <span class="text-sm font-medium text-slate-400">toko</span></h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition group">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-slate-100 p-3 rounded-xl group-hover:bg-slate-200 transition">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Total Santri</p>
            <h3 class="text-xl font-bold text-slate-800">{{ number_format($jumlah_user) }} <span class="text-sm font-medium text-slate-400">orang</span></h3>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Transaksi Terakhir</h3>
            <a href="{{ route('admin.transactions') }}" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4">Kode TRX</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4 text-right">Nominal</th>
                        <th class="px-6 py-4 text-center">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recent_transactions as $trx)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-mono font-bold text-slate-700">{{ $trx->transaction_code }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $trx->user->nama_lengkap ?? 'Unknown' }}</div>
                            <div class="text-xs text-slate-400">{{ $trx->user->member_id ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($trx->type == 'topup')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">TOPUP</span>
                            @elseif($trx->type == 'payment')
                                <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded text-xs font-bold">PAYMENT</span>
                            @else
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">TRANSFER</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $trx->description }}</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-800">
                            Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center text-xs text-slate-400">
                            {{ $trx->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada transaksi hari ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>