<div class="min-h-screen bg-slate-50 p-6 md:p-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Riwayat Top Up</h1>
            <p class="text-slate-500 text-sm mt-1">Daftar pemasukan saldo manual oleh Admin/Kasir.</p>
        </div>

        <div class="bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="p-2 bg-green-100 rounded-full text-green-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 uppercase font-bold">Total Hari Ini</p>
                <p class="text-sm font-bold text-slate-700">Rp {{ number_format(\App\Models\TopUp::whereDate('created_at', now())->sum('amount'), 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-xs border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Santri (Penerima)</th>
                        <th class="px-6 py-4">Nominal</th>
                        <th class="px-6 py-4">Kasir (Petugas)</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $item)
                        <tr class="hover:bg-slate-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-700">
                                    {{ $item->created_at->format('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $item->created_at->format('H:i') }} WIB
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($item->user->nama_lengkap ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-700">{{ $item->user->nama_lengkap ?? 'User Terhapus' }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $item->user->member_id ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-lg font-bold border border-emerald-100">
                                    + Rp {{ number_format($item->amount, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-[10px] font-bold">
                                        {{ substr($item->admin->nama_lengkap ?? 'S', 0, 1) }}
                                    </div>
                                    <span class="text-slate-600 font-medium text-sm">
                                        {{ $item->admin->nama_lengkap ?? 'Sistem' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($item->status == 'approved')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-green-100 text-green-700 px-2 py-1 rounded-full uppercase tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Sukses
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded-full uppercase">
                                        {{ $item->status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <p class="text-sm font-medium">Belum ada riwayat topup manual.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $history->links() }}
        </div>
    </div>
</div>