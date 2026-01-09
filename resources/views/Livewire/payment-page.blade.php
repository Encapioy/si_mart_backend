<div class="min-h-screen bg-slate-900 text-white flex flex-col p-6">

    <div class="bg-slate-800 p-4 rounded-xl flex items-center space-x-3 mb-8 border border-slate-700 shadow-lg">
        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center font-bold text-white text-lg shadow-green-500/50 shadow-md">
            {{ substr($store->name ?? 'T', 0, 1) }}
        </div>
        <div>
            <h3 class="font-bold text-sm text-slate-200">{{ $store->name ?? 'Nama Toko' }}</h3>
            <p class="text-[10px] text-green-400 flex items-center bg-green-400/10 px-2 py-0.5 rounded-full w-fit mt-1">
                <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1 animate-pulse"></span> Verified Merchant
            </p>
        </div>
    </div>

    <form wire:submit="processPayment" class="flex-1 flex flex-col">

        <div class="flex-1 flex flex-col items-center justify-center">
            <p class="text-slate-400 text-sm mb-4 font-medium tracking-wide">MASUKKAN NOMINAL</p>

            <div class="flex items-center justify-center w-full relative">
                <span class="text-3xl font-bold text-slate-500 absolute left-4 md:left-auto md:-ml-32">Rp</span>

                <input wire:model="amount"
                       type="number"
                       inputmode="numeric"
                       autofocus
                       class="bg-transparent text-5xl font-bold w-full text-center outline-none placeholder-slate-700 text-white no-spin"
                       placeholder="0">
            </div>

            @error('amount')
                <div class="mt-4 bg-red-500/10 text-red-500 px-4 py-2 rounded-lg text-xs font-bold border border-red-500/20">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mt-auto space-y-4 pb-4">

            <div class="relative">
                <input wire:model="note" type="text"
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-4 text-sm text-white outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition placeholder-slate-500"
                    placeholder="Catatan (Opsional)...">
            </div>

            <div class="relative">
                <input wire:model="pin" type="password" inputmode="numeric" maxlength="6"
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-4 text-sm text-white outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition text-center tracking-[0.5em] font-bold placeholder-slate-500"
                    placeholder="••••••">
                @error('pin')
                    <span class="text-red-400 text-xs text-center block mt-2 font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-green-500 text-slate-900 font-bold py-4 rounded-xl hover:bg-green-400 transition shadow-lg shadow-green-500/20 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove>BAYAR SEKARANG</span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-slate-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    MEMPROSES...
                </span>
            </button>

            <a href="{{ route('dashboard') }}" class="block text-center text-slate-500 text-xs font-medium hover:text-white transition py-2">
                BATALKAN TRANSAKSI
            </a>
        </div>
    </form>
</div>

<style>
    /* Chrome, Safari, Edge, Opera */
    .no-spin::-webkit-outer-spin-button,
    .no-spin::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    /* Firefox */
    .no-spin {
        -moz-appearance: textfield;
    }
</style>