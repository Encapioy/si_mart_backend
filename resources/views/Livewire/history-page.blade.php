<div class="min-h-screen bg-gray-50 pb-32">

    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-800">Riwayat Transaksi</h1>

            {{-- <button class="p-2 rounded-full hover:bg-gray-100 text-gray-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
            </button> --}}
        </div>
    </div>

    <div class="px-4 mt-4 space-y-3">

        @forelse($mutations as $item)
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition active:scale-[0.98]">

                <div class="shrink-0 relative">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center
                        {{ $item->type == 'credit' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500' }}">

                        @if($item->category == 'topup')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        @elseif($item->category == 'payment')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        @elseif($item->category == 'transfer')
                            <svg class="w-6 h-6 transform {{ $item->type == 'debit' ? '-rotate-45' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        @endif
                    </div>

                    <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5">
                        @if($item->type == 'credit')
                            <div class="bg-green-500 rounded-full w-4 h-4 flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                            </div>
                        @else
                            <div class="bg-red-500 rounded-full w-4 h-4 flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-gray-900 truncate">
                        {{ $item->description }}
                    </h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-gray-400 font-medium">
                            {{ $item->created_at->format('d M, H:i') }}
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-md font-medium uppercase tracking-wide
                            {{ $item->category == 'payment' ? 'bg-orange-50 text-orange-600' : '' }}
                            {{ $item->category == 'topup' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $item->category == 'transfer' ? 'bg-purple-50 text-purple-600' : '' }}
                        ">
                            {{ $item->category }}
                        </span>
                    </div>
                </div>

                <div class="text-right shrink-0">
                    <p class="text-sm font-bold {{ $item->type == 'credit' ? 'text-green-600' : 'text-slate-800' }}">
                        {{ $item->type == 'credit' ? '+' : '-' }}Rp{{ number_format($item->amount, 0, ',', '.') }}
                    </p>
                    @if($item->status == 'pending')
                        <span class="text-[10px] text-yellow-600 bg-yellow-50 px-1.5 py-0.5 rounded">Proses</span>
                    @else
                         <span class="text-[10px] text-green-600 flex justify-end items-center gap-1">
                            Sukses <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                         </span>
                    @endif
                </div>

            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="text-gray-900 font-semibold">Belum ada transaksi</h3>
                <p class="text-gray-500 text-sm mt-1">Semua riwayat uang masuk dan keluar akan tampil di sini.</p>
            </div>
        @endforelse

        <div class="pt-4 pb-8">
             {{ $mutations->links(data: ['scrollTo' => false]) }}
        </div>
    </div>
</div>