<div class="min-h-screen bg-[#F1F5F9] pb-20 font-sans">

    {{-- 1. HERO SECTION --}}
    <div class="relative h-80 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900">
            <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900 via-slate-900 to-black opacity-90"></div>

            {{-- Animated Blobs --}}
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/4 animate-pulse"></div>
            <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-emerald-600/10 rounded-full blur-[80px] translate-y-1/2 -translate-x-1/4"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10 pt-8 flex justify-between items-start">
            <a href="{{ route('admin.merchant.list') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-xl text-white text-sm font-medium hover:bg-white/10 hover:border-white/20 transition-all group">
                <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-40 relative z-20">

        {{-- 2. PROFILE CARD --}}
        <div class="bg-white rounded-[2rem] shadow-2xl shadow-slate-200/50 p-6 md:p-10 border border-white mb-10 relative overflow-hidden">
            {{-- Decorative bg --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-16 -mt-16 pointer-events-none"></div>

            <div class="flex flex-col md:flex-row gap-8 items-start relative">
                {{-- Store Image --}}
                <div class="shrink-0 mx-auto md:mx-0">
                    <div class="relative group">
                        <div class="absolute -inset-2 bg-gradient-to-tr from-blue-600 to-cyan-400 rounded-[2.5rem] blur opacity-20 group-hover:opacity-40 transition duration-500"></div>
                        <div class="relative">
                            @if($store->gambar)
                                <img src="{{ asset('storage/' . $store->gambar) }}"
                                     class="w-40 h-40 md:w-48 md:h-48 rounded-[2rem] object-cover border-4 border-white shadow-xl">
                            @else
                                <div class="w-40 h-40 md:w-48 md:h-48 rounded-[2rem] bg-slate-50 border-4 border-white shadow-xl flex flex-col items-center justify-center text-slate-300">
                                    <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="text-[10px] font-bold uppercase tracking-widest">No Image</span>
                                </div>
                            @endif

                            {{-- Verified Badge --}}
                            <div class="absolute -bottom-3 -right-3 bg-blue-600 text-white p-2.5 rounded-2xl border-4 border-white shadow-lg" title="Official Merchant">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.64.304 1.24.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Store Info --}}
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center justify-center md:justify-start gap-3 mb-3">
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-extrabold uppercase tracking-widest rounded-full border border-blue-100">Merchant</span>
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-extrabold uppercase tracking-widest rounded-full border border-emerald-100">Active</span>
                            </div>
                            <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-4">{{ $store->nama_toko }}</h1>

                            <div class="flex flex-wrap justify-center md:justify-start gap-3">
                                <div class="flex items-center gap-2 text-slate-600 bg-slate-50 pl-2 pr-4 py-1.5 rounded-full border border-slate-200/60">
                                    <div class="w-6 h-6 rounded-full bg-white shadow-sm flex items-center justify-center text-blue-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <span class="text-xs font-bold">{{ $store->owner->nama_lengkap ?? 'Unknown Owner' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-600 bg-slate-50 pl-2 pr-4 py-1.5 rounded-full border border-slate-200/60">
                                    <div class="w-6 h-6 rounded-full bg-white shadow-sm flex items-center justify-center text-red-500">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                    </div>
                                    <span class="text-xs font-bold">{{ $store->lokasi }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 relative">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 to-transparent rounded-full"></div>
                        <p class="pl-6 text-slate-500 text-sm leading-relaxed italic">"{{ $store->deskripsi }}"</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. STATS GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <div class="group bg-white p-1 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="h-full p-6 bg-gradient-to-br from-emerald-50/50 to-white rounded-[1.8rem] flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-emerald-500 text-white rounded-2xl shadow-lg shadow-emerald-200 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-black text-emerald-600 uppercase bg-emerald-100/80 rounded-lg">Today</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Pendapatan Hari Ini</p>
                        <p class="text-3xl font-black text-slate-900">Rp {{ number_format($incomeToday, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="group bg-white p-1 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="h-full p-6 bg-gradient-to-br from-blue-50/50 to-white rounded-[1.8rem] flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-200 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-black text-blue-600 uppercase bg-blue-100/80 rounded-lg">Month</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Pendapatan Bulan Ini</p>
                        <p class="text-3xl font-black text-slate-900">Rp {{ number_format($incomeMonth, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="group bg-slate-900 p-1 rounded-[2rem] shadow-xl shadow-slate-900/10 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 sm:col-span-2 lg:col-span-1">
                <div class="h-full p-6 bg-gradient-to-br from-slate-800 to-slate-900 rounded-[1.8rem] flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>

                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <div class="p-3 bg-white/10 backdrop-blur text-white rounded-2xl border border-white/10 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-black text-slate-300 uppercase bg-white/10 rounded-lg border border-white/5">Lifetime</span>
                    </div>
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Akumulasi</p>
                        <p class="text-3xl font-black text-white">Rp {{ number_format($incomeTotal, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. NEW: TRANSACTION HISTORY --}}
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-slate-400 border border-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Riwayat Transaksi</h3>
                    <p class="text-xs text-slate-500">Transaksi terbaru yang masuk ke toko ini</p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Waktu</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pembeli</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Item</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Total</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($transactions as $trx)
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-700">{{ $trx->created_at->format('d M Y') }}</span>
                                            <span class="text-xs text-slate-400">{{ $trx->created_at->format('H:i') }} WIB</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xs font-bold border border-slate-200">
                                                {{ substr($trx->user->nama_lengkap ?? 'Guest', 0, 1) }}
                                            </div>
                                            <span class="text-sm font-medium text-slate-600">{{ $trx->user->nama_lengkap ?? 'Guest User' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{-- Asumsi ada relasi items atau kolom description --}}
                                        <span class="text-sm text-slate-600">{{ $trx->keterangan ?? 'Pembelian Produk' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-black text-slate-800 group-hover:text-blue-600 transition">Rp {{ number_format($trx->amount ?? $trx->total_bayar, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        {{-- Sesuaikan status dengan data kamu --}}
                                        @if($trx->status == 'success' || $trx->status == 'paid')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                BERHASIL
                                            </span>
                                        @elseif($trx->status == 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                                PENDING
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">
                                                GAGAL
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <p class="text-slate-500 font-medium">Belum ada transaksi</p>
                                            <p class="text-slate-400 text-xs mt-1">Transaksi yang masuk akan muncul di sini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination Links (Optional) --}}
                @if($transactions->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- 5. PRODUCT CATALOG --}}
        <div>
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-slate-400 border border-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">Katalog Produk</h3>
                        <p class="text-sm text-slate-500">Ditemukan {{ $store->products->count() }} item tersedia</p>
                    </div>
                </div>
            </div>

            @if($store->products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($store->products as $product)
                        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group flex flex-col h-full">
                            {{-- Image Area --}}
                            <div class="h-56 bg-slate-50 relative overflow-hidden shrink-0">
                                @if($product->gambar)
                                    <div class="absolute inset-0 bg-slate-200 animate-pulse group-hover:hidden"></div> <img src="{{ asset('storage/products/thumbnails/' . $product->gambar) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700 ease-in-out">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-slate-50">
                                        <svg class="w-12 h-12 mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="text-[10px] font-bold tracking-widest uppercase opacity-50">No Photo</span>
                                    </div>
                                @endif

                                <div class="absolute top-4 left-4">
                                     <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-white/90 backdrop-blur text-slate-800 shadow-sm border border-white/50">
                                        STOK: {{ $product->stok ?? '∞' }}
                                     </span>
                                </div>
                            </div>

                            {{-- Content Area --}}
                            <div class="p-6 flex flex-col flex-1">
                                <div class="flex-1">
                                    <h4 class="font-black text-slate-800 text-lg mb-2 leading-tight group-hover:text-blue-600 transition-colors">{{ $product->nama_produk }}</h4>
                                    <p class="text-xs text-slate-500 line-clamp-2 mb-4 leading-relaxed">{{ $product->deskripsi }}</p>
                                </div>

                                <div class="flex items-end justify-between pt-4 border-t border-slate-50 mt-auto">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Harga Unit</p>
                                        <span class="text-xl font-black text-blue-600">Rp {{ number_format($product->harga, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-blue-500/30">
                                        <svg class="w-5 h-5 group-hover:rotate-45 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200 p-16 text-center">
                    <div class="relative w-24 h-24 mx-auto mb-6">
                        <div class="absolute inset-0 bg-blue-100 rounded-full animate-ping opacity-20"></div>
                        <div class="relative w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 shadow-inner border border-slate-100">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">Katalog Masih Kosong</h3>
                    <p class="text-slate-500 mt-2 max-w-sm mx-auto leading-relaxed">Merchant ini belum mengunggah produk apapun.</p>
                </div>
            @endif
        </div>
    </div>
</div>