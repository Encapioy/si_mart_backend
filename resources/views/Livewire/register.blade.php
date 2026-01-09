<div class="min-h-screen w-screen flex items-center justify-center bg-slate-50 py-10 px-4">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-lg border border-slate-100">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Daftar Akun SI PAY</h1>
            <p class="text-slate-400 text-xs">Lengkapi data diri sesuai identitas asli</p>
        </div>

        <form wire:submit="register">

            <div class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Lengkap</label>
                <input wire:model="nama_lengkap" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:bg-white transition" placeholder="Nama sesuai KTP/KTM">
                @error('nama_lengkap') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Email Aktif</label>
                    <input wire:model="email" type="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:bg-white transition" placeholder="email@sekolah.com">
                    @error('email') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. WhatsApp</label>
                    <input wire:model="no_hp" type="number" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:bg-white transition" placeholder="08xxxxxxxxxx">
                    @error('no_hp') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Username (Login)</label>
                    <input wire:model="username" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:bg-white transition" placeholder="tanpa spasi">
                    @error('username') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">PIN Transaksi (6 Angka)</label>
                    <input wire:model="pin" type="tel" maxlength="6" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:bg-white transition text-center tracking-widest" placeholder="******">
                    @error('pin') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr class="border-slate-100 my-6">

            <div class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Password Login</label>
                <input wire:model="password" type="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:bg-white transition">
                @error('password') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="mb-8">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Ulangi Password</label>
                <input wire:model="password_confirmation" type="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:bg-white transition">
            </div>

            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl text-sm hover:bg-black transition shadow-lg transform active:scale-[0.99]">
                <span wire:loading.remove>DAFTAR AKUN SEKARANG</span>
                <span wire:loading>SEDANG MEMPROSES...</span>
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-xs text-slate-400 mb-2">Sudah punya akun?</p>
            <a href="{{ route('login') }}" class="inline-block px-6 py-2 rounded-full bg-blue-50 text-blue-600 text-xs font-bold hover:bg-blue-100 transition">
                Masuk Disini
            </a>
        </div>
    </div>
</div>