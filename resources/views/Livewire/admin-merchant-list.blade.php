<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Daftar Merchant</h1>
            <p class="text-slate-500 text-sm">Kelola semua toko mitra yang terdaftar.</p>
        </div>

        <div class="relative">
            <input type="text" placeholder="Cari nama toko..."
                class="pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-xs border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Info Toko</th>
                        <th class="px-6 py-4">Pemilik (Owner)</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($stores as $store)
                        <tr class="hover:bg-slate-50 transition duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 flex-shrink-0">
                                        <img src="{{ $store->gambar ? asset('storage/' . $store->gambar) : 'https://ui-avatars.com/api/?name=' . urlencode($store->nama_toko) . '&background=random' }}"
                                            class="w-full h-full rounded-lg object-cover border border-slate-200"
                                            alt="{{ $store->nama_toko }}">
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-base">{{ $store->nama_toko }}</p>
                                        <p class="text-slate-500 text-xs truncate max-w-[200px]">
                                            {{ $store->lokasi ?? 'Lokasi tidak diset' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                        {{ substr($store->owner->nama_lengkap ?? 'X', 0, 1) }}
                                    </div>
                                    <span
                                        class="font-medium text-slate-700">{{ $store->owner->nama_lengkap ?? 'Tidak Diketahui' }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-medium">
                                    {{ $store->kategori ?? 'Umum' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($store->is_open)
                                    <span
                                        class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200">
                                        BUKA
                                    </span>
                                @else
                                    <span
                                        class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold border border-red-200">
                                        TUTUP
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.merchant.detail', $store->id) }}"
                                    class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 px-4 py-2 rounded-lg text-xs font-bold transition">
                                    <span>Detail</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="bg-slate-100 p-4 rounded-full mb-3">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                    </div>
                                    <p class="text-slate-500 font-medium">Belum ada merchant yang terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $stores->links() }}
        </div>
    </div>
</div>