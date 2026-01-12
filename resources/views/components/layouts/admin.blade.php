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

        [x-cloak] {
            display: none !important;
        }

        /* Custom Scrollbar yang rapi */
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

<body wire:poll.10s class="bg-slate-50 text-slate-800 antialiased overflow-hidden" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen w-full">

        <aside class="w-72 bg-slate-900 text-white flex-col shadow-2xl z-20 hidden md:flex">
            <div class="h-20 flex items-center px-8 border-b border-slate-800/50 shrink-0">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <span class="font-bold text-lg">SI</span>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg tracking-tight">SI PAY</h1>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">Admin Panel</p>
                    </div>
                </div>
            </div>

            @include('components.layouts.partials.admin-menu')

            @include('components.layouts.partials.admin-footer')
        </aside>


        <div x-show="sidebarOpen" x-transition.opacity
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 md:hidden" @click="sidebarOpen = false"></div>

        <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 w-72 bg-slate-900 text-white z-50 flex flex-col shadow-2xl md:hidden">

            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
                <span class="font-bold text-xl">Menu</span>
                <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            @include('components.layouts.partials.admin-menu')

            @include('components.layouts.partials.admin-footer')
        </div>


        <main class="flex-1 h-full overflow-y-auto bg-slate-50 relative flex flex-col">

            <div
                class="md:hidden h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sticky top-0 z-30 shadow-sm shrink-0">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xs">
                        SI</div>
                    <span class="font-bold text-slate-700">Admin Panel</span>
                </div>

                <button @click="sidebarOpen = true"
                    class="p-2 bg-slate-100 rounded-lg text-slate-600 hover:bg-slate-200 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>

            <div class="flex-1 w-full p-4 md:p-8">
                {{ $slot }}
            </div>
        </main>

    </div>
    @livewireScripts
    @stack('scripts')
</body>

</html>