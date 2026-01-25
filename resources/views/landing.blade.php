{{-- <x-layouts.app>
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
                @php
// 1. Set Default ke Login
$targetUrl = route('login');

// 2. Cek apakah ADMIN yang sedang login?
if (Auth::guard('admin')->check()) {
    $targetUrl = route('admin.dashboard');
}
// 3. Cek apakah USER BIASA yang sedang login?
elseif (Auth::guard('web')->check()) {
    $targetUrl = route('dashboard');
}
                @endphp
                <a href="{{ $targetUrl }}"
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

</x-layouts.app> --}}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI PAY</title>

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- AOS Animation Library --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* 1. Mengaktifkan Smooth Scroll untuk seluruh halaman */
        html {
            scroll-behavior: smooth;

            /* 2. SOLUSI PENTING: Scroll Padding Top */
            /* Ini memberi jarak agar judul section tidak tertutup Navbar saat berhenti */
            /* Nilai 6rem (sekitar 96px) disesuaikan dengan tinggi navbar kamu */
            scroll-padding-top: 6rem;
        }

        /* Opsional: Mematikan smooth scroll jika user lebih suka gerakan instan (Accessibility) */
        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }
        }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- Tailwind CSS & Config Warna Logo --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        // Diambil dari Logo Sekolah Impian
                        'si-orange': '#F59E0B', // Bagian Atas S
                        'si-teal': '#10B981',   // Bagian Tengah S
                        'si-blue': '#3B82F6',   // Bagian Bawah S & Tulisan MPIAN
                        'si-dark': '#0F172A',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        blob: {
                            "0%": { transform: "translate(0px, 0px) scale(1)" },
                            "33%": { transform: "translate(30px, -50px) scale(1.1)" },
                            "66%": { transform: "translate(-20px, 20px) scale(0.9)" },
                            "100%": { transform: "translate(0px, 0px) scale(1)" }
                        }
                    }
                }
            }
        }
    </script>
</head>

<body
    class="font-sans text-slate-800 antialiased overflow-x-hidden selection:bg-si-orange selection:text-white bg-white">

    {{-- LOGIKA PHP UNTUK PENENTUAN ARAH TOMBOL --}}
    @php
// 1. Set Default ke Login
$targetUrl = route('login');
$btnText = "Masuk App";

