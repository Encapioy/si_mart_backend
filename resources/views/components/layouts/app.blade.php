<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Dashboard | SI PAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom Scrollbar untuk sidebar agar rapi */
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: #0f172a;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased overflow-hidden">

    <div class="flex h-screen w-full">

        <aside
            class="w-72 bg-slate-900 text-white flex flex-col shadow-2xl z-20 transition-all duration-300 hidden md:flex">

            <div class="h-20 flex items-center px-8 border-b border-slate-800/50">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg tracking-tight">SI PAY</h1>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">Admin Panel</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto custom-scroll py-6 px-4 space-y-1">

                <p class="px-4 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 mt-2">Main Menu</p>

                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                        </path>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <p class="px-4 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 mt-6">Keuangan</p>

                <a href="{{ route('admin.topup') }}"
                    class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.topup') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.topup') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="font-medium">Input Top Up</span>
                </a>

                <a href="{{ route('admin.topups') }}"
                    class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.topups') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.topups') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    <span class="font-medium">Riwayat Top Up</span>
                </a>

                <a href="{{ route('admin.transactions') }}"
                    class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.transactions') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.transactions') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                    <span class="font-medium">Semua Transaksi</span>
                </a>

                <p class="px-4 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 mt-6">Data Master</p>

                <a href="{{ route('admin.merchant.list') }}"
                    class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.merchant.list') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.merchant.list') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    <span class="font-medium">Data Merchant</span>
                </a>

            </nav>

            <div class="p-4 border-t border-slate-800">
                <div
                    class="flex items-center gap-3 p-3 rounded-xl bg-slate-800 hover:bg-slate-700 transition cursor-pointer">
                    <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-sm font-bold">
                        A
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">Administrator</p>
                    </div>
                    <button class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

        </aside>

        <main class="flex-1 h-full overflow-y-auto bg-slate-50 relative">
            <div
                class="md:hidden h-16 bg-slate-900 text-white flex items-center justify-between px-6 sticky top-0 z-30">
                <span class="font-bold">SI PAY ADMIN</span>
                <button class="p-2 bg-slate-800 rounded">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>

            <div class="w-full">
                {{ $slot }}
            </div>
        </main>

    </div>

</body>

</html>