<div class="min-h-screen bg-slate-50 pb-12">

    <div class="relative bg-slate-900 h-48 overflow-hidden">
        <div class="absolute inset-0 opacity-20 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-blue-900 to-slate-900 opacity-90"></div>

        <a href="{{ route('admin.merchants') }}" class="absolute top-6 left-6 text-white/80 hover:text-white flex items-center gap-2 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>

    <div class="max-w-7xl mx-auto px-6 -mt-20 relative z-10">

        <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col md:flex-row gap-6 mb-8 border border-slate-100">
            <div class="shrink-0 relative">
                @if($store->gambar)
                    <img src="{{ asset('storage/' . $store->gambar) }}" class="w-32 h-32 rounded-xl object-cover border-4 border-white shadow-md bg-slate-100">
                @else
                    <div class="w-32 h-32 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 border-4 border-white shadow-md flex items-center justify-center text-slate-400">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                @endif
            </div>

            <div class="flex-1 pt-2">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">{{ $store->nama_toko }}</h1>
                        <p class="text-slate-500 mt-1 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Owner: <span class="font-medium text-slate-700">{{ $store->owner->nama_lengkap ?? 'Unknown' }}</span>
                        </p>
                        <p class="text-slate-500 mt-1 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ $store->lokasi }}
                        </p>
                        <p class="text-slate-500 text-sm mt-3 max-w-2xl leading-relaxed">{{ $store->deskripsi }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="p-3 bg-green-100 rounded-xl text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pendapatan Hari Ini</p>
                    <p class="text-2xl font-bold text-slate-900">Rp {{ number_format($incomeToday, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="p-3 bg-blue-100 rounded-xl text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pendapatan Bulan Ini</p>
                    <p class="text-2xl font-bold text-slate-900">Rp {{ number_format($incomeMonth, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="p-3 bg-purple-100 rounded-xl text-purple-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-slate-900">Rp {{ number_format($incomeTotal, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Katalog Produk ({{ $store->products->count() }})
            </h3>
            </div>

        @if($store->products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($store->products as $product)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition group">
                        <div class="h-48 bg-slate-100 relative overflow-hidden">
                             @if($product->gambar)
                                <img src="{{ asset('storage/' . $product->gambar) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                             @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                             @endif
                             <div class="absolute top-3 right-3 bg-white/90 backdrop-blur px-2 py-1 rounded-lg text-xs font-bold text-slate-800 shadow-sm">
                                 Stok: {{ $product->stok ?? '∞' }}
                             </div>
                        </div>
                        <div class="p-4">
                            <h4 class="font-bold text-slate-800 mb-1 truncate">{{ $product->nama_produk }}</h4>
                            <p class="text-xs text-slate-500 line-clamp-2 mb-3 h-8">{{ $product->deskripsi }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-blue-600">Rp {{ number_format($product->harga, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl border border-dashed border-slate-300 p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-700">Belum ada produk</h3>
                <p class="text-slate-500 text-sm">Toko ini belum menambahkan produk apapun ke katalog.</p>
            </div>
        @endif

    </div>
</div>