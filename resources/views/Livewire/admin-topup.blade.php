<div class="min-h-screen bg-slate-50 flex items-center justify-center p-4 font-sans text-slate-800 relative overflow-hidden">

    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-slate-200 to-slate-50 -z-10"></div>
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Custom Scrollbar untuk dropdown */
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    </style>

    <div class="bg-white w-full max-w-md rounded-3xl shadow-xl shadow-slate-200/50 border border-white overflow-visible relative z-10">

        <div class="px-8 pt-8 pb-4 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 mb-4 shadow-sm">
                <i class="fa-solid fa-wallet text-xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Top Up Saldo</h1>
            <p class="text-slate-500 text-sm mt-1">Isi saldo siswa dengan aman & cepat.</p>
        </div>

        <div class="px-8 pb-8">
            <form wire:submit.prevent="triggerConfirm" autocomplete="off" class="space-y-6">

                <div class="relative z-50">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Target Penerima</label>

                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i>
                        </div>
                        <input type="text"
                            wire:model.live.debounce.300ms="search"
                            class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all outline-none placeholder:text-slate-400"
                            placeholder="Ketik nama atau username siswa..."
                        >

                        @if(!empty($usersFound))
                            <div class="absolute top-full left-0 w-full mt-2 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scroll z-50 ring-1 ring-black/5">
                                @foreach($usersFound as $u)
                                    <div wire:click="selectUser({{ $u->id }})"
                                        class="px-4 py-3 hover:bg-emerald-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors group/item">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-bold text-slate-700 text-sm group-hover/item:text-emerald-700">{{ $u->nama_lengkap }}</p>
                                                <p class="text-xs text-slate-400 font-mono mt-0.5">@ {{ $u->username }}</p>
                                            </div>
                                            <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover/item:text-emerald-400"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(strlen($search) > 1 && empty($usersFound) && !$selectedUser)
                            <div class="absolute top-full left-0 w-full mt-2 bg-white border border-red-100 rounded-xl shadow-lg p-4 text-center z-50">
                                <i class="fa-regular fa-circle-xmark text-red-400 mb-1"></i>
                                <p class="text-xs text-red-500 font-medium">Siswa tidak ditemukan.</p>
                            </div>
                        @endif
                    </div>
                    @error('search') <span class="text-red-500 text-xs mt-1.5 ml-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</span> @enderror
                </div>

                <div>
                    <div class="flex justify-between items-end mb-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Nominal Top Up</label>
                        <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">Minimal 1k</span>
                    </div>

                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">Rp</span>
                        <input type="number"
                            wire:model="amount"
                            class="block w-full pl-12 pr-4 py-4 bg-white border-2 border-slate-100 rounded-2xl text-xl font-extrabold text-slate-800 focus:ring-0 focus:border-emerald-500 transition-colors outline-none placeholder:text-slate-200"
                            placeholder="0" min="1000"
                        >
                    </div>
                    @error('amount') <span class="text-red-500 text-xs mt-1.5 ml-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</span> @enderror

                    <div class="grid grid-cols-3 gap-3 mt-3">
                        @foreach([100000, 150000, 200000] as $amt)
                        <button type="button" wire:click="setAmount({{ $amt }})"
                            class="py-2.5 px-2 rounded-xl text-xs font-bold border border-emerald-100 bg-emerald-50/50 text-emerald-600 hover:bg-emerald-500 hover:text-white hover:shadow-md hover:shadow-emerald-200 transition-all duration-200 transform active:scale-95">
                            {{ number_format($amt/1000, 0) }}k
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-shield-halved text-slate-400 text-xs"></i>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Verifikasi Kasir</span>
                    </div>

                    <div class="space-y-4">
                        <div class="relative">
                            <select wire:model="cashier_id"
                                class="block w-full bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 font-medium appearance-none cursor-pointer hover:border-slate-300 transition-colors outline-none">
                                <option value="" selected>Pilih Nama Anda...</option>
                                @foreach($cashiers as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_lengkap }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                            @error('cashier_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <input type="password" wire:model="cashier_pin" maxlength="6"
                                class="block w-full bg-white border border-slate-200 rounded-xl px-3 py-3 text-sm font-bold text-center tracking-[0.5em] focus:ring-2 focus:ring-slate-200 focus:border-slate-400 transition-all outline-none placeholder:tracking-normal placeholder:font-normal"
                                placeholder="Masukan 6 Digit PIN">
                            @error('cashier_pin') <span class="text-red-500 text-xs mt-1 block text-center">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="triggerConfirm"
                    class="w-full group relative bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-slate-900/20 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden">

                    <div wire:loading wire:target="triggerConfirm"
                         class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center rounded-xl z-20 transition-all duration-300">

                        <div class="flex items-center gap-3">
                            <div class="relative w-5 h-5">
                                <div class="absolute inset-0 border-2 border-slate-600 rounded-full opacity-20"></div>
                                <div class="absolute inset-0 border-2 border-white rounded-full border-t-transparent animate-spin"></div>
                            </div>

                            <span class="text-sm font-bold tracking-widest text-white animate-pulse">VERIFIKASI...</span>
                        </div>

                    </div>

                    <div wire:loading.remove wire:target="triggerConfirm" class="flex items-center justify-center gap-2">
                        <span>PROSES TOP UP</span>
                        <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </button>

            </form>
        </div>
    </div>

    <div class="absolute bottom-4 text-center">
        <p class="text-[10px] text-slate-400 font-medium opacity-60">Protected Transaction System v2.0</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @script
    <script>
        document.addEventListener('livewire:initialized', () => {

            // Konfigurasi SweetAlert Default yang lebih modern
            const swalConfig = Swal.mixin({
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg hover:bg-slate-800 focus:ring-0',
                    cancelButton: 'bg-red-50 text-red-500 px-6 py-2.5 rounded-xl font-bold hover:bg-red-100 focus:ring-0'
                },
                buttonsStyling: false
            });

            // 1. Event Konfirmasi
            Livewire.on('show-confirmation-modal', (event) => {
                const username = event.username;
                const amount = event.amount;
                const cashier_name = event.cashier_name;

                swalConfig.fire({
                    title: '<span class="text-xl font-bold text-slate-800">Konfirmasi Top Up</span>',
                    html: `
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mt-2 mb-4 text-left">
                            <div class="flex justify-between mb-2">
                                <span class="text-xs text-slate-400 uppercase font-bold">Penerima</span>
                                <span class="text-sm font-bold text-slate-800">${username}</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-xs text-slate-400 uppercase font-bold">Nominal</span>
                                <span class="text-sm font-bold text-emerald-600">Rp ${new Intl.NumberFormat('id-ID').format(amount)}</span>
                            </div>
                            <div class="flex justify-between border-t border-slate-200 pt-2 mt-2">
                                <span class="text-xs text-slate-400 uppercase font-bold">Kasir</span>
                                <span class="text-sm font-medium text-slate-600">${cashier_name}</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400">Pastikan uang tunai sudah diterima sebelum memproses.</p>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Proses',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.processTopUp();
                    }
                });
            });

            // 2. Event Sukses
            Livewire.on('show-success', (event) => {
                swalConfig.fire({
                    title: 'Berhasil!',
                    text: event.message,
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
            });

            // 3. Event Error
            Livewire.on('show-error', (event) => {
                swalConfig.fire({
                    title: 'Gagal',
                    text: event.message,
                    icon: 'error'
                });
            });
        });
    </script>
    @endscript
</div>