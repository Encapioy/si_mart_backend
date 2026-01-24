<div class="min-h-screen p-6">

    {{-- HEADER SECTION --}}
    <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen Kasir</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola akun, kredensial login, dan pantau kinerja kasir.</p>
        </div>

        {{-- Tombol Tambah Kasir --}}
        <button wire:click="create"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-600/30 transition transform active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Kasir Baru
        </button>
    </div>

    {{-- STATS CARD (Total Uang) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="md:col-span-1 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-6 text-white shadow-xl shadow-slate-900/20 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Total Dana (Approved)</p>
                <h3 class="text-3xl font-bold tracking-tight">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h3>
                <p class="text-xs text-slate-400 mt-2">Akumulasi dari seluruh kasir aktif</p>
            </div>
            {{-- Hiasan Background --}}
            <div class="absolute -right-6 -bottom-6 text-white/5 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.15-1.46-3.27-3.4h1.96c.1 1.05 1.18 1.91 2.53 1.91 1.29 0 2.13-.72 2.13-1.71 0-1.22-.98-1.75-2.82-2.19-2.22-.54-3.64-1.39-3.64-3.52 0-2.01 1.4-3.15 2.95-3.53V4h2.67v1.93c1.38.3 2.48 1.12 2.73 2.59h-1.99c-.1-1.05-1.12-1.57-2.4-1.57-1.15 0-1.92.6-1.92 1.57 0 .97.94 1.53 2.83 2.05 2.45.66 3.63 1.63 3.63 3.61 0 1.96-1.42 3.19-3.14 3.51z"></path></svg>
            </div>
        </div>
    </div>

    {{-- TABEL DATA KASIR --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        {{-- Toolbar Table --}}
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-bold text-lg text-slate-800">Daftar Akun Kasir</h2>

            {{-- Search Bar --}}
            <div class="relative w-full md:w-72">
                <input wire:model.live="search" type="text" placeholder="Cari username kasir..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider font-bold border-b border-slate-100">
                        <th class="p-5 pl-6">Identitas</th>
                        <th class="p-5">Kredensial (Rahasia)</th>
                        <th class="p-5 text-center">Kinerja</th>
                        <th class="p-5 text-center pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashiers as $admin)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            {{-- 1. IDENTITAS --}}
                            <td class="p-5 pl-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700 flex items-center justify-center font-bold text-sm shadow-sm">
                                        {{ substr($admin->username, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm">{{ $admin->username }}</p>
                                        <p class="text-xs text-slate-500 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            {{ $admin->username }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- 2. KREDENSIAL (PIN & PASSWORD) --}}
                            <td class="p-5">
                                <div class="flex flex-col gap-2">
                                    {{-- Baris PIN --}}
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase w-8 tracking-wider">PIN</span>
                                        <span class="font-mono text-sm font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                            {{ $admin->pin ?? '-' }}
                                        </span>
                                    </div>

                                    {{-- Baris Password --}}
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase w-8 tracking-wider">Pass</span>
                                        <div class="flex items-center gap-2">
                                            @if(isset($showPasswordMap[$admin->id]))
                                                {{-- Mode Terlihat --}}
                                                <span class="font-mono text-sm font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100 animate-pulse">
                                                    {{ $admin->plain_password ?? 'N/A' }}
                                                </span>
                                                <button wire:click="togglePasswordVisibility({{ $admin->id }})" class="text-slate-400 hover:text-slate-600 transition" title="Sembunyikan">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                                </button>
                                            @else
                                                {{-- Mode Tersembunyi --}}
                                                <span class="font-mono text-sm text-slate-400 tracking-widest">••••••</span>
                                                <button wire:click="togglePasswordVisibility({{ $admin->id }})" class="text-slate-400 hover:text-blue-500 transition" title="Lihat Password">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. KINERJA --}}
                            <td class="p-5 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-full">
                                        {{ $admin->top_ups_count ?? 0 }}x Trx
                                    </span>
                                    <span class="text-sm font-bold text-emerald-600">
                                        Rp {{ number_format($admin->top_ups_sum_amount ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>

                            {{-- 4. AKSI --}}
                            <td class="p-5 pr-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- History Button --}}
                                    <button wire:click="showHistory({{ $admin->id }})" title="Riwayat Transaksi"
                                        class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>

                                    {{-- Edit Button --}}
                                    <button wire:click="edit({{ $admin->id }})" title="Edit Akun"
                                        class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    {{-- Delete Button --}}
                                    {{-- <button wire:confirm="Apakah Anda yakin ingin menghapus akun kasir ini? Data kinerja akan tetap tersimpan namun akun tidak bisa login lagi."
                                        wire:click="delete({{ $admin->id }})" title="Hapus Akun"
                                        class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button> --}}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-16 h-16 mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <p class="font-medium">Belum ada data kasir.</p>
                                    <p class="text-sm">Silakan tambah kasir baru untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-5 border-t border-slate-100 bg-slate-50/50">
            {{ $cashiers->links() }}
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL 1: FORM TAMBAH / EDIT KASIR          --}}
    {{-- ========================================== --}}
    <div x-data="{ open: false }"
         x-on:open-form-modal.window="open = true"
         x-on:close-form-modal.window="open = false"
         x-show="open"
         style="display: none;"
         class="fixed inset-0 z-[60] flex items-center justify-center px-4">

        {{-- Backdrop --}}
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

        {{-- Form Content --}}
        <div x-show="open" x-transition.scale.duration.300ms class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            {{-- Header Form --}}
            <div class="bg-slate-900 px-6 py-5 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-white text-lg">
                        {{ $isEditMode ? 'Edit Data Kasir' : 'Tambah Kasir Baru' }}
                    </h3>
                    <p class="text-slate-400 text-xs mt-0.5">Lengkapi form berikut ini</p>
                </div>
                <button @click="open = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="overflow-y-auto p-6">
                {{-- Ganti action form menjadi 'save' --}}
                <form wire:submit="save">
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Lengkap</label>
                        <input wire:model="nama_lengkap" type="text" placeholder="Contoh: Budi Santoso"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        @error('nama_lengkap') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Username --}}
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Username</label>
                        <div class="relative">
                            <input wire:model="username" type="text" placeholder="budi_kasir"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        </div>
                        @error('username') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        {{-- Password --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Password</label>
                            <input wire:model="password" type="text" placeholder="{{ $isEditMode ? '(Tetap)' : 'Min 6 kar' }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                            @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        {{-- PIN --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">PIN (6 Angka)</label>
                            <input wire:model="pin" type="text" maxlength="6" placeholder="123456"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-center font-mono tracking-widest focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                            @error('pin') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="open = false"
                            class="flex-1 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition text-sm">
                            Batal
                        </button>

                        {{-- TOMBOL SUBMIT YANG SUDAH DIPERBAIKI --}}
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-600/30 transition text-sm flex justify-center items-center disabled:opacity-70 disabled:cursor-not-allowed">

                            {{-- Teks Normal: Muncul saat TIDAK loading 'save' --}}
                            <span wire:loading.remove wire:target="save">
                                {{ $isEditMode ? 'Simpan Perubahan' : 'Buat Akun' }}
                            </span>

                            {{-- Loading Spinner: Muncul HANYA saat loading 'save' --}}
                            <span wire:loading.class.remove="hidden" wire:target="save" class="hidden flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL 2: RIWAYAT TRANSAKSI                 --}}
    {{-- ========================================== --}}
    <div x-data="{ open: false }"
         x-on:open-history-modal.window="open = true"
         x-on:keydown.escape.window="open = false"
         x-show="open"
         style="display: none;"
         class="fixed inset-0 z-[60] flex items-center justify-center px-4">

        {{-- Backdrop --}}
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

        {{-- Modal Content --}}
        <div x-show="open" x-transition.scale class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[80vh] flex flex-col">

            {{-- Header --}}
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg text-slate-800">Riwayat Topup</h3>
                    @if($selectedCashier)
                        <p class="text-xs text-slate-500">Kasir: <span class="font-bold text-blue-600">{{ $selectedCashier->username }}</span> (10 Approved Terakhir)</p>
                    @endif
                </div>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- List --}}
            <div class="overflow-y-auto p-0 flex-1">
                @if(count($historyTopups) > 0)
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                            <tr>
                                <th class="p-4 pl-6">Tanggal</th>
                                <th class="p-4">User</th>
                                <th class="p-4 text-right pr-6">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($historyTopups as $history)
                                <tr>
                                    <td class="p-4 pl-6 text-slate-500">{{ $history->created_at->format('d M Y, H:i') }}</td>
                                    <td class="p-4">
                                        <p class="font-bold text-slate-800">{{ $history->user->username ?? 'User Hapus' }}</p>
                                        <p class="text-xs text-slate-400">{{ $history->user->member_id ?? '-' }}</p>
                                    </td>
                                    <td class="p-4 pr-6 text-right font-bold text-emerald-600">
                                        +Rp {{ number_format($history->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-10 text-center text-slate-400 flex flex-col items-center">
                        <svg class="w-12 h-12 mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <p>Belum ada riwayat transaksi approved.</p>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="p-4 bg-slate-50 border-t border-slate-100 text-right">
                <button @click="open = false" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 font-medium hover:bg-slate-100 transition text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>