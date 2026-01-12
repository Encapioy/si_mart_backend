<div class="h-screen overflow-hidden flex justify-center bg-white text-slate-800">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Style Autocomplete bawaan request */
        .autocomplete-items {
            position: absolute;
            border: 1px solid #e2e8f0;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border-radius: 0 0 0.5rem 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .autocomplete-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #e2e8f0;
        }

        .autocomplete-item:hover {
            background-color: #f1f5f9;
        }
    </style>

    <div class="w-1/2 h-full flex flex-col justify-center items-center relative p-12 bg-white z-10">
        <div class="w-full max-w-md">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Top Up Saldo</h1>
                <p class="text-slate-500 text-sm">Minimal transaksi Rp 100.000</p>
            </div>

            <form wire:submit.prevent="triggerConfirm" autocomplete="off">

                <div class="mb-5 relative group">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Cari Username Siswa</label>
                    <div
                        class="flex items-center bg-slate-50 rounded-xl px-4 py-3 border border-transparent focus-within:border-blue-500 focus-within:bg-white transition">
                        <i class="fa-solid fa-user-graduate text-slate-400 mr-3"></i>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="bg-transparent w-full text-sm font-bold focus:outline-none text-slate-800"
                            placeholder="Ketik nama atau username...">
                    </div>

                    @if(!empty($usersFound))
                        <div class="autocomplete-items">
                            @foreach($usersFound as $u)
                                <div class="autocomplete-item" wire:click="selectUser({{ $u->id }})">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 text-sm">{{ $u->username }}</span>
                                        <span class="text-[10px] text-slate-400 uppercase">{{ $u->nama_lengkap }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif(strlen($search) > 1 && empty($usersFound) && !$selectedUser)
                        <div class="autocomplete-items">
                            <div class="p-3 text-xs text-red-500">User tidak ditemukan.</div>
                        </div>
                    @endif
                    @error('search') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nominal (Min 100k)</label>
                    <div
                        class="flex items-center bg-slate-50 rounded-xl px-4 py-3 border border-transparent focus-within:border-emerald-500 focus-within:bg-white transition mb-2">
                        <span class="text-slate-400 font-bold mr-3 text-sm">Rp</span>
                        <input type="number" wire:model="amount"
                            class="bg-transparent w-full text-lg font-bold focus:outline-none text-emerald-700 placeholder-slate-300"
                            placeholder="0" min="100000">
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="setAmount(100000)"
                            class="flex-1 py-2 text-xs font-bold bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition">100k</button>
                        <button type="button" wire:click="setAmount(150000)"
                            class="flex-1 py-2 text-xs font-bold bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition">150k</button>
                        <button type="button" wire:click="setAmount(200000)"
                            class="flex-1 py-2 text-xs font-bold bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition">200k</button>
                    </div>
                    @error('amount') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 mb-1">NAMA KASIR</label>
                        <div class="relative">
                            <select wire:model="cashier_id"
                                class="w-full bg-slate-50 rounded-lg px-3 py-2 text-sm border border-transparent focus:bg-white focus:border-slate-300 focus:outline-none appearance-none cursor-pointer font-medium">
                                <option value="" selected>Pilih Nama...</option>
                                @foreach($cashiers as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_lengkap }}</option>
                                @endforeach
                            </select>
                            <i
                                class="fa-solid fa-chevron-down absolute right-3 top-3 text-xs text-slate-400 pointer-events-none"></i>
                        </div>
                        @error('cashier_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 mb-1">PIN KASIR</label>
                        <input type="password" wire:model="cashier_pin" maxlength="6"
                            class="w-full bg-slate-50 rounded-lg px-3 py-2 text-sm border border-transparent focus:bg-white focus:border-slate-300 focus:outline-none text-center tracking-widest font-bold"
                            placeholder="******">
                        @error('cashier_pin') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-slate-900 hover:bg-black text-white font-bold py-3.5 rounded-xl shadow-lg transition transform active:scale-[0.98] flex justify-center items-center">
                    <span wire:loading.remove>PROSES TOP UP</span>
                    <span wire:loading><i class="fas fa-spinner fa-spin mr-2"></i> LOADING...</span>
                </button>
            </form>
        </div>

        <p class="absolute bottom-6 text-[10px] text-slate-300">Protected System | v2.0</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @script
<script>
    document.addEventListener('livewire:initialized', () => {

        // 1. Dengar Event Konfirmasi (Menggunakan Destructuring Objek)
        Livewire.on('show-confirmation-modal', (event) => {

            // Di Livewire 3, 'event' adalah objek berisi properti yang dikirim dari PHP
            const username = event.username;
            const amount = event.amount;
            const cashier_name = event.cashier_name;

            Swal.fire({
                title: 'Konfirmasi Top Up',
                html: `
                    <div class="text-left text-sm border-t border-b py-3 my-2">
                        <p class="mb-1">Tujuan: <b>${username}</b></p>
                        <p class="mb-1">Nominal: <b class="text-emerald-600">Rp ${new Intl.NumberFormat('id-ID').format(amount)}</b></p>
                        <p>Kasir: <b>${cashier_name}</b></p>
                    </div>
                    <p class="text-[10px] text-gray-400">Pastikan dana tunai telah diterima.</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#d33',
                confirmButtonText: 'YA, PROSES SEKARANG',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Panggil method PHP menggunakan helper $wire
                    $wire.processTopUp();
                }
            });
        });

        // 2. Dengar Event Sukses
        Livewire.on('show-success', (event) => {
            Swal.fire({
                title: 'Berhasil!',
                text: event.message,
                icon: 'success',
                confirmButtonColor: '#0f172a'
            });
        });

        // 3. Dengar Event Error
        Livewire.on('show-error', (event) => {
            Swal.fire({
                title: 'Terjadi Kesalahan',
                text: event.message,
                icon: 'error',
                confirmButtonColor: '#0f172a'
            });
        });

    });
</script>
@endscript
</div>