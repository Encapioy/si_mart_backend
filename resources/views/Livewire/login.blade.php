<div class="h-screen w-screen flex items-center justify-center bg-slate-50">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm border border-slate-100">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Selamat Datang</h1>
            <p class="text-slate-400 text-xs">Login Admin / User</p>
        </div>

        @error('username')
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-xs font-bold mb-4 text-center">{{ $message }}</div>
        @enderror

        <form wire:submit="login">
            <div class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Username</label>
                <input wire:model="username" type="text"
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500">
            </div>
            <div class="mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Password</label>
                <input wire:model="password" type="password"
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl text-sm">
                <span wire:loading.remove>LOGIN SEKARANG</span>
                <span wire:loading>MEMPROSES...</span>
            </button>
        </form>
        <div class="mt-6 text-center border-t border-slate-100 pt-4">
            <p class="text-xs text-slate-400">Belum punya akun?</p>
            <a href="{{ route('register') }}" class="text-sm font-bold text-blue-600 hover:underline">Daftar Akun Baru</a>
        </div>
    </div>
</div>