<div class="min-h-screen w-full flex items-center justify-center bg-[#F3F4F6] py-10 px-4 font-sans">

    {{-- Card Container --}}
    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-xl border border-white/50 relative overflow-hidden">

        {{-- Hiasan Background Abstrak (Opsional) --}}
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-blue-500 rounded-full blur-3xl opacity-10 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-indigo-500 rounded-full blur-3xl opacity-10 pointer-events-none"></div>

        {{-- Header --}}
        <div class="text-center mb-10 relative z-10">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Daftar Akun <span class="text-blue-600">SI PAY</span></h1>
            <p class="text-slate-500 text-sm mt-2">Bergabunglah untuk kemudahan transaksi di Sekolah Impian</p>
        </div>

        <form wire:submit="register" class="relative z-10">

            {{-- Nama Lengkap --}}
            <div class="mb-5 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input wire:model="nama_lengkap" type="text"
                        class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full pl-12 p-3.5 outline-none transition duration-200"
                        placeholder="Sesuai KTP / Kartu Pelajar">
                </div>
                @error('nama_lengkap') <span class="text-red-500 text-xs font-medium mt-1 ml-1 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{ $message }}</span> @enderror
            </div>

            <div class="mb-5 group">
    {{-- Label dengan Indikator Status --}}
    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex justify-between">
        Nomor WhatsApp / HP

        {{-- Logika Tampilan Status --}}
        @if($noHpStatus === 'available')
            <span class="text-green-600 normal-case font-semibold flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Tersedia
            </span>
        @elseif($noHpStatus === 'taken')
            <span class="text-red-500 normal-case font-semibold">
                Terdaftar
            </span>
        @endif
    </label>

    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
        </div>

        {{-- Input Field dengan Live Debounce & Dynamic Border --}}
        <input wire:model.live.debounce.500ms="no_hp" type="tel"
            class="w-full bg-slate-50 border {{ $noHpStatus == 'taken' ? 'border-red-500 bg-red-50' : ($noHpStatus == 'available' ? 'border-green-500 bg-green-50' : 'border-slate-200') }} text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full pl-12 p-3.5 outline-none transition duration-200"
            placeholder="08xxxxxxxxxx">

        {{-- Loading Spinner khusus No HP --}}
        <div wire:loading wire:target="no_hp" class="absolute inset-y-0 right-0 pr-4 flex items-center">
            <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>
    </div>
    @error('no_hp') <span class="text-red-500 text-xs font-medium mt-1 ml-1 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{ $message }}</span> @enderror