// 2. Cek apakah ADMIN yang sedang login?
if (Auth::guard('admin')->check()) {
    $targetUrl = route('admin.dashboard');
    $btnText = "Dashboard Admin";
}
// 3. Cek apakah USER BIASA yang sedang login?
elseif (Auth::guard('web')->check()) {
    $targetUrl = route('dashboard');
    $btnText = "Dashboard Saya";
}
    @endphp

    {{-- =============================================== --}}
    {{-- 1. NAVBAR --}}
    {{-- =============================================== --}}
    {{-- =============================================== --}}
    {{-- 1. NAVBAR (Responsive & Glassmorphism) --}}
    {{-- =============================================== --}}
    <nav x-data="{ isOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)"
        :class="scrolled ? 'bg-white/90 backdrop-blur-md shadow-sm' : 'bg-transparent'"
        class="fixed w-full z-50 top-0 transition-all duration-300 border-b border-transparent"
        :class="{ 'border-slate-100': scrolled }">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">

                {{-- A. LOGO SECTION --}}
                <div class="flex-shrink-0 flex items-center gap-3 cursor-pointer" onclick="window.scrollTo(0,0)">
                    {{-- Logo Icon --}}
                    <div class="relative w-10 h-10 group">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-si-orange via-si-teal to-si-blue rounded-xl blur-sm opacity-60 group-hover:opacity-100 transition duration-500">
                        </div>
                        <div
                            class="relative w-full h-full bg-white rounded-xl flex items-center justify-center border border-slate-100 shadow-sm">
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-br from-si-orange to-si-blue font-black text-xl">S</span>
                        </div>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-slate-900">
                        Si<span class="text-si-blue">pay</span>
                    </span>
                </div>

                {{-- B. DESKTOP MENU (Hidden on Mobile) --}}
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#solution"
                        class="text-sm font-semibold text-slate-600 hover:text-si-blue transition relative group">
                        Solusi
                        <span
                            class="absolute -bottom-1 left-0 w-0 h-0.5 bg-si-blue transition-all group-hover:w-full"></span>
                    </a>
                    <a href="#features"
                        class="text-sm font-semibold text-slate-600 hover:text-si-blue transition relative group">
                        Fitur
                        <span
                            class="absolute -bottom-1 left-0 w-0 h-0.5 bg-si-blue transition-all group-hover:w-full"></span>
                    </a>
                    <a href="#faq"
                        class="text-sm font-semibold text-slate-600 hover:text-si-blue transition relative group">
                        FAQ
                        <span
                            class="absolute -bottom-1 left-0 w-0 h-0.5 bg-si-blue transition-all group-hover:w-full"></span>
                    </a>
                </div>

                {{-- C. DESKTOP BUTTONS (Hidden on Mobile) --}}
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ $targetUrl }}"
                        class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white transition-all duration-200 bg-slate-900 rounded-full hover:bg-si-blue hover:shadow-lg hover:shadow-blue-500/30 active:scale-95">
                        {{ $btnText }}
                    </a>
                </div>

                {{-- D. MOBILE TOGGLE BUTTON (Visible on Mobile) --}}
                <div class="flex items-center md:hidden">
                    <button @click="isOpen = !isOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-slate-700 hover:text-si-blue hover:bg-slate-100 focus:outline-none transition">
                        <span class="sr-only">Open main menu</span>
                        {{-- Icon Hamburger --}}
                        <i class="fa-solid fa-bars text-xl" x-show="!isOpen" x-transition.opacity></i>
                        {{-- Icon Close --}}
                        <i class="fa-solid fa-xmark text-xl" x-show="isOpen" x-cloak x-transition.opacity></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- E. MOBILE MENU DRAWER --}}
        <div x-show="isOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden bg-white border-b border-slate-100 shadow-xl absolute w-full left-0 z-40" x-cloak>

            <div class="px-4 pt-4 pb-6 space-y-2">
                <a href="#solution" @click="isOpen = false"
                class="block px-4 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-si-blue hover:bg-slate-50 transition">
                <i class="fa-solid fa-lightbulb w-6 text-center"></i> Solusi Kami
                </a>
                <a href="#features" @click="isOpen = false"
                class="block px-4 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-si-blue hover:bg-slate-50 transition">
                <i class="fa-solid fa-bolt w-6 text-center"></i> Fitur Unggulan
                </a>
                <a href="#faq" @click="isOpen = false"
                    class="block px-4 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-si-blue hover:bg-slate-50 transition">
                    <i class="fa-solid fa-circle-question w-6 text-center"></i> Pertanyaan Umum
                </a>

                <div class="border-t border-slate-100 my-2 pt-2"></div>

                <a href="{{ $targetUrl }}"
                    class="block w-full text-center px-4 py-3 rounded-xl text-base font-bold text-white bg-slate-900 hover:bg-si-blue transition shadow-md">
                    {{ $btnText }}
                </a>
                <a href="https://drive.google.com/drive/folders/1gc-pH9oYvdwCWIGLKtjZvsna-RqJ8n0R?usp=sharing" @click="isOpen = false"
                    class="block w-full text-center px-4 py-3 mt-2 rounded-xl text-base font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                    Download Aplikasi
                </a>
            </div>
        </div>
    </nav>

    {{-- =============================================== --}}
    {{-- 2. HERO SECTION --}}
    {{-- =============================================== --}}
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        {{-- Background Gradients (Sesuai Logo) --}}
        <div
            class="absolute top-0 right-0 -mr-20 -mt-20 w-[600px] h-[600px] bg-si-orange/10 rounded-full blur-3xl opacity-50 animate-blob">
        </div>
        <div
            class="absolute top-1/2 left-0 -ml-20 w-[400px] h-[400px] bg-si-teal/10 rounded-full blur-3xl opacity-50 animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute bottom-0 right-1/4 w-[400px] h-[400px] bg-si-blue/10 rounded-full blur-3xl opacity-50 animate-blob animation-delay-4000">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">

                {{-- Text Content --}}
                <div class="text-center lg:text-left z-10">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-50 border border-orange-100 text-si-orange text-xs font-bold uppercase tracking-wide mb-6">
                        <span class="w-2 h-2 rounded-full bg-si-orange animate-pulse"></span>
                        Cashless System #1
                    </div>

                    <h1 class="text-4xl lg:text-6xl font-extrabold text-slate-900 leading-tight mb-6" data-aos="fade-up">
                        Kantin Masa Depan <br>
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-si-orange via-si-teal to-si-blue">Sekolah
                            Impian</span>
                    </h1>

                    <p class="text-lg text-slate-600 mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Smart SI Mart hadir membawa revolusi pembayaran digital. Lebih aman, transparan, dan mendidik
                        siswa mengelola keuangan sejak dini.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="{{ $targetUrl }}"
                            class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-si-blue to-indigo-600 text-white rounded-xl font-bold flex items-center justify-center gap-3 hover:shadow-lg hover:shadow-blue-500/30 transition transform hover:-translate-y-1">
                            <i class="fa-solid fa-rocket"></i>
                            <span>{{ $btnText }}</span>
                        </a>
                        <a href="https://drive.google.com/drive/folders/1gc-pH9oYvdwCWIGLKtjZvsna-RqJ8n0R?usp=sharing"
                            class="w-full sm:w-auto px-8 py-4 bg-white text-slate-900 border border-slate-200 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-slate-50 transition">
                            <i class="fa-brands fa-google-play"></i> Download App
                        </a>
                    </div>

                    <div
                        class="mt-8 flex items-center justify-center lg:justify-start gap-6 text-sm text-slate-500 font-bold">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-si-orange rounded-full"></div> Aman
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-si-teal rounded-full"></div> Cepat
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-si-blue rounded-full"></div> Modern
                        </div>
                    </div>
                </div>

                {{-- PHONE MOCKUP AREA --}}
                <div class="relative flex justify-center" data-aos="fade-left" data-aos-delay="200">

                    {{-- 1. Glow Effect (Cahaya di belakang HP) --}}
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[500px] bg-gradient-to-tr from-si-orange via-si-teal to-si-blue blur-[60px] opacity-30">
                    </div>

                    {{-- 2. Phone Frame --}}
                    <div
                        class="relative w-[300px] h-[600px] bg-slate-900 rounded-[3rem] border-[8px] border-slate-900 shadow-2xl animate-float z-10 overflow-hidden ring-1 ring-white/20">

                        {{-- Notch / Kamera Depan --}}
                        <div
                            class="absolute top-0 left-1/2 transform -translate-x-1/2 h-[25px] w-[120px] bg-slate-900 rounded-b-2xl z-20 pointer-events-none">
                        </div>

                        {{-- 3. SCREEN CONTENT (Tempat Foto) --}}
                        {{-- class 'rounded-[2.5rem]' agar sudut gambar mengikuti lengkungan HP --}}
                        <div class="w-full h-full bg-slate-800 relative overflow-hidden rounded-[2.5rem]">

                            {{-- MASUKKAN FOTO APLIKASI DISINI --}}
                            {{-- Pastikan ukuran foto rasio 9:16 agar pas --}}
                            <img src="{{ asset('img/mockup.jpeg') }}" class="w-full h-full object-cover"
                                alt="Tampilan Aplikasi Smart SI Mart">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =============================================== --}}
    {{-- 3. PROBLEM VS SOLUTION (The "Why") --}}
    {{-- =============================================== --}}
    <section id="solution" class="py-24 bg-slate-50 relative overflow-hidden">
        {{-- Background Pattern --}}
        <div class="absolute top-0 left-0 w-full h-full opacity-30 pointer-events-none"
            style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 32px 32px;">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 mb-4">
                    Tinggalkan Cara Lama yang <span class="text-red-500">Berisiko</span>
                </h2>
                <p class="text-lg text-slate-600">
                    Sistem tunai konvensional seringkali merepotkan. Kami hadir untuk mengubah masalah menjadi kemudahan.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 lg:gap-12 items-center">

                {{-- CARD 1: THE PROBLEM (Cara Lama) --}}
                <div data-aos="fade-right"
                    class="bg-white rounded-[2.5rem] p-8 lg:p-10 shadow-sm border border-slate-100 relative group overflow-hidden">
                    <div
                        class="absolute top-0 right-0 bg-red-100 text-red-600 text-xs font-bold px-6 py-2 rounded-bl-2xl uppercase tracking-wider">
                        Konvensional
                    </div>

                    <div
                        class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center text-red-500 text-3xl mb-8 group-hover:scale-110 transition duration-300">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <h3 class="text-2xl font-bold text-slate-900 mb-6">Masalah Uang Tunai</h3>

                    <ul class="space-y-5">
                        <li class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-xs mt-0.5">
                                <i class="fa-solid fa-xmark"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">Uang Sering Hilang</h4>
                                <p class="text-sm text-slate-500 mt-1">Uang saku siswa rawan jatuh, hilang, atau dicuri saat
                                    di sekolah.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-xs mt-0.5">
                                <i class="fa-solid fa-xmark"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">Penyalahgunaan Uang</h4>
                                <p class="text-sm text-slate-500 mt-1">Orang tua tidak tahu uang saku digunakan untuk jajan
                                    apa (sehat/tidak).</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-xs mt-0.5">
                                <i class="fa-solid fa-xmark"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">Antrian Kantin Lama</h4>
                                <p class="text-sm text-slate-500 mt-1">Waktu istirahat habis hanya untuk mengantri kembalian
                                    uang receh.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                {{-- CARD 2: THE SOLUTION (Sekolah Impian) --}}
                <div data-aos="fade-left"
                    class="bg-slate-900 rounded-[2.5rem] p-8 lg:p-10 shadow-2xl relative overflow-hidden group transform md:-translate-y-4 border border-slate-700">
                    {{-- Glow Effect --}}
                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-si-teal rounded-full blur-[80px] opacity-20 group-hover:opacity-30 transition">
                    </div>
                    <div
                        class="absolute bottom-0 left-0 w-64 h-64 bg-si-blue rounded-full blur-[80px] opacity-20 group-hover:opacity-30 transition">
                    </div>

                    <div
                        class="absolute top-0 right-0 bg-gradient-to-r from-si-teal to-si-blue text-white text-xs font-bold px-6 py-2 rounded-bl-2xl uppercase tracking-wider shadow-lg">
                        Solusi Cerdas
                    </div>

                    <div
                        class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center text-si-teal text-3xl mb-8 group-hover:scale-110 transition duration-300 border border-white/10">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <h3 class="text-2xl font-bold text-white mb-6">Ekosistem Smart SI</h3>

                    <ul class="space-y-5 relative z-10">
                        <li class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-si-teal flex items-center justify-center text-slate-900 text-xs mt-0.5 font-bold">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Aman & Terjamin</h4>
                                <p class="text-sm text-slate-400 mt-1">Saldo tersimpan digital dengan PIN. Kartu hilang?
                                    Uang tetap aman di akun.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-si-blue flex items-center justify-center text-white text-xs mt-0.5 font-bold">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Monitoring Real-time</h4>
                                <p class="text-sm text-slate-400 mt-1">Notifikasi instan ke HP orang tua setiap kali siswa
                                    melakukan transaksi.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-si-orange flex items-center justify-center text-white text-xs mt-0.5 font-bold">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Transaksi Kilat (QRIS)</h4>
                                <p class="text-sm text-slate-400 mt-1">Scan QR Code dalam hitungan detik. Tanpa ribet cari
                                    uang kembalian.</p>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    {{-- =============================================== --}}
    {{-- 4. FEATURES (Multi-Color Icons) --}}
    {{-- =============================================== --}}
    <section id="features" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900">Fitur SI PAY</h2>
                <p class="text-slate-500 mt-2">Didesain khusus untuk kebutuhan ekosistem keuangan.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- Feature 1 (Orange Theme) --}}
                <div data-aos="zoom-in" data-aos-delay="100"
                    class="p-8 rounded-3xl bg-white shadow-sm hover:shadow-xl transition duration-300 border-b-4 border-si-orange group">
                    <div
                        class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-si-orange text-2xl mb-6 group-hover:scale-110 transition">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Transaksi Kilat</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Bayar jajan tinggal scan QRIS. Tidak perlu bawa
                        uang tunai, tidak perlu tunggu kembalian.</p>
                </div>

                {{-- Feature 2 (Teal Theme) --}}
                <div data-aos="zoom-in" data-aos-delay="200"
                    class="p-8 rounded-3xl bg-white shadow-sm hover:shadow-xl transition duration-300 border-b-4 border-si-teal group">
                    <div
                        class="w-14 h-14 bg-teal-50 rounded-2xl flex items-center justify-center text-si-teal text-2xl mb-6 group-hover:scale-110 transition">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Monitoring Orang Tua</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Orang tua bisa memantau riwayat jajan siswa dan
                        mengatur limit harian lewat dashboard.</p>
                </div>

                {{-- Feature 3 (Blue Theme) --}}
                <div data-aos="zoom-in" data-aos-delay="300"
                    class="p-8 rounded-3xl bg-white shadow-sm hover:shadow-xl transition duration-300 border-b-4 border-si-blue group">
                    <div
                        class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-si-blue text-2xl mb-6 group-hover:scale-110 transition">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Manajemen Kantin</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Laporan penjualan otomatis untuk merchant. Stok
                        terkontrol, pembukuan jadi rapi.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- =============================================== --}}
    {{-- 5. SECTION: CARA KERJA (How It Works) --}}
    {{-- =============================================== --}}
    <section class="py-20 bg-white border-t border-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900">Mulai dalam 3 Langkah</h2>
                <p class="text-slate-500 mt-2">Tidak perlu proses ribet. Langsung aktif dalam hitungan menit.</p>
            </div>

            <div class="relative grid md:grid-cols-3 gap-8">
                {{-- Connecting Line (Hidden on Mobile) --}}
                <div
                    class="hidden md:block absolute top-12 left-[16%] right-[16%] h-0.5 bg-gradient-to-r from-si-orange via-si-teal to-si-blue border-t-2 border-dashed border-slate-200 -z-10">
                </div>

                {{-- Step 1 --}}
                <div class="relative text-center group">
                    <div
                        class="w-24 h-24 mx-auto bg-white border-4 border-si-orange rounded-full flex items-center justify-center text-3xl font-bold text-si-orange mb-6 shadow-lg group-hover:scale-110 transition duration-300 relative z-10">
                        1
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Daftar Akun</h3>
                    <p class="text-sm text-slate-500 px-4">Admin sekolah akan mendaftarkan akun siswa & memberikan kartu
                        identitas digital.</p>
                </div>

                {{-- Step 2 --}}
                <div class="relative text-center group">
                    <div
                        class="w-24 h-24 mx-auto bg-white border-4 border-si-teal rounded-full flex items-center justify-center text-3xl font-bold text-si-teal mb-6 shadow-lg group-hover:scale-110 transition duration-300 relative z-10">
                        2
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Isi Saldo (Top Up)</h3>
                    <p class="text-sm text-slate-500 px-4">Top up saldo melalui Admin Sekolah atau transfer bank. Saldo
                        langsung masuk real-time.</p>
                </div>

                {{-- Step 3 --}}
                <div class="relative text-center group">
                    <div
                        class="w-24 h-24 mx-auto bg-white border-4 border-si-blue rounded-full flex items-center justify-center text-3xl font-bold text-si-blue mb-6 shadow-lg group-hover:scale-110 transition duration-300 relative z-10">
                        3
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Siap Jajan!</h3>
                    <p class="text-sm text-slate-500 px-4">Gunakan aplikasi atau Kartu Siswa untuk scan QRIS di kantin
                        sekolah. Selesai!</p>
                </div>
            </div>
        </div>
    </section>

    {{-- =============================================== --}}
    {{-- 6. SECTION: FAQ (Accordion Style) --}}
    {{-- =============================================== --}}
    <section id="faq" class="py-20 bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-slate-900">Pertanyaan Umum</h2>
                <p class="text-slate-500 mt-2">Hal yang sering ditanyakan oleh orang tua dan siswa.</p>
            </div>

            <div class="space-y-4" x-data="{ active: null }">

                {{-- FAQ Item 1 --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="active = (active === 1 ? null : 1)"
                        class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none bg-white hover:bg-slate-50 transition">
                        <span class="font-bold text-slate-800">Apakah uang di aplikasi aman jika HP hilang?</span>
                        <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300"
                            :class="active === 1 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="active === 1" x-collapse
                        class="px-6 pb-4 pt-0 text-slate-600 text-sm leading-relaxed border-t border-slate-100 bg-slate-50/50">
                        <p class="mt-4"><strong>Sangat Aman.</strong> Saldo tersimpan di server cloud kami, bukan di HP.
                            Selama Anda mengingat email dan password, atau segera melapor ke admin sekolah untuk memblokir
                            akun, saldo Anda tetap utuh 100%.</p>
                    </div>
                </div>

                {{-- FAQ Item 2 --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="active = (active === 2 ? null : 2)"
                        class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none bg-white hover:bg-slate-50 transition">
                        <span class="font-bold text-slate-800">Bagaimana cara orang tua memantau jajan anak?</span>
                        <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300"
                            :class="active === 2 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="active === 2" x-collapse
                        class="px-6 pb-4 pt-0 text-slate-600 text-sm leading-relaxed border-t border-slate-100 bg-slate-50/50">
                        <p class="mt-4">Orang tua dapat login ke aplikasi menggunakan akun yang terhubung dengan siswa. Di
                            sana tersedia menu "Riwayat Transaksi" yang menampilkan detail jam, lokasi merchant, dan menu
                            yang dibeli secara real-time.</p>
                    </div>
                </div>

                {{-- FAQ Item 3 --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="active = (active === 3 ? null : 3)"
                        class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none bg-white hover:bg-slate-50 transition">
                        <span class="font-bold text-slate-800">Apakah ada biaya admin saat Top Up?</span>
                        <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300"
                            :class="active === 3 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="active === 3" x-collapse
                        class="px-6 pb-4 pt-0 text-slate-600 text-sm leading-relaxed border-t border-slate-100 bg-slate-50/50">
                        <p class="mt-4">Top Up melalui <strong>Admin Sekolah (Tunai) Gratis</strong> tanpa biaya admin. Jika
                            Top Up melalui transfer bank atau e-wallet, biaya admin mengikuti kebijakan masing-masing bank
                            (biasanya Rp 0 - Rp 2.500).</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Tambahkan AlpineJS untuk fungsi klik FAQ --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- =============================================== --}}
    {{-- 7. CTA SECTION (Logika PHP diterapkan disini juga) --}}
    {{-- =============================================== --}}
    <section id="download" class="py-20 bg-white relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 relative z-10">
            <div class="bg-slate-900 rounded-[3rem] p-10 md:p-16 text-center shadow-2xl relative overflow-hidden group">
                {{-- Animated Gradient Background --}}
                <div
                    class="absolute inset-0 bg-gradient-to-r from-si-orange/20 via-si-teal/20 to-si-blue/20 opacity-0 group-hover:opacity-100 transition duration-1000">
                </div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-si-blue rounded-full blur-[100px] opacity-30"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-si-orange rounded-full blur-[100px] opacity-30"></div>

                <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-6">Mulai Transformasi Digital</h2>
                <p class="text-slate-300 text-lg mb-10 max-w-xl mx-auto">
                    Bergabunglah dengan ratusan siswa dan merchant yang telah merasakan kemudahan bertransaksi di
                    SI PAY.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10">
                    {{-- TOMBOL LOGIS DI SINI --}}
                    <a href="{{ $targetUrl }}"
                        class="w-full sm:w-auto py-4 px-8 bg-white text-slate-900 font-bold rounded-xl shadow-lg hover:bg-slate-50 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        @if(Auth::guard('admin')->check() || Auth::guard('web')->check())
                            <i class="fa-solid fa-gauge-high"></i> BUKA DASHBOARD
                        @else
                            <i class="fa-solid fa-right-to-bracket"></i> MASUK WEB APP
                        @endif
                    </a>

                    <a href="https://drive.google.com/drive/folders/1gc-pH9oYvdwCWIGLKtjZvsna-RqJ8n0R?usp=sharing"
                        class="w-full sm:w-auto py-4 px-8 bg-slate-800 text-white font-bold rounded-xl border border-slate-700 hover:bg-slate-700 transition flex items-center justify-center gap-2">
                        <i class="fa-brands fa-google-play"></i>
                        <span>Download APK</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- =============================================== --}}
    {{-- 5. FOOTER --}}
    {{-- =============================================== --}}
    <footer class="bg-white pt-16 pb-8 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2">
                    {{-- Logo Kecil Footer --}}
                    <div
                        class="w-8 h-8 bg-gradient-to-br from-si-orange to-si-blue rounded-lg flex items-center justify-center text-white font-bold">
                        S</div>
                    <span class="font-bold text-lg text-slate-900">SI<span
                            class="text-si-blue">PAY</span></span>
                </div>
                <div class="text-slate-500 text-sm">
                    &copy; {{ date('Y') }} Smart SI Mart. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800, // Durasi animasi
            once: true,    // Animasi hanya sekali saat scroll ke bawah
            offset: 100,   // Jarak trigger animasi dari bawah layar
        });
    </script>

</body>

</html>