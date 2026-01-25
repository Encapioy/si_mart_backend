<div
    class="min-h-screen bg-slate-50 flex items-center justify-center p-4 font-sans text-slate-800 relative overflow-hidden">

    {{-- Background Decoration --}}
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-slate-200 to-slate-50 -z-10"></div>
    <div
        class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob">
    </div>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }
    </style>

    <div
        class="bg-white w-full max-w-md rounded-3xl shadow-xl shadow-slate-200/50 border border-white overflow-visible relative z-10">

        <div class="px-8 pt-8 pb-4 text-center">
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Top Up Saldo User</h1>
            {{-- Menampilkan info Kasir yang sedang login --}}
            <p class="text-slate-500 text-sm mt-1 flex items-center justify-center gap-1">
                <i class="fa-solid fa-user-shield text-emerald-500"></i>
                Kasir: <span
                    class="font-bold text-slate-700">{{ Auth::guard('admin')->user()->nama_lengkap ?? 'Unknown' }}</span>
            </p>
        </div>

        <div class="px-8 pb-8">
            <form wire:submit.prevent="triggerConfirm" autocomplete="off" class="space-y-5">

                {{-- 1. PENCARIAN SISWA (TETAP SAMA) --}}
                <div class="relative z-50">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Cari Akun
                        User</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <div wire:loading.remove wire:target="search">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                            </div>
                            <div wire:loading wire:target="search" style="display: none;">
                                <i class="fa-solid fa-circle-notch fa-spin text-emerald-500"></i>
                            </div>
                        </div>

                        <input type="text" wire:model.live.debounce.400ms="search"
                            class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all outline-none placeholder:text-slate-400"
                            placeholder="Ketik nama / username / email / no handphone" autofocus>

                        {{-- Hasil Pencarian --}}
                        @if(!empty($usersFound))
                            <div
                                class="absolute top-full left-0 w-full mt-2 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scroll z-50 ring-1 ring-black/5">
                                @foreach($usersFound as $u)
                                    <div wire:click="selectUser({{ $u->id }})"
                                        class="px-4 py-3 hover:bg-emerald-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors flex justify-between items-center group">
                                        <div>
                                            <p class="font-bold text-slate-700 text-sm group-hover:text-emerald-700">
                                                {{ $u->nama_lengkap }}</p>
                                            <p class="text-xs text-slate-400">{{ $u->username }}</p>
                                        </div>
                                        <i
                                            class="fa-solid fa-check text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @error('search') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- 2. NOMINAL (TETAP SAMA - Logic Alpine tidak diganggu) --}}
                <div x-data="{
                    nominal: @entangle('amount'),
                    formatRupiah(angka) {
                        if (!angka) return '';
                        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    },
                    handleInput(e) {
                        let rawValue = e.target.value.replace(/[^0-9]/g, '');
                        this.nominal = rawValue;
                        e.target.value = this.formatRupiah(rawValue);
                    },
                    init() {
                        this.$watch('nominal', (value) => {
                            if (this.$refs.inputNominal.value.replace(/\./g, '') !== value) {
                                this.$refs.inputNominal.value = this.formatRupiah(value);
                            }
                        });
                    }
                }">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nominal</label>
                    <div class="relative">
                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">Rp</span>
                        <input type="text" x-ref="inputNominal" @input="handleInput" :value="formatRupiah(nominal)"
                            inputmode="numeric"
                            class="block w-full pl-12 pr-4 py-3.5 bg-white border-2 border-slate-100 rounded-xl text-xl font-extrabold text-slate-800 focus:ring-0 focus:border-emerald-500 transition-colors outline-none placeholder:text-slate-200"
                            placeholder="0">
                    </div>

                    {{-- Quick Buttons --}}
                    <div class="flex gap-2 mt-2">
                        @foreach([10000, 20000, 50000, 100000] as $amt)
                            <button type="button" wire:click="$set('amount', '{{ $amt }}')"
                                class="flex-1 py-2 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 hover:bg-emerald-500 hover:text-white transition-colors">
                                {{ number_format($amt / 1000, 0) }}k
                            </button>
                        @endforeach
                    </div>
                    @error('amount') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- 3. VERIFIKASI KASIR (UBAH: Hapus Dropdown, Sisa PIN Saja) --}}
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block text-center">
                        KONFIRMASI PIN ANDA
                    </label>

                    <div class="relative max-w-[200px] mx-auto">
                        <input type="password" wire:model="cashier_pin" maxlength="6" inputmode="numeric"
                            pattern="[0-9]*"
                            class="w-full bg-white border border-slate-200 rounded-lg py-3 text-center text-lg font-bold tracking-[0.5em] focus:border-emerald-500 outline-none placeholder:text-slate-200 shadow-sm"
                            placeholder="••••••">
                    </div>

                    @error('cashier_pin')
                        <span
                            class="text-red-500 text-xs text-center block font-bold bg-red-50 py-1 rounded-lg mt-2 animate-pulse">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- 4. TOMBOL SUBMIT --}}
                <button type="submit" wire:loading.attr="disabled" wire:target="triggerConfirm"
                    class="w-full relative bg-slate-900 hover:bg-black text-white font-bold py-4 rounded-xl shadow-lg transition-all active:scale-[0.98]">

                    <div wire:loading.remove wire:target="triggerConfirm"
                        class="flex items-center justify-center gap-2">
                        <span>PROSES SEKARANG</span>
                    </div>

                    <div wire:loading.class.remove="hidden" wire:target="triggerConfirm"
                        class="hidden flex items-center justify-center gap-2" style="display: none;">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>MEMVERIFIKASI...</span>
                    </div>
                </button>

            </form>
        </div>
    </div>

    {{-- Script SweetAlert (TETAP SAMA) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @script
    <script>
        document.addEventListener('livewire:initialized', () => {
            const swalConfig = Swal.mixin({
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-slate-800',
                    cancelButton: 'bg-red-50 text-red-500 px-6 py-2.5 rounded-xl font-bold hover:bg-red-100'
                },
                buttonsStyling: false
            });

            Livewire.on('show-confirmation-modal', (event) => {
                swalConfig.fire({
                    title: 'Konfirmasi Top Up?',
                    html: `
                        <div class="bg-slate-50 p-3 rounded-lg text-sm mb-2">
                            <div class="flex justify-between mb-1">
                                <span class="text-slate-500">Kasir:</span>
                                <span class="font-bold">${event.cashier_name}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Penerima:</span>
                                <span class="font-bold">${event.username}</span>
                            </div>
                        </div>
                        <h2 class="text-2xl font-bold text-emerald-600">Rp ${new Intl.NumberFormat('id-ID').format(event.amount)}</h2>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'YA, PROSES',
                    cancelButtonText: 'BATAL',
                    reverseButtons: true,
                    focusConfirm: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.processTopUp();
                    }
                });
            });

            Livewire.on('show-success', (event) => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                Toast.fire({ icon: 'success', title: event.message });
            });

            Livewire.on('show-error', (event) => swalConfig.fire('Gagal', event.message, 'error'));
        });
    </script>
    @endscript
</div>