<div class="relative flex flex-col justify-center h-screen bg-black overflow-hidden">

    {{-- TOMBOL KEMBALI --}}
    <a href="{{ route('dashboard') }}" wire:navigate
        class="absolute z-50 p-3 transition rounded-full top-6 left-6 text-white bg-black/40 backdrop-blur hover:bg-black/60 border border-white/10 shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </a>

    {{-- AREA SCANNER --}}
    <div wire:ignore class="w-full h-full relative bg-black">

        {{-- 1. LOADER --}}
        <div id="camera-loading" class="absolute inset-0 flex flex-col items-center justify-center bg-black z-20">
            <svg class="animate-spin h-10 w-10 text-emerald-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <p class="text-white text-sm font-medium animate-pulse">Menyiapkan Kamera...</p>
        </div>

        {{-- 2. ERROR STATE --}}
        <div id="camera-error"
            class="hidden absolute inset-0 flex flex-col items-center justify-center bg-black z-30 p-6 text-center">
            <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mb-4">
                <i class="fa-solid fa-camera-slash text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-white font-bold text-lg mb-2">Kamera Tidak Aktif</h3>
            <p class="text-gray-400 text-sm mb-6">Pastikan izin kamera diaktifkan di pengaturan browser Anda.</p>
            <button onclick="window.location.reload()"
                class="px-6 py-2 bg-white text-black rounded-full font-bold text-sm hover:bg-gray-200 transition">
                Muat Ulang
            </button>
        </div>

        {{-- 3. READER CONTAINER --}}
        {{-- Penting: overflow-hidden disini agar video tidak keluar container --}}
        <div id="reader" class="w-full h-full overflow-hidden"></div>
    </div>

    {{-- OVERLAY UI (Bingkai Fokus) --}}
    {{-- <div class="absolute inset-0 pointer-events-none z-10 flex items-center justify-center">
        <div class="w-64 h-64 border-2 border-white/50 rounded-xl relative">
            <div class="absolute top-0 left-0 w-4 h-4 border-l-4 border-t-4 border-emerald-500 -mt-1 -ml-1"></div>
            <div class="absolute top-0 right-0 w-4 h-4 border-r-4 border-t-4 border-emerald-500 -mt-1 -mr-1"></div>
            <div class="absolute bottom-0 left-0 w-4 h-4 border-l-4 border-b-4 border-emerald-500 -mb-1 -ml-1"></div>
            <div class="absolute bottom-0 right-0 w-4 h-4 border-r-4 border-b-4 border-emerald-500 -mb-1 -mr-1"></div>

            <div class="absolute top-0 left-0 w-full h-0.5 bg-emerald-500 shadow-[0_0_10px_#10b981] animate-scan"></div>
        </div>
    </div> --}}

    {{-- BOTTOM CONTROLS --}}
    <div class="absolute w-full flex flex-col items-center justify-center bottom-12 z-40 pointer-events-none">

        {{-- Hint Text (Sangat tipis & rapi) --}}
        <p class="text-white/60 text-[10px] font-semibold tracking-[0.2em] uppercase mb-4">
            Arahkan kamera dengan stabil
        </p>

        {{-- Upload File Button --}}
        <div class="pointer-events-auto">
            <input type="file" id="qr-input-file" accept="image/*" class="hidden" onchange="scanFromFile(this)">

            <button onclick="document.getElementById('qr-input-file').click()"
                class="group flex items-center gap-3 px-5 py-2.5 rounded-full bg-black/30 backdrop-blur-md border border-white/20 hover:bg-white hover:border-white transition-all duration-300 ease-out active:scale-95">

                {{-- Icon (Berubah warna saat hover) --}}
                <i class="fa-regular fa-image text-white group-hover:text-black transition-colors duration-300 text-lg"></i>

                {{-- Text --}}
                <span class="text-sm font-medium text-white group-hover:text-black transition-colors duration-300">
                    Ambil dari Galeri
                </span>
            </button>
        </div>
    </div>

    {{-- Load Library --}}
    @assets
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    @endassets

    @script
    <script>
        let html5QrCode;
        const elReader = "reader";
        const elLoader = document.getElementById('camera-loading');
        const elError = document.getElementById('camera-error');
        let isProcessing = false; // Flag untuk mencegah scan ganda

        // CONFIG SCANNER YANG LEBIH STABIL
        const qrConfig = {
            fps: 10, // Frame per second
            qrbox: { width: 250, height: 250 }, // Area scan (kotak tengah)
            // PENTING: Jangan set aspectRatio! Biarkan browser menyesuaikan native sensor.
            // aspectRatio: 1.0
        };

        const initScanner = async () => {
            elLoader.classList.remove('hidden');
            elError.classList.add('hidden');
            isProcessing = false;

            if (typeof Html5Qrcode === "undefined") {
                console.error("Library Html5Qrcode belum siap.");
                return;
            }

            // Hentikan instance lama jika ada (mencegah memory leak)
            if (html5QrCode) {
                try { await html5QrCode.stop(); } catch (e) { }
            }

            html5QrCode = new Html5Qrcode(elReader);

            try {
                // METODE TERBAIK UNTUK MOBILE (iOS & Android):
                // Langsung minta facingMode: "environment" daripada list devices.
                // Ini memaksa browser memilih kamera belakang utama secara otomatis.
                await html5QrCode.start(
                    {
                        facingMode: "environment"
                    },
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        // Video Constraints: Minta resolusi HD agar tidak buram
                        videoConstraints: {
                            facingMode: "environment",
                            width: { min: 640, ideal: 1280, max: 1920 },
                            height: { min: 480, ideal: 720, max: 1080 },
                            focusMode: "continuous" // Penting untuk Android
                        }
                    },
                    (decodedText, decodedResult) => {
                        handleScanSuccess(decodedText);
                    },
                    (errorMessage) => {
                        // Abaikan error scanning frame kosong
                    }
                );

                elLoader.classList.add('hidden');

                // Hack CSS untuk memperbaiki tampilan video agar fullscreen (cover)
                // Kita force element video agar object-fit cover
                setTimeout(() => {
                    const videoElement = document.querySelector(`#${elReader} video`);
                    if (videoElement) {
                        videoElement.style.objectFit = "cover";
                        videoElement.style.width = "100%";
                        videoElement.style.height = "100%";
                    }
                }, 500);

            } catch (err) {
                console.error("Error starting scanner:", err);
                elLoader.classList.add('hidden');
                elError.classList.remove('hidden');
            }
        };

        const handleScanSuccess = (text) => {
            if (isProcessing) return; // Cegah double process
            isProcessing = true;
            console.log("QR Found:", text);

            // Pause scanner segera
            if (html5QrCode) html5QrCode.pause();

            // Logic Navigasi
            if (text.startsWith('SIPAY:STORE:')) {
                let parts = text.split(':');
                Livewire.navigate('/pay/' + parts[2]);
            } else if (/^\d+$/.test(text) && text.length >= 10) {
                Livewire.navigate('/transfer/' + text);
            } else {
                // Gunakan SweetAlert jika ada, atau alert biasa
                // Disini pakai alert biasa agar ringan
                alert("QR Code tidak dikenali!");
                isProcessing = false;
                if (html5QrCode) html5QrCode.resume();
            }
        };

        // Scan dari File
        window.scanFromFile = (input) => {
            if (input.files.length === 0) return;
            const imageFile = input.files[0];

            // Perlu instance Html5QrcodeScanner untuk file, atau reuse Html5Qrcode
            // Untuk keamanan, kita stop kamera dulu jika sedang jalan
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => processFile(imageFile));
            } else {
                processFile(imageFile);
            }
        };

        const processFile = (file) => {
            const html5QrCodeFile = new Html5Qrcode(elReader);
            html5QrCodeFile.scanFile(file, true)
                .then(decodedText => {
                    handleScanSuccess(decodedText);
                })
                .catch(err => {
                    alert("QR Code tidak terbaca dari gambar ini.");
                    console.error(err);
                    // Restart kamera jika gagal
                    initScanner();
                });
        };

        // LIFECYCLE LIVEWIRE
        document.addEventListener('livewire:navigated', () => {
            // Delay sedikit untuk memastikan container render
            setTimeout(() => {
                initScanner();
            }, 100);
        });

        // Cleanup saat pindah halaman
        document.addEventListener('livewire:navigating', () => {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                }).catch(err => console.log('Stop failed', err));
            }
        });

    </script>
    @endscript

    <style>
        /* Animasi Garis Scan */
        @keyframes scan-move {
            0% {
                top: 0%;
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                top: 100%;
                opacity: 0;
            }
        }

        .animate-scan {
            animation: scan-move 2s infinite linear;
        }

        /* Override CSS bawaan library agar tidak bentrok */
        #reader__scan_region {
            display: none !important;
            /* Sembunyikan garis kuning bawaan library */
        }
    </style>
</div>