<div class="min-h-screen bg-slate-100 pb-20 relative">
    <div class="bg-blue-600 px-6 pt-10 pb-20 rounded-b-[3rem] shadow-xl text-center text-white">
        <p class="text-blue-200 text-sm">Total Saldo Kamu</p>
        <h2 class="text-4xl font-extrabold mt-1">Rp {{ number_format($user->saldo, 0, ',', '.') }}</h2>
        <p class="mt-4 text-sm font-medium bg-blue-500/50 inline-block px-3 py-1 rounded-full">
            {{ $user->nama_lengkap }}
        </p>
    </div>

    <div class="px-6 -mt-12">
        <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col items-center">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">QR Identitas Saya</span>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $qrCode }}"
                class="w-40 h-40 mix-blend-multiply">
            <p class="mt-4 text-xs text-slate-400 text-center">Tunjukkan ke Admin Keuangan<br>untuk Top Up Saldo</p>
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