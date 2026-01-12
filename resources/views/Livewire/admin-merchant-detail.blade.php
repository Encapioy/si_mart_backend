<div class="mt-12">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-slate-400 border border-slate-100">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Riwayat Transaksi Masuk</h3>
                        <p class="text-sm text-slate-500">Daftar pembayaran yang diterima toko ini</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
                                <th class="px-6 py-4">Kode TRX</th>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Pembeli (Siswa)</th>
                                <th class="px-6 py-4 text-right">Nominal</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($transactions as $trx)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4">
                                        <span class="font-mono font-medium text-slate-700 select-all">
                                            {{ $trx->transaction_code }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-700">{{ $trx->created_at->format('d M Y') }}</div>
                                        <div class="text-[10px] text-slate-400 font-bold">{{ $trx->created_at->format('H:i') }} WIB</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold uppercase">
                                                {{ substr($trx->user->nama_lengkap ?? '?', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-700">{{ $trx->user->nama_lengkap ?? 'User Terhapus' }}</div>
                                                <div class="text-xs text-slate-400">{{ $trx->user->username ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <span class="font-bold text-emerald-600">
                                            + Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-emerald-50 text-emerald-600 border border-emerald-100">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            Berhasil
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                            <p class="font-medium">Belum ada riwayat transaksi</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>