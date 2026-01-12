<div class="min-h-screen bg-gray-50 pb-32">
    <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 pb-24 rounded-b-[2.5rem] shadow-lg overflow-hidden">

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
                <div class="absolute bottom-2 right-2 bg-blue-500 text-white p-1.5 rounded-full border-4 border-blue-700 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-white tracking-tight">{{ $user->nama_lengkap }}</h2>
            <p class="text-blue-100 text-sm mt-1 font-medium bg-blue-900/30 inline-block px-3 py-1 rounded-full border border-blue-500/30">
                Member ID: {{ $user->member_id }} </p>
        </div>
    </div>

    <div class="px-5 -mt-16 relative z-20 space-y-5 max-w-md mx-auto">

        <div class="bg-white rounded-2xl shadow-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Saldo</span>
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
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
                         <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400">Pemasukan</p>
                        <p class="text-xs font-bold text-gray-700">Lihat History</p>
                    </div>
                </div>

                <a href="{{ route('history') }}" wire:navigate class="text-xs text-blue-600 font-semibold hover:underline">
                    Detail &rarr;
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Informasi Akun</h3>
            </div>

            <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition cursor-default">
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs text-gray-500">Nomor Handphone</p>
                    <p class="text-sm font-semibold text-slate-800 font-mono tracking-wide">{{ $user->no_hp }}</p>
                </div>
            </div>

            <div class="h-px bg-gray-50 mx-4"></div>

<div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition cursor-default">
    <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
    </div>
    <div class="flex-1 min-w-0"> <p class="text-xs text-gray-500">Alamat Email</p>
        <p class="text-sm font-semibold text-slate-800 truncate">
            {{ $user->email }}
        </p>
    </div>
</div>

            <div class="h-px bg-gray-50 mx-4"></div>

            <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition cursor-default">
                <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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

        <div class="pt-4">

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <button onclick="confirmLogout()"
        class="group w-full bg-white border border-red-100 text-red-600 font-semibold py-4 rounded-2xl shadow-sm hover:bg-red-50 hover:border-red-200 transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98]">
        <div class="p-1 rounded-full bg-red-100 group-hover:bg-red-200 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </div>
        Keluar Aplikasi
    </button>

    <div class="text-center mt-6">
            <p class="text-[10px] text-gray-400 font-medium">SI PAY v1.0.0 &copy; 2026</p>
    </div>
</div>

    </div>
    <script>
    function confirmLogout() {
        Swal.fire({
            title: 'Ingin keluar?',
            text: "Sesi Anda akan diakhiri sekarang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33', // Warna Merah
            cancelButtonColor: '#3085d6', // Warna Biru
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal',
            reverseButtons: true, // Tombol Batal di kiri, Hapus di kanan
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl', // Agar sudut alert membulat
                confirmButton: 'rounded-xl px-6 py-3 font-bold',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading biar keren
                Swal.fire({
                    title: 'Keluar...',
                    text: 'Sedang memproses logout',
                    timer: 2000,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        // Submit Form Tersembunyi
                        document.getElementById('logout-form').submit();
                    }
                });
            }
        })
    }
</script>
</div>