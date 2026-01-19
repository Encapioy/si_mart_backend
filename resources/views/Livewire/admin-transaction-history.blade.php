<div class="min-h-screen bg-slate-50 p-6 md:p-8">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Riwayat Transaksi</h1>
            <p class="text-slate-500 text-sm mt-1">Pantau pembayaran toko dan transfer antar user.</p>
        </div>

        <div class="relative w-full md:w-72">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search"
                   type="text"
                   class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition shadow-sm"
                   placeholder="Cari Kode TRX, Nama, Toko...">
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-xs border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Detail Transaksi</th>
                        <th class="px-6 py-4">Pembayar (User)</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4">Tujuan / Deskripsi</th>
                        <th class="px-6 py-4 text-center">Nominal</th>
                        <th class="px-6 py-4 text-center">Koreksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition duration-150">

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-700 text-xs bg-slate-100 px-2 py-1 rounded w-fit mb-1">
                                    {{ $trx->transaction_code }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $trx->created_at->format('d M Y') }} • {{ $trx->created_at->format('H:i') }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <div class="font-bold text-slate-700">{{ $trx->user->nama_lengkap ?? 'User Terhapus' }}</div>
                                        <div class="text-xs text-slate-400">{{ $trx->user->member_id ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if($trx->type == 'payment')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-orange-50 text-orange-600 border border-orange-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                        MERCHANT
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                        TRANSFER
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($trx->store)
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700">{{ $trx->store->nama_toko }}</span>
                                        <span class="text-xs text-slate-400">Owner: {{ $trx->store->owner->nama_lengkap ?? '-' }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-slate-600 italic">
                                        "{{ Str::limit($trx->description, 30) }}"
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-slate-800">
                                    Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <button wire:click="deleteTransaction({{ $item->id }})"
                                    wire:confirm="Yakin ingin membatalkan TopUp ini? Saldo user akan otomatis berkurang!"
                                    class="group inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 hover:text-red-700 hover:border-red-200 transition-all duration-200 active:scale-95">

                                    {{-- Ikon Trash (Sampah) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>

                                    {{-- Teks --}}
                                    <span class="text-xs font-bold tracking-wide">Batal</span>
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <p class="font-medium text-slate-500">Tidak ada data transaksi ditemukan.</p>
                                    <p class="text-sm mt-1 text-slate-400">Coba ubah kata kunci pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $transactions->links() }}
        </div>
    </div>
</div>