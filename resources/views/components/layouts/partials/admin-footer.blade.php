<div class="p-4 border-t border-slate-800 shrink-0">
    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800 transition">
        <div
            class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 flex items-center justify-center text-sm font-bold text-white shadow-lg">
            {{ substr(Auth::user()->nama_lengkap ?? 'A', 0, 1) }}
        </div>

        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-white truncate">{{ Auth::user()->nama_lengkap ?? 'Admin' }}</p>
            <p class="text-[10px] text-slate-400">Administrator</p>
        </div>

        <form method="POST" action="{{ route('logout') }}" x-data>
            @csrf
            <button type="submit"
                class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-700/50 transition duration-200 group"
                title="Keluar Aplikasi">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
            </button>
        </form>
    </div>
</div>