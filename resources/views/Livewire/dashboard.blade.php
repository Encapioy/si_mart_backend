<div class="min-h-screen bg-slate-50 font-sans selection:bg-blue-100 selection:text-blue-900 pb-20">

    {{-- =============================================== --}}
    {{-- 1. HEADER SECTION (SALDO & PROFILE) --}}
    {{-- =============================================== --}}
    <div
        class="relative bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-800 pt-12 pb-36 rounded-b-[3.5rem] shadow-xl overflow-hidden">

        {{-- Decorative Background Elements --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div
                class="absolute -top-[20%] -left-[10%] w-96 h-96 bg-blue-400/20 rounded-full blur-3xl mix-blend-overlay animate-pulse">
            </div>
            <div
                class="absolute top-[10%] right-[0%] w-72 h-72 bg-indigo-300/20 rounded-full blur-3xl mix-blend-overlay">
            </div>
            <div
                class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10">
            </div>
        </div>

        <div class="relative z-10 text-center px-6">
            {{-- User Badge --}}
            <div
                class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-4 py-1.5 rounded-full border border-white/10 shadow-inner mb-6">
                <div class="w-5 h-5 rounded-full bg-blue-400/30 flex items-center justify-center">
                    <svg class="w-3 h-3 text-blue-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span
                    class="text-xs font-semibold text-blue-50 tracking-wide uppercase">{{ Str::limit($user->nama_lengkap, 20) }}</span>
            </div>

            {{-- Balance Display --}}
            <div class="flex flex-col items-center">
                <p class="text-blue-200 text-sm font-medium mb-2 tracking-wide">Total Saldo Aktif</p>
                <h2 class="text-[2.75rem] leading-none font-bold tracking-tight text-white drop-shadow-md">
                    @if($user->saldo < 0)
                        <span class="text-red-300 drop-shadow-none">- Rp
                            {{ number_format(abs($user->saldo), 0, ',', '.') }}</span>
                    @else
                        Rp {{ number_format($user->saldo, 0, ',', '.') }}
                    @endif
                </h2>
            </div>
        </div>
    </div>

    {{-- =============================================== --}}
    {{-- 2. FLOATING CARD (QR CODE) --}}
    {{-- =============================================== --}}
    <div class="px-5 -mt-24 relative z-20">
        <div
            class="w-full max-w-sm mx-auto bg-white rounded-[2rem] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] border border-white/60 overflow-hidden transform transition-all hover:-translate-y-1 duration-300">

            {{-- Card Header --}}
            <div
                class="bg-slate-50/80 backdrop-blur-sm p-5 text-center border-b border-slate-100 flex items-center justify-between px-8">
                <div class="text-left">
                    <h3 class="text-base font-bold text-slate-800">QR Code</h3>
                    <p class="text-[10px] text-slate-500">Scan untuk menerima transfer dari orang lain</p>
                </div>
                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                        </path>
                    </svg>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="p-8 bg-white flex flex-col items-center">
                @if($memberId)
                    {{-- QR Container --}}
                    <div class="relative group cursor-pointer" onclick="openQrModal()">
                        <div
                            class="absolute -inset-2 bg-gradient-to-tr from-blue-500 to-indigo-500 rounded-3xl blur opacity-25 group-hover:opacity-50 transition duration-500">
                        </div>
                        <div
                            class="relative bg-white p-4 rounded-2xl border-2 border-dashed border-slate-200 group-hover:border-blue-300 transition-colors">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ $memberId }}"
                                alt="QR Member"
                                class="w-40 h-40 object-contain mix-blend-multiply opacity-90 group-hover:opacity-100 transition">

                            {{-- Logo Tengah QR --}}
                            <div
                                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-1 rounded-full shadow-sm">
                                <div
                                    class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-[9px] shadow-inner">
                                    SI
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Member ID --}}
                    <div class="mt-6 w-full text-center">
                        <div class="inline-block bg-slate-50 border border-slate-200 rounded-xl px-4 py-2">
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-0.5">ID MEMBER</p>
                            <p class="font-mono text-xl font-bold text-slate-700 tracking-widest">{{ $memberId }}</p>
                        </div>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-6">
                        <div
                            class="w-16 h-16 bg-red-50 rounded-2xl rotate-3 flex items-center justify-center mx-auto mb-4 border border-red-100">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h4 class="text-slate-800 font-bold">Belum Ada ID</h4>
                        <p class="text-slate-500 text-xs mt-1">Hubungi admin untuk aktivasi.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- =============================================== --}}
    {{-- 3. ADVERTISING / PROMO SECTION (NEW!) --}}
    {{-- =============================================== --}}
    <div class="max-w-6xl mx-auto mt-10 px-6">

        {{-- Section Header --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Promo & Info</h3>
                <p class="text-xs text-slate-500">Penawaran menarik dari merchant sekolah</p>
            </div>
            {{-- Optional: Arrow buttons for desktop could go here --}}
        </div>

        {{-- Scroll Container --}}
        {{-- Logika Responsive:
        - Mobile: snap-x mandatory (biar berhenti pas di tengah gambar), lebar 100%
        - Desktop: lebar disesuaikan biar muat 3 --}}
        <div class="flex overflow-x-auto pb-6 -mx-4 px-4 sm:mx-0 sm:px-0 gap-4 sm:gap-6 snap-x snap-mandatory scrollbar-hide"
            style="scrollbar-width: none; -ms-overflow-style: none;">

            @forelse($ads as $ad)
                {{-- AD CARD --}}
                <div class="relative flex-shrink-0 snap-center w-full sm:w-[calc(50%-12px)] md:w-[calc(33.33%-16px)] group">
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden h-full flex flex-col hover:shadow-md transition-shadow duration-300">

                        {{-- Image Wrapper (Aspect Ratio 16:9 or similar) --}}
                        <div class="relative w-full aspect-[16/9] overflow-hidden bg-slate-100">
                            {{-- Badge Toko --}}
                            <div
                                class="absolute top-3 left-3 z-10 bg-white/90 backdrop-blur-md px-2 py-1 rounded-lg shadow-sm border border-white/50">
                                <span class="text-[10px] font-bold text-slate-700 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z">
                                        </path>
                                    </svg>
                                    {{ Str::limit($ad->store->nama_toko ?? 'Merchant', 15) }}
                                </span>
                            </div>

                            <img src="{{ asset('storage/ads/' . $ad->banner_image) }}" alt="Promo"
                                class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700 ease-out">

                            {{-- Gradient Overlay (Supaya teks putih terbaca) --}}
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-80">
                            </div>

                            {{-- Teks di dalam gambar (bawah) --}}
                            <div class="absolute bottom-3 left-3 right-3">
                                <p class="text-white text-xs font-medium line-clamp-2">
                                    {{-- Bisa ambil deskripsi toko atau teks default --}}
                                    Kunjungi {{ $ad->store->nama_toko }} sekarang!
                                </p>
                            </div>
                        </div>

                        {{-- Action Button (Optional) --}}
                        {{-- <div class="p-3 bg-white border-t border-slate-50">
                            <button
                                class="w-full py-2 text-xs font-bold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                Lihat Toko
                            </button>
                        </div> --}}
                    </div>
                </div>
            @empty
                {{-- STATE KOSONG (Skeleton / Default Info) --}}
                <div
                    class="w-full flex flex-col items-center justify-center py-10 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
                    <p class="text-slate-400 font-medium text-sm">Belum ada promo aktif saat ini.</p>
                </div>
            @endforelse

        </div>
    </div>

    <x-modal-my-qr />

    {{-- Style Tambahan untuk Hide Scrollbar (jika class tailwind scrollbar-hide tdk jalan) --}}
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</div>