<div class="min-h-screen bg-slate-100 pb-20 relative">
    <div class="bg-blue-600 px-6 pt-10 pb-20 rounded-b-[3rem] shadow-xl text-center text-white">
        <p class="text-blue-200 text-sm">Total Saldo Kamu</p>
        <h2 class="text-4xl font-extrabold mt-1">Rp {{ number_format($user->saldo, 0, ',', '.') }}</h2>
        <p class="mt-4 text-sm font-medium bg-blue-500/50 inline-block px-3 py-1 rounded-full">
            {{ $user->nama_lengkap }}
        </p>
    </div>

    <div class="w-full flex justify-center p-4 md:p-6">
    <div class="w-full max-w-xs sm:max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100 transition-all duration-300 hover:shadow-2xl">

        <div class="bg-blue-600 p-4 text-center">
            <h3 class="text-white font-bold text-base sm:text-lg">QR Code Saya</h3>
            <p class="text-blue-100 text-[10px] sm:text-xs">Tunjukkan ke teman untuk terima saldo</p>
        </div>

        <div class="p-6 sm:p-8 flex flex-col items-center">
            @if($memberId)
                <div class="p-2 bg-white rounded-lg border-2 border-slate-100 shadow-sm mb-4 sm:mb-6">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ $memberId }}"
                         alt="QR Member {{ $memberId }}"
                         class="w-full max-w-[180px] sm:max-w-[250px] h-auto rounded-md">
                </div>

                <p class="text-slate-500 text-xs sm:text-sm font-medium mb-1">Nomor Member:</p>

                <div class="bg-slate-100 px-4 py-2 sm:px-6 sm:py-3 rounded-lg w-full text-center">
                    <span class="text-lg sm:text-2xl font-mono font-bold text-slate-800 tracking-widest break-all">
                        {{ $memberId }}
                    </span>
                </div>

                <button onclick="navigator.clipboard.writeText('{{ $memberId }}'); alert('ID berhasil disalin!')"
                        class="mt-4 text-xs text-blue-500 hover:text-blue-700 font-medium flex items-center gap-1 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    Salin ID
                </button>

            @else
                <div class="text-center py-6 sm:py-8">
                    <div class="bg-red-50 p-3 rounded-full inline-block mb-3">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-red-500 font-bold text-sm sm:text-base">Member ID Belum Ada</p>
                    <p class="text-slate-500 text-xs mt-1 px-4">Silakan hubungi admin sekolah untuk generate ID Anda.</p>
                </div>
            @endif
        </div>
    </div>
</div>

    <div class="fixed bottom-8 left-0 right-0 px-6 flex justify-center z-50">
        <a href="{{ route('scan') }}"
            class="flex items-center space-x-2 bg-slate-900 text-white px-8 py-4 rounded-full shadow-2xl hover:scale-105 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                </path>
            </svg>
            <span class="font-bold">SCAN BAYAR</span>
        </a>
    </div>
</div>