<div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col p-6">

    <div class="bg-white p-4 rounded-xl flex items-center space-x-3 mb-8 border border-slate-200 shadow-sm">
        <div
            class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-lg">
            {{ substr($store->nama_toko ?? 'T', 0, 1) }}
        </div>
        <div>
            <h3 class="font-bold text-sm text-slate-800">{{ $store->nama_toko ?? 'Nama Toko' }}</h3>
            <p
                class="text-[10px] text-green-600 flex items-center bg-green-50 px-2 py-0.5 rounded-full w-fit mt-1 border border-green-100">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1 animate-pulse"></span> Verified Merchant
            </p>
        </div>
    </div>

    {{-- INFORMASI SALDO PENGIRIM --}}
    <div class="flex justify-end mb-2 -mt-4">
        <div
            class="bg-slate-200/50 border border-slate-200 backdrop-blur-sm px-3 py-1.5 rounded-lg flex items-center gap-2">
            <div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fa-solid fa-wallet text-[10px] text-emerald-600"></i>
            </div>
            <div class="text-xs text-slate-500 font-medium">
                Saldo: <span class="text-slate-800 font-bold font-mono">Rp
                    {{ number_format(auth()->user()->saldo, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="isProcessing = true; $wire.processPayment().finally(() => { isProcessing = false })"
        class="flex-1 flex flex-col" x-data="{
            isProcessing: false,
            nominal: @entangle('amount'),

            // Logic Format Rupiah
            formatRupiah(angka) {
                if (!angka) return '';
                return angka.toString().replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },

            // Logic Saat Ngetik
            handleInput(e) {
                // 1. Hapus titik dulu
                let rawValue = e.target.value.replace(/\./g, '');

                // 2. [BARU] Batasi Maksimal 15 Digit (Agar tidak error overflow)
                if (rawValue.length > 15) {
                    rawValue = rawValue.substring(0, 15);
                }

                // 3. Update state
                this.nominal = rawValue;
                e.target.value = this.formatRupiah(rawValue);
            },

            // Logic Init
            init() {
                if (this.nominal) {
                    $refs.inputField.value = this.formatRupiah(this.nominal);
                }
            }
        }">

        <div class="flex-1 flex flex-col items-center justify-center">

            <p class="text-slate-400 text-xs font-bold tracking-widest mb-4">MASUKKAN NOMINAL</p>

            {{-- [PERBAIKAN UI] --}}
            {{-- Menggunakan Flexbox items-baseline agar Rp dan Angka sejajar rapi & tidak tabrakan --}}
            <div class="flex items-baseline justify-center w-full gap-1">
                <span class="text-3xl font-bold text-slate-400">Rp</span>

                {{-- Input dibuat text-left tapi width-nya menyesuaikan (atau tetap center tapi flow flex) --}}
                {{-- Kita gunakan approach 'w-auto' dengan 'min-w-[50px]' agar input terlihat menyatu --}}
                <input x-ref="inputField" @input="handleInput" type="text" inputmode="numeric" autofocus
                    class="bg-transparent text-5xl font-bold text-slate-800 outline-none placeholder-slate-200 no-spin w-full text-center focus:ring-0 border-none p-0"
                    placeholder="0">
            </div>

            @error('amount')
                <div
                    class="mt-4 bg-red-50 text-red-500 px-4 py-2 rounded-lg text-xs font-bold border border-red-100 animate-pulse">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mt-auto space-y-4 pb-4">

            <div class="relative">
                <input wire:model="pin" type="password" inputmode="numeric" maxlength="6"
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-4 text-sm text-slate-800 outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition text-center font-bold placeholder-slate-300 shadow-sm"
                    placeholder="MASUKAN PIN (6 DIGIT)">
                @error('pin')
                    <span class="text-red-500 text-xs text-center block mt-2 font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" x-bind:disabled="isProcessing" wire:loading.attr="disabled"
                wire:target="processPayment" class="w-full bg-green-500 text-white font-bold py-4 rounded-xl hover:bg-green-600 transition shadow-lg shadow-green-500/20 active:scale-[0.98]
                               disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-green-400">

                <span x-show="!isProcessing" wire:loading.remove wire:target="processPayment">
                    BAYAR SEKARANG
                </span>

                <span x-show="isProcessing" wire:loading.flex wire:target="processPayment"
                    class="flex items-center justify-center gap-2" style="display: none;">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    MEMPROSES...
                </span>
            </button>

            <a href="{{ route('dashboard') }}"
                class="block w-full text-center py-3.5 rounded-xl bg-red-50 text-red-600 font-bold text-sm tracking-wide hover:bg-red-100 hover:text-red-700 transition-all duration-200 active:scale-[0.98]">
                BATALKAN TRANSAKSI
            </a>
        </div>
    </form>

    <style>
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