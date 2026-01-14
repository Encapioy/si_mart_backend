<div class="min-h-screen bg-gray-50 pb-20 relative font-sans">

    <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 pt-12 pb-32 rounded-b-[3rem] shadow-lg overflow-hidden">

        <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20 pointer-events-none">
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-blue-400 rounded-full blur-3xl mix-blend-overlay"></div>
            <div class="absolute top-1/2 right-0 w-40 h-40 bg-white rounded-full blur-2xl mix-blend-overlay"></div>
        </div>

        <div class="relative z-10 text-center px-6">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-3 py-1 rounded-full border border-white/20 mb-2">
                <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-xs font-medium text-blue-50 tracking-wide uppercase">{{ $user->nama_lengkap }}</span>
            </div>

            <p class="text-blue-200 text-sm mb-1 font-medium">Total Saldo Aktif</p>
            <h2 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-sm">
                <span class="text-2xl font-semibold align-top opacity-80">Rp</span>{{ number_format($user->saldo, 0, ',', '.') }}
            </h2>
        </div>
    </div>

    <div class="px-4 -mt-24 relative z-20">
        <div class="w-full max-w-sm mx-auto bg-white rounded-3xl shadow-2xl border border-white/50 overflow-hidden transform transition-all hover:scale-[1.01]">

            <div class="bg-gray-50/50 p-6 text-center border-b border-gray-100">
                <h3 class="text-lg font-bold text-slate-800">QR Code Saya</h3>
                <p class="text-xs text-slate-500 mt-1">Tunjukkan kode ini untuk menerima saldo</p>
            </div>

            <div class="p-8 flex flex-col items-center justify-center bg-white relative">

                @if($memberId)
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-500"></div>

                        <div class="relative bg-white p-3 rounded-2xl border-2 border-dashed border-gray-200 shadow-sm">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ $memberId }}"
                                alt="QR Member {{ $memberId }}"
                                class="w-full max-w-[200px] h-auto rounded-lg mix-blend-multiply">
                        </div>

                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-1 rounded-full shadow-md border border-gray-100">
                            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-[10px]">
                                SI
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 w-full text-center">
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-semibold mb-2">Nomor Member ID</p>

                        <div class="bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 flex items-center justify-between gap-3 group hover:border-blue-200 transition">
                            <span class="font-mono text-xl sm:text-2xl font-bold text-slate-700 tracking-wider w-full text-center group-hover:text-blue-600 transition">
                                {{ $memberId }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 mt-4">
                            <button onclick="openQrModal()"
                                class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-blue-50 text-blue-600 font-semibold text-sm hover:bg-blue-100 hover:shadow-sm transition active:scale-95">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
            </path>
        </svg>
                                Download Kode Qr
                            </button>
                        </div>
                    </div>

                @else
                    <div class="text-center py-10">
                        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h4 class="text-slate-800 font-bold text-lg">ID Tidak Ditemukan</h4>
                        <p class="text-slate-500 text-sm mt-2 px-6 leading-relaxed">
                            Akun Anda belum memiliki Member ID. Silakan hubungi admin sekolah.
                        </p>
                    </div>
                @endif
            </div>

            <div class="h-2 w-full bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-500"></div>
        </div>

        <div class="text-center mt-6">
            <p class="text-xs text-slate-400">
                QR Code ini diperbarui secara otomatis dan aman digunakan.
            </p>
        </div>
    </div>
    <x-modal-my-qr />
</div>