<div class="min-h-screen p-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen User (Santri)</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola data akun santri, reset PIN/Password, dan info kontak.</p>
        </div>

        <button wire:click="create"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-600/30 transition transform active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            Tambah User Manual
        </button>
    </div>

    {{-- TABEL DATA --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">

        {{-- Toolbar --}}
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-bold text-lg text-slate-800">Database User</h2>
            <div class="relative w-full md:w-72">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, username, email..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider font-bold border-b border-slate-100">
                        <th class="p-5 pl-6">User Info</th>
                        <th class="p-5">Kontak</th>
                        <th class="p-5">Keamanan (PIN/Pass)</th>
                        <th class="p-5 text-right">Saldo</th>
                        <th class="p-5 text-center pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            {{-- 1. Info User --}}
                            <td class="p-5 pl-6">
                                <div>
                                    <p class="font-bold text-slate-800 text-sm">{{ $user->nama_lengkap }}</p>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5">@ {{ $user->username }}</p>
                                    <span
                                        class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        ID: {{ $user->member_id ?? '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- 2. Kontak --}}
                            <td class="p-5">
                                <div class="text-sm space-y-1">
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        {{ $user->email }}
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                            </path>
                                        </svg>
                                        {{ $user->no_hp ?? '-' }}
                                    </div>
                                </div>
                            </td>

                            {{-- 3. Keamanan --}}
                            <td class="p-5">
                                <div class="flex flex-col gap-2">
                                    {{-- PIN --}}
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase w-8">PIN</span>
                                        <span
                                            class="font-mono text-sm font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                            {{ $user->pin ?? 'N/A' }}
                                        </span>
                                    </div>
                                    {{-- Password --}}
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase w-8">PASS</span>
                                        <span class="text-xs text-slate-400 italic bg-slate-50 px-2 py-0.5 rounded">
                                            Tersembunyi (Aman)
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- 4. Saldo --}}
                            <td class="p-5 text-right">
                                <span class="font-bold text-emerald-600 text-sm">
                                    Rp {{ number_format($user->saldo, 0, ',', '.') }}
                                </span>
                            </td>

                            {{-- 5. Aksi --}}
                            <td class="p-5 pr-6 text-center">
                                <div class="flex justify-center gap-2">
                                    <button wire:click="edit({{ $user->id }})"
                                        class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition shadow-sm"
                                        title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button wire:confirm="Yakin ingin menghapus user ini?"
                                        wire:click="delete({{ $user->id }})"
                                        class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm"
                                        title="Hapus User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400">
                                Tidak ada data user ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-5 border-t border-slate-100 bg-slate-50/50">
            {{ $users->links() }}
        </div>
    </div>

    {{-- MODAL EDIT/CREATE FORM --}}
    <div x-data="{ open: false }" x-on:open-form-modal.window="open = true" x-on:close-form-modal.window="open = false"
        x-show="open" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center px-4">

        <div x-show="open" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
            class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="bg-slate-900 px-6 py-5 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-white text-lg">{{ $isEditMode ? 'Edit Data User' : 'Tambah User' }}</h3>
                <button @click="open = false" class="text-slate-400 hover:text-white"><svg class="w-6 h-6" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>

            <div class="overflow-y-auto p-6">
                <form wire:submit="save">
                    {{-- Nama & Username --}}
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Lengkap</label>
                            <input wire:model="nama_lengkap" type="text"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:border-blue-500 outline-none">
                            @error('nama_lengkap') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Username</label>
                            <input wire:model="username" type="text"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:border-blue-500 outline-none">
                            @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Email & HP --}}
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Email</label>
                            <input wire:model="email" type="email"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:border-blue-500 outline-none">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">No HP</label>
                            <input wire:model="no_hp" type="number"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:border-blue-500 outline-none">
                            @error('no_hp') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="my-4 border-t border-slate-100"></div>

                    {{-- Security --}}
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">PIN (6 Angka)</label>
                            <input wire:model="pin" type="text" maxlength="6"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-mono text-center tracking-widest focus:border-blue-500 outline-none">
                            @error('pin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">
                                {{ $isEditMode ? 'Reset Password (Opsional)' : 'Password' }}
                            </label>
                            <input wire:model="password" type="text"
                                placeholder="{{ $isEditMode ? 'Isi jika ingin ubah' : 'Min 6 Karakter' }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:border-blue-500 outline-none">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-600/30">
                        <span wire:loading.remove>Simpan Data</span>
                        <span wire:loading>Memproses...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>