<div class="min-h-screen bg-slate-900 text-white flex flex-col p-6">
    <div class="bg-slate-800 p-4 rounded-xl flex items-center space-x-3 mb-8 border border-slate-700">
        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center font-bold text-white">
            {{ substr($store->name, 0, 1) }}
        </div>
        <div>
            <h3 class="font-bold text-sm">{{ $store->name }}</h3>
            <p class="text-xs text-green-400 flex items-center">
                <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span> Verified Merchant
            </p>
        </div>
    </div>

    <div class="flex-1 flex flex-col items-center justify-center">
        <p class="text-slate-400 text-sm mb-2">Masukan Nominal</p>
        <div class="flex items-end">
            <span class="text-2xl font-bold mr-2 text-slate-500">Rp</span>
            <input wire:model="amount" type="number"
                class="bg-transparent text-5xl font-bold w-full text-center outline-none placeholder-slate-700"
                placeholder="0">
        </div>
        @error('amount') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
    </div>

    <div class="mt-auto space-y-4">
        <input wire:model="note" type="text"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white outline-none focus:border-green-500"
            placeholder="Catatan (Opsional)...">

        <input wire:model="pin" type="password"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white outline-none focus:border-green-500 text-center tracking-widest"
            placeholder="MASUKAN PIN">
        @error('pin') <span class="text-red-500 text-xs text-center block">{{ $message }}</span> @enderror

        <button wire:click="processPayment"
            class="w-full bg-slate-200 text-slate-900 font-bold py-4 rounded-full hover:bg-white transition">
            <span wire:loading.remove>BAYAR SEKARANG</span>
            <span wire:loading>PROSES...</span>
        </button>

        <a href="{{ route('dashboard') }}" class="block text-center text-slate-500 text-sm">Batal</a>
    </div>
</div>