<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SI MART</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-screen w-screen flex items-center justify-center bg-slate-50">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm border border-slate-100">

        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-blue-600 rounded-lg mx-auto flex items-center justify-center text-white text-xl font-bold mb-3">S</div>
            <h1 class="text-2xl font-bold text-slate-800">Selamat Datang</h1>
            <p class="text-slate-400 text-xs">Silakan login untuk melanjutkan</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-xs font-bold mb-4 text-center border border-red-100">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Username / No. HP</label>
                <input type="text" name="username" class="w-full bg-slate-50 border border-transparent focus:bg-white focus:border-blue-500 rounded-lg px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition" placeholder="Masukan ID Anda" required>
            </div>

            <div class="mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Password</label>
                <input type="password" name="password" class="w-full bg-slate-50 border border-transparent focus:bg-white focus:border-blue-500 rounded-lg px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition" placeholder="••••••" required>
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-bold py-3.5 rounded-xl transition shadow-lg active:scale-[0.98] text-sm">
                LOGIN SEKARANG
            </button>
        </form>

        <p class="text-center text-[10px] text-slate-300 mt-6">&copy; 2026 SI MART System</p>
    </div>

</body>
</html>