</div>

            {{-- Email (Real-time Validation) --}}
            <div class="mb-5 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex justify-between">
                    Email
                    {{-- Indikator Status Email --}}
                    @if($emailStatus === 'available')
                        <span class="text-green-600 normal-case font-semibold flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Tersedia</span>
                    @elseif($emailStatus === 'taken')
                        <span class="text-red-500 normal-case font-semibold">Terdaftar</span>
                    @endif
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    {{-- NOTE: Pakai wire:model.live.debounce untuk cek otomatis --}}
                    <input wire:model.live.debounce.500ms="email" type="email"
                        class="w-full bg-slate-50 border {{ $emailStatus == 'taken' ? 'border-red-500 bg-red-50' : ($emailStatus == 'available' ? 'border-green-500 bg-green-50' : 'border-slate-200') }} text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full pl-12 p-3.5 outline-none transition duration-200"
                        placeholder="email@sekolah.com">

                    {{-- Loading Spinner khusus Email --}}
                    <div wire:loading wire:target="email" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                        <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>
                @error('email') <span class="text-red-500 text-xs font-medium mt-1 ml-1">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex justify-between">
                    Username
                    @if($usernameStatus === 'available')
                        <span class="text-green-600 normal-case font-semibold flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>OK</span>
                    @elseif($usernameStatus === 'taken')
                        <span class="text-red-500 normal-case font-semibold">Dipakai</span>
                    @endif
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-slate-400 font-bold group-focus-within:text-blue-500">@</span>
                    </div>
                    <input wire:model.live.debounce.500ms="username" type="text"
                        class="w-full bg-slate-50 border {{ $usernameStatus == 'taken' ? 'border-red-500 bg-red-50' : ($usernameStatus == 'available' ? 'border-green-500 bg-green-50' : 'border-slate-200') }} text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full pl-10 p-3.5 outline-none transition duration-200"
                        placeholder="username">
                </div>
                @error('username') <span class="text-red-500 text-xs font-medium mt-1 ml-1">{{ $message }}</span> @enderror
            </div>

            <hr class="border-slate-100 my-6">

            {{-- PASSWORD SECTION --}}
            <div x-data="{ showPass: false }"> {{-- Password Utama --}}
                <div class="mb-5 group">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <input wire:model.live.debounce.300ms="password"
                            :type="showPass ? 'text' : 'password'"
                            class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full pl-4 pr-12 p-3.5 outline-none transition duration-200"
                            placeholder="Minimal 6 karakter">

                        {{-- Tombol Mata (Toggle) --}}
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            {{-- Icon Mata Terbuka (Show) --}}
                            <svg x-show="showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            {{-- Icon Mata Tertutup (Hide) --}}
                            <svg x-show="!showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                        </button>
                    </div>
                </div>

                {{-- Ulangi Password --}}
                <div class="mb-5 group">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex justify-between">
                        Ulangi Password
                        {{-- Indikator Match Password --}}
                        @if($passwordMatchStatus === 'match')
                            <span class="text-green-600 normal-case font-semibold flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Cocok</span>
                        @elseif($passwordMatchStatus === 'mismatch')
                            <span class="text-red-500 normal-case font-semibold">Tidak Sama</span>
                        @endif
                    </label>

                    <input wire:model.live.debounce.300ms="password_confirmation"
                        :type="showPass ? 'text' : 'password'"
                        class="w-full bg-slate-50 border {{ $passwordMatchStatus == 'mismatch' ? 'border-red-500 bg-red-50' : ($passwordMatchStatus == 'match' ? 'border-green-500 bg-green-50' : 'border-slate-200') }} text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full px-4 p-3.5 outline-none transition duration-200">

                    @error('password_confirmation') <span class="text-red-500 text-xs font-medium mt-1 ml-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- PIN SECTION --}}
            <div x-data="{ showPin: false }"> <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

                    {{-- PIN Utama --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">PIN Transaksi</label>
                        <div class="relative">
                            <input wire:model.live.debounce.300ms="pin"
                                :type="showPin ? 'tel' : 'password'"
                                inputmode="numeric"
                                maxlength="6"
                                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full pl-4 pr-10 p-3.5 outline-none transition duration-200 text-center"
                                placeholder="6 digit">

                            {{-- Tombol Mata PIN --}}
                            <button type="button" @click="showPin = !showPin" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg x-show="showPin" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="!showPin" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Ulangi PIN --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex justify-between">
                            Ulangi PIN
                            {{-- Indikator Match PIN --}}
                            @if($pinMatchStatus === 'match')
                                <span class="text-green-600 normal-case font-semibold flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>OK</span>
                            @elseif($pinMatchStatus === 'mismatch')
                                <span class="text-red-500 normal-case font-semibold">Beda</span>
                            @endif
                        </label>

                        <input wire:model.live.debounce.300ms="pin_confirmation"
                            :type="showPin ? 'tel' : 'password'"
                            inputmode="numeric"
                            maxlength="6"
                            class="w-full bg-slate-50 border {{ $pinMatchStatus == 'mismatch' ? 'border-red-500 bg-red-50' : ($pinMatchStatus == 'match' ? 'border-green-500 bg-green-50' : 'border-slate-200') }} text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full px-4 p-3.5 outline-none transition duration-200 text-center"
                            placeholder="">
                    </div>
                </div>
                {{-- Pesan Error Global PIN Confirmation --}}
                @error('pin_confirmation') <div class="text-red-500 text-xs font-medium -mt-4 mb-4 text-center">{{ $message }}</div> @enderror
            </div>

            {{-- Submit Button --}}
            <button type="submit"
    wire:loading.attr="disabled"
    wire:target="register" {{-- TAMBAHAN PENTING --}}
    class="w-full bg-gradient-to-r from-slate-900 to-slate-800 hover:from-black hover:to-slate-900 text-white font-bold py-4 rounded-xl text-sm transition-all shadow-lg hover:shadow-xl transform active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center">

    {{-- Teks Normal (Hilang saat register diproses) --}}
    <span wire:loading.remove wire:target="register">
        BUAT AKUN BARU
    </span>

    {{-- Teks Loading (Muncul HANYA saat register diproses) --}}
    <div wire:loading wire:target="register" class="flex items-center">
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        MEMPROSES DATA...
    </div>
</button>
        </form>

        {{-- Footer --}}
        <div class="mt-8 text-center relative z-10">
            <p class="text-xs text-slate-500 mb-3">Sudah memiliki akun SI PAY?</p>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 rounded-full bg-blue-50 text-blue-600 text-xs font-bold hover:bg-blue-100 transition duration-200 border border-blue-100">
                Masuk ke Akun
            </a>
        </div>
    </div>
</div>