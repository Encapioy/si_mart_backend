<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    {{-- PERBAIKAN 1: Tambahkan viewport-fit=cover agar support layar poni/notch iPhone --}}
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>SI PAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body wire:poll.10s
    class="bg-gray-50 text-slate-800 antialiased font-sans selection:bg-blue-100 selection:text-blue-600">

    {{-- PERBAIKAN 2: Padding bawah diperbesar (pb-32) agar konten tidak tertutup navbar di iPhone --}}
    <main class="min-h-screen pb-32 relative z-0">
        {{ $slot }}
    </main>

    @auth
        {{-- PERBAIKAN: Menambahkan Logika Slide Down saat Modal Buka --}}
        <div id="userbar" x-data="{ hideNav: false }" @toggle-nav.window="hideNav = $event.detail"
            :class="hideNav ? 'translate-y-[20vh]' : 'translate-y-0'"
            class="fixed bottom-0 left-0 z-50 w-full bg-white/90 backdrop-blur-md border-t border-gray-200 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] rounded-t-[2rem] pb-[env(safe-area-inset-bottom)] transition-transform duration-300 ease-in-out">

            {{-- Grid Menu tetap h-20 (80px) --}}
            <div class="relative grid h-20 max-w-lg grid-cols-4 mx-auto font-medium z-50">

                <a href="{{ route('dashboard') }}" wire:navigate
                    class="inline-flex flex-col items-center justify-center px-5 group">
                    <div
                        class="p-1.5 rounded-xl transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 translate-y-[-2px]' : 'text-gray-400 hover:text-gray-600' }}">
                        <svg class="w-6 h-6 transition-transform duration-300 {{ request()->routeIs('dashboard') ? 'scale-110' : '' }}"
                            fill="{{ request()->routeIs('dashboard') ? 'currentColor' : 'none' }}" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                    </div>
                    <span
                        class="text-[10px] mt-1 font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400' }}">
                        Beranda
                    </span>
                </a>

                <div class="relative flex items-center justify-center">
                    <a href="{{ route('scan') }}"
                        class="absolute -top-6 bg-gradient-to-br from-blue-600 to-blue-700 text-white p-4 rounded-2xl shadow-lg shadow-blue-600/40 border-4 border-gray-50 transform transition-transform duration-200 active:scale-95 hover:-translate-y-1">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                            </path>
                        </svg>
                    </a>
                    <span
                        class="absolute bottom-3 text-[10px] font-semibold {{ request()->routeIs('scan') ? 'text-blue-600' : 'text-gray-400' }}">
                        Scan
                    </span>
                </div>

                <a href="{{ route('history') }}" wire:navigate
                    class="inline-flex flex-col items-center justify-center px-5 group">
                    <div
                        class="p-1.5 rounded-xl transition-all duration-300 {{ request()->routeIs('history') ? 'bg-blue-50 text-blue-600 translate-y-[-2px]' : 'text-gray-400 hover:text-gray-600' }}">
                        <svg class="w-6 h-6 transition-transform duration-300 {{ request()->routeIs('history') ? 'scale-110' : '' }}"
                            fill="none" stroke="currentColor" stroke-width="{{ request()->routeIs('history') ? '2.5' : '2' }}"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </div>
                    <span
                        class="text-[10px] mt-1 font-semibold transition-colors {{ request()->routeIs('history') ? 'text-blue-600' : 'text-gray-400' }}">
                        Riwayat
                    </span>
                </a>

                <a href="{{ route('profile') }}" wire:navigate
                    class="inline-flex flex-col items-center justify-center px-5 group">
                    <div
                        class="p-1.5 rounded-xl transition-all duration-300 {{ request()->routeIs('profile') ? 'bg-blue-50 text-blue-600 translate-y-[-2px]' : 'text-gray-400 hover:text-gray-600' }}">
                        <svg class="w-6 h-6 transition-transform duration-300 {{ request()->routeIs('profile') ? 'scale-110' : '' }}"
                            fill="{{ request()->routeIs('profile') ? 'currentColor' : 'none' }}" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                            </path>
                        </svg>
                    </div>
                    <span
                        class="text-[10px] mt-1 font-semibold transition-colors {{ request()->routeIs('profile') ? 'text-blue-600' : 'text-gray-400' }}">
                        Akun
                    </span>
                </a>

            </div>
        </div>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>