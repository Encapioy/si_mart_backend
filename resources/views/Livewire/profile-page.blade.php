<div class="min-h-screen bg-gray-50 pb-32" x-data="{ showEditModal: false } x-init="$watch('showEditModal', value => window.dispatchEvent(new CustomEvent('toggle-nav', { detail: value })))"">

    {{-- HEADER GRADIENT --}}
    <div
        class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 pb-24 rounded-b-[2.5rem] shadow-lg overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-10 pointer-events-none">
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-white rounded-full blur-3xl"></div>
            <div class="absolute top-20 right-0 w-60 h-60 bg-white rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 pt-10 px-6 text-center">
            <div class="relative inline-block mb-4">
                <div class="p-1 rounded-full bg-white/20 backdrop-blur-sm border border-white/30">
                    <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->nama_lengkap) }}"
                        class="w-28 h-28 rounded-full object-cover shadow-2xl">
                </div>
                {{-- Tombol Edit Foto (Hiasan visual dulu) --}}
                <div @click="showEditModal = true"
                    class="cursor-pointer absolute bottom-2 right-2 bg-blue-500 text-white p-1.5 rounded-full border-4 border-blue-700 shadow-sm hover:bg-blue-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-white tracking-tight">{{ $user->nama_lengkap }}</h2>
            <p
                class="text-blue-100 text-sm mt-1 font-medium bg-blue-900/30 inline-block px-3 py-1 rounded-full border border-blue-500/30">
                Member ID: {{ $user->member_id }} </p>
        </div>
    </div>

    {{-- CONTENT CARD --}}
    <div class="px-5 -mt-16 relative z-20 space-y-5 max-w-md mx-auto">

        {{-- SALDO CARD (Tidak berubah) --}}
        <div class="bg-white rounded-2xl shadow-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Saldo</span>
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                    </path>
                </svg>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-sm text-gray-500 font-medium">Rp</span>
                <span class="text-3xl font-extrabold text-slate-800 tracking-tight">
                    {{ number_format($user->saldo, 0, ',', '.') }}
                </span>
            </div>
            <div class="h-px bg-gray-100 my-4"></div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400">Pemasukan</p>
                        <p class="text-xs font-bold text-gray-700">Lihat History</p>
                    </div>
                </div>
                <a href="{{ route('history') }}" wire:navigate
                    class="text-xs text-blue-600 font-semibold hover:underline">
                    Detail &rarr;
                </a>
            </div>
        </div>

        {{-- INFORMASI AKUN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Informasi Akun</h3>

                {{-- Tombol Trigger Modal --}}
                <button @click="showEditModal = true"
                    class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                    Ubah
                </button>
            </div>

            <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition cursor-default">
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs text-gray-500">Nomor Handphone</p>
                    <p class="text-sm font-semibold text-slate-800 font-mono tracking-wide">{{ $user->no_hp }}</p>
                </div>
            </div>

            <div class="h-px bg-gray-50 mx-4"></div>

            <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition cursor-default">
                <div
                    class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">Alamat Email</p>
                    <p class="text-sm font-semibold text-slate-800 truncate">
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            <div class="h-px bg-gray-50 mx-4"></div>

            <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition cursor-default">
                <div
                    class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs text-gray-500">Status Akun</p>
                    <p class="text-sm font-semibold text-slate-800 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        Aktif
                    </p>
                </div>
            </div>
        </div>

        {{-- TOMBOL LOGOUT --}}
        <div class="pt-4">
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
            <button onclick="confirmLogout()"
                class="group w-full bg-white border border-red-100 text-red-600 font-semibold py-4 rounded-2xl shadow-sm hover:bg-red-50 hover:border-red-200 transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98]">
                <div class="p-1 rounded-full bg-red-100 group-hover:bg-red-200 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </div>
                Keluar Aplikasi
            </button>

            <div class="text-center mt-6">
                <p class="text-[10px] text-gray-400 font-medium">SI PAY v1.0.0 &copy; 2026</p>
            </div>
        </div>
    </div>

    {{-- ======================== --}}
    {{-- MODAL EDIT PROFILE --}}
    {{-- ======================== --}}
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center px-4"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>

        {{-- Modal Content --}}
        <div class="relative bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl transform transition-all"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-10 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100">

            <div class="text-center mb-6">
                <h3 class="text-lg font-bold text-slate-800">Edit Profil</h3>
                <p class="text-xs text-gray-500">Perbarui informasi data diri Anda.</p>
            </div>

            <form wire:submit.prevent="updateProfile">
                <div class="space-y-4">

                    {{-- UPLOAD FOTO (BARU) --}}
                    <div class="flex flex-col items-center justify-center mb-6">
                        <div class="relative group">
                            {{-- Logic Preview: --}}
                            {{-- 1. Jika user baru upload ($photo), tampilkan preview sementaranya --}}
                            {{-- 2. Jika tidak, tampilkan foto asli dari DB --}}
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}"
                                    class="w-24 h-24 rounded-full object-cover border-4 border-gray-100 shadow-md">
                            @else
                                <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->nama_lengkap) }}"
                                    class="w-24 h-24 rounded-full object-cover border-4 border-gray-100 shadow-md">
                            @endif

                            {{-- Tombol Overlay Kamera --}}
                            <label for="photo-upload"
                                class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full cursor-pointer hover:bg-blue-700 shadow-sm transition transform hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </label>

                            {{-- Input File Tersembunyi --}}
                            <input wire:model="photo" id="photo-upload" type="file" class="hidden" accept="image/*">
                        </div>

                        {{-- Loading State saat upload --}}
                        <div wire:loading wire:target="photo" class="text-xs text-blue-500 font-bold mt-2 animate-pulse">
                            Sedang mengunggah...
                        </div>
                        @error('photo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Nama --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                        <input wire:model="nama_lengkap" type="text"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold text-slate-800 transition">
                        @error('nama_lengkap') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
                        <input wire:model="email" type="email"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold text-slate-800 transition">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. Handphone</label>
                        <input wire:model="no_hp" type="number"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold text-slate-800 transition">
                        @error('no_hp') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" @click="showEditModal = false"
                        class="flex-1 py-3 rounded-xl bg-gray-100 text-gray-600 font-bold text-sm hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 rounded-xl bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition flex justify-center items-center gap-2">
                        <span wire:loading.remove wire:target="updateProfile">Simpan</span>
                        <span wire:loading wire:target="updateProfile">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        // 1. Script Logout (Existing)
        function confirmLogout() {
            Swal.fire({
                title: 'Ingin keluar?',
                text: "Sesi Anda akan diakhiri sekarang.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                background: '#fff',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold',
                    cancelButton: 'rounded-xl px-6 py-3 font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Keluar...',
                        text: 'Sedang memproses logout',
                        timer: 2000,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            document.getElementById('logout-form').submit();
                        }
                    });
                }
            })
        }

        // 2. Script Notifikasi Update Sukses (Baru)
        document.addEventListener('livewire:initialized', () => {
            @this.on('profile-updated', () => {
                // Tutup Modal via Alpine
                // Kita cari elemen root yang punya x-data dan set showEditModal ke false
                document.querySelector('[x-data]').__x.$data.showEditModal = false;

                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Profil berhasil diperbarui.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    background: '#fff',
                    customClass: { popup: 'rounded-2xl' }
                });
            });
        });
    </script>
</div>