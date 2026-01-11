<div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col p-6">

    <div class="bg-white p-4 rounded-xl flex items-center space-x-3 mb-8 border border-slate-200 shadow-sm">
        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-lg">
            {{ substr($targetUser->nama_lengkap ?? 'U', 0, 1) }}
        </div>
        <div>
            <h3 class="font-bold text-sm text-slate-800">{{ $targetUser->nama_lengkap ?? 'Nama User' }}</h3>
            <p class="text-[10px] text-slate-500 flex items-center mt-0.5 font-mono bg-slate-100 px-2 py-0.5 rounded w-fit">
                ID: {{ $targetUser->member_id }}
            </p>
        </div>
    </div>

    <form wire:submit.prevent="processTransfer" class="flex-1 flex flex-col">

        <div class="flex-1 flex flex-col items-center justify-center">
            <p class="text-slate-400 text-xs font-bold tracking-widest mb-4">MASUKKAN NOMINAL</p>

            <div class="flex items-center justify-center w-full relative">
                <span class="text-3xl font-bold text-slate-300 absolute left-8 md:left-auto md:-ml-32 pointer-events-none">Rp</span>

                <input wire:model="amount"
                       type="number"
                       inputmode="numeric"
                       autofocus
                       class="bg-transparent text-5xl font-bold w-full text-center outline-none placeholder-slate-200 text-slate-800 no-spin pl-8 md:pl-0"
                       placeholder="0">
            </div>

            @error('amount')
                <div class="mt-4 bg-red-50 text-red-500 px-4 py-2 rounded-lg text-xs font-bold border border-red-100 animate-pulse">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mt-auto space-y-4 pb-4">

            <div class="relative">
                <input wire:model="note" type="text"
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-4 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition placeholder-slate-400 shadow-sm"
                    placeholder="Catatan transfer (Opsional)...">
            </div>

            <div class="relative">
                <input wire:model="pin" type="password" inputmode="numeric" maxlength="6"
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-4 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition text-center tracking-[0.5em] font-bold placeholder-slate-300 shadow-sm"
                    placeholder="••••••">
                @error('pin')
                    <span class="text-red-500 text-xs text-center block mt-2 font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit"
                wire:loading.attr="disabled"
                class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/20 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">

                <span wire:loading.remove>KIRIM UANG SEKARANG</span>

                <span wire:loading class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    MEMPROSES...
                </span>
            </button>

            <a href="{{ route('dashboard') }}" class="block text-center text-slate-400 text-xs font-medium hover:text-slate-600 transition py-2">
                BATALKAN TRANSAKSI
            </a>
        </div>
    </form>

    <style>
    /* Utility untuk hilangkan spinner input number */
    .no-spin::-webkit-outer-spin-button,
    .no-spin::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .no-spin {
        -moz-appearance: textfield;
    }
    </style>

</div>