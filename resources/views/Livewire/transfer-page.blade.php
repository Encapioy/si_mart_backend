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

    <form wire:submit.prevent="processTransfer"
    x-data="{
          isProcessing: false,
          nominal: @entangle('amount'), // Sambungkan ke Livewire

          // Logic Format Rupiah
          formatRupiah(angka) {
              if (!angka) return '';
              return angka.toString().replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
          },

          // Logic Saat Ngetik
          handleInput(e) {
              let rawValue = e.target.value.replace(/\./g, '');
              this.nominal = rawValue;
              e.target.value = this.formatRupiah(rawValue);
          },

          // Logic Init (Load data awal)
          init() {
              if(this.nominal) {
                  $refs.inputField.value = this.formatRupiah(this.nominal);
              }
          }
      }"
      {{-- Saat form di-submit, kunci tombol --}}
      @submit="isProcessing = true">

        <div class="flex flex-col items-center justify-center mt-6">
            <label class="text-slate-400 text-xs font-bold tracking-widest mb-4">MAU KIRIM BERAPA?</label>

            <div class="flex items-center justify-center w-full relative">
                <span
                    class="text-3xl font-bold text-slate-300 absolute left-8 md:left-auto md:-ml-32 pointer-events-none">Rp</span>

                {{-- INPUT NOMINAL --}}
                <input x-ref="inputField" @input="handleInput" type="text" inputmode="numeric" autofocus
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
                <input wire:model="pin" type="password" inputmode="numeric" maxlength="6"
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-4 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition text-center font-bold placeholder-slate-300 shadow-sm"
                    placeholder="MASUKAN PIN(6 DIGIT)">
                @error('pin')
                    <span class="text-red-500 text-xs text-center block mt-2 font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" {{-- 1. Matikan tombol jika sedang processing (Alpine) atau Loading (Livewire) --}}
                x-bind:disabled="isProcessing" wire:loading.attr="disabled" {{-- 2. Trigger Submit Manual (Biar aman) --}}
                x-on:click="$dispatch('submit')" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl transition shadow-lg shadow-blue-500/20 active:scale-[0.98]
                               disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-blue-400">

                {{-- STATE NORMAL (Muncul jika TIDAK processing) --}}
                <span x-show="!isProcessing" wire:loading.remove target="processTransfer">
                    KIRIM UANG SEKARANG
                </span>

                {{-- STATE LOADING (Muncul jika processing) --}}
                <span x-show="isProcessing" wire:loading.flex target="processTransfer"
                    class="flex items-center justify-center gap-2" style="display: none;">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
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

        {{-- HANDLING ERROR (PENTING) --}}
        {{-- Jika validasi gagal (misal PIN salah), tombol harus bisa dipencet lagi --}}
        @script
        <script>
            // Tangkap jika request Livewire selesai tapi gagal (misal validasi error)
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, content, preventDefault }) => {
                    // Cari elemen x-data utama dan reset isProcessing
                    // Kita gunakan querySelector pada form ini
                    let formEl = document.querySelector('[x-data*="isProcessing"]');
                    if (formEl) {
                        formEl.__x.$data.isProcessing = false;
                    }
                })
            });
        </script>
        @endscript
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