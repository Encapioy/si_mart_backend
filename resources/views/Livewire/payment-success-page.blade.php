<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 px-6 sm:px-8">
    <div class="relative w-full max-w-sm mx-auto bg-white rounded-3xl shadow-xl overflow-hidden">

        <div class="bg-green-500 pt-10 pb-16 text-center relative">
            <div
                class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg animate-bounce">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-white text-2xl font-bold tracking-wide">Pembayaran Berhasil!</h2>
            <p class="text-green-100 text-sm mt-1">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
        </div>

        <div class="px-8 py-8 -mt-8 bg-white rounded-t-[2rem] relative z-10">

            <div class="text-center mb-8 border-b border-dashed border-gray-200 pb-6">
                <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-2">Penerima Dana</p>
                <h3 class="text-xl font-bold text-slate-800">
                    {{ $transaction->store->nama_toko ?? 'Transfer Sesama User' }}
                </h3>
                @if(isset($transaction->store))
                    <p class="text-sm text-gray-500 mt-1">Merchant / Toko</p>
                @endif
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 text-sm">Nominal Bayar</span>
                    <span class="font-bold text-slate-800">Rp
                        {{ number_format($transaction->total_bayar, 0, ',', '.') }}</span>
                </div>

                <div class="h-px bg-gray-100 my-2"></div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-800 font-bold text-lg">Total</span>
                    <span class="text-green-600 font-extrabold text-xl">Rp
                        {{ number_format($transaction->total_bayar, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="mt-8 bg-gray-50 p-4 rounded-xl border border-gray-100 text-center">
                <p class="text-[10px] text-gray-400 uppercase mb-1">Kode Referensi</p>
                <p class="font-mono text-sm font-bold text-slate-600 tracking-wider select-all">
                    {{ $transaction->transaction_code }}
                </p>
            </div>

            <div class="mt-8">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-200 text-center transition transform active:scale-[0.98]">
                    Kembali ke Beranda
                </a>
            </div>

        </div>

        <div class="h-4 bg-gray-50 w-full"
            style="background-image: radial-gradient(circle, transparent 50%, #f9fafb 50%); background-size: 20px 20px; background-position: 0 10px;">
        </div>
    </div>
</div>