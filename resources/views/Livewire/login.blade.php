<div class="min-h-screen w-full flex items-center justify-center bg-[#F3F4F6] py-10 px-4 font-sans">

    {{-- Card Container --}}
    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-md border border-white/50 relative overflow-hidden">

        {{-- Hiasan Background Abstrak --}}
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-blue-500 rounded-full blur-3xl opacity-10 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-32 h-32 bg-indigo-500 rounded-full blur-3xl opacity-10 pointer-events-none"></div>

        {{-- Header --}}
        <div class="text-center mb-8 relative z-10">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Masuk <span class="text-blue-600">SI PAY</span></h1>
            <p class="text-slate-500 text-sm mt-2">Selamat datang kembali! Silakan login.</p>
        </div>

        {{-- Global Error Alert --}}
        @error('username')
            <div class="mb-6 bg-red-50 border border-red-100 rounded-xl p-4 flex items-start gap-3 relative z-10">
                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h3 class="text-xs font-bold text-red-800">Login Gagal</h3>
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                </div>
            </div>
        @enderror

        <form wire:submit="login" class="relative z-10">

            {{-- Username / Email Input --}}
            <div class="mb-5 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Username / Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input wire:model="username" type="text"
                        class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full pl-12 p-3.5 outline-none transition duration-200 placeholder:text-slate-300"
                        placeholder="Masukkan username anda">
                </div>
            </div>

            {{-- Password Input dengan Toggle Show/Hide --}}
            <div class="mb-8 group" x-data="{ showPass: false }">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Password</label>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>

                    <input wire:model="password"
                        :type="showPass ? 'text' : 'password'"
                        class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full pl-12 pr-12 p-3.5 outline-none transition duration-200 placeholder:text-slate-300"
                        placeholder="••••••••">

                    {{-- Tombol Mata Toggle --}}
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer transition">
                        <svg x-show="showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg x-show="!showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                    </button>
                </div>
            </div>


            {{-- Submit Button --}}
            <button type="submit"
                wire:loading.attr="disabled"
                wire:target="login"
                class="w-full bg-gradient-to-r from-slate-900 to-slate-800 hover:from-black hover:to-slate-900 text-white font-bold py-4 rounded-xl text-sm transition-all shadow-lg hover:shadow-xl transform active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center">

                <span wire:loading.remove wire:target="login">MASUK SEKARANG</span>

                <div wire:loading wire:target="login" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    MEMERIKSA AKUN...
                </div>
            </button>
        </form>

        {{-- Footer --}}
        <div class="mt-8 text-center relative z-10">
            <p class="text-xs text-slate-500 mb-3">Belum memiliki akun?</p>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-2.5 rounded-full bg-blue-50 text-blue-600 text-xs font-bold hover:bg-blue-100 transition duration-200 border border-blue-100">
                Daftar Akun Baru
            </a>
        </div>
    </div>
</div>