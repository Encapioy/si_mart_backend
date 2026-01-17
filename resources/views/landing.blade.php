<x-layouts.app>
    <div class="min-h-screen flex flex-col items-center justify-center bg-white p-6 relative overflow-hidden">
        <div class="absolute top-[-50px] left-[-50px] w-40 h-40 bg-blue-100 rounded-full blur-3xl opacity-50"></div>

        <div class="max-w-md w-full text-center space-y-8 z-10">
            <div>
                <div
                    class="w-20 h-20 bg-blue-600 rounded-2xl mx-auto flex items-center justify-center shadow-lg shadow-blue-200">
                    <span class="text-white text-3xl font-bold">SI</span>
                </div>
                <h1 class="mt-6 text-3xl font-extrabold text-slate-900">SI Pay App</h1>
                <p class="mt-2 text-slate-500">Jajan di kantin jadi lebih sat-set.</p>
            </div>

            <div class="space-y-4">
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                    class="block w-full py-3.5 px-4 bg-slate-900 text-white font-bold rounded-xl shadow-lg hover:bg-black transition">
                    MASUK WEB APP
                </a>
                <a href="https://drive.google.com/drive/folders/1gc-pH9oYvdwCWIGLKtjZvsna-RqJ8n0R?usp=sharing"
                    class="block w-full py-3.5 px-4 bg-blue-50 text-blue-700 font-bold rounded-xl border border-blue-100">
                    DOWNLOAD APLIKASI
                </a>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-100">
                <p class="text-xs text-slate-400">© 2026 SI Pay System</p>
            </div>
        </div>
    </div>

</x-layouts.app>