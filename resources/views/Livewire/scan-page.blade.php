<div class="relative flex flex-col justify-center h-screen bg-black overflow-hidden">

    {{-- TOMBOL KEMBALI --}}
    <a href="{{ route('dashboard') }}" wire:navigate
        class="absolute z-50 p-3 transition rounded-full top-6 left-6 text-white bg-black/40 backdrop-blur hover:bg-black/60 border border-white/10 shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </a>

    {{-- AREA SCANNER --}}
    <div wire:ignore class="w-full h-full relative">

        {{-- 1. LOADER (Muncul saat inisialisasi kamera) --}}
        <div id="camera-loading" class="absolute inset-0 flex flex-col items-center justify-center bg-black z-20">
            <svg class="animate-spin h-10 w-10 text-emerald-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-white text-sm font-medium animate-pulse">Menyiapkan Kamera...</p>
        </div>

        {{-- 2. ERROR STATE (Muncul jika kamera ditolak) --}}
        <div id="camera-error" class="hidden absolute inset-0 flex-col items-center justify-center bg-black z-30 p-6 text-center">
            <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mb-4">
                <i class="fa-solid fa-camera-slash text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-white font-bold text-lg mb-2">Kamera Tidak Aktif</h3>
            <p class="text-gray-400 text-sm mb-6">Pastikan kamu memberikan izin akses kamera atau coba gunakan fitur upload gambar.</p>
            <button onclick="window.location.reload()" class="px-6 py-2 bg-white text-black rounded-full font-bold text-sm hover:bg-gray-200 transition">
                Coba Lagi
            </button>
        </div>

        {{-- 3. READER CONTAINER --}}
        <div id="reader" class="w-full h-full bg-black"></div>
    </div>

    {{-- OVERLAY UI --}}
    <div class="absolute w-full flex flex-col items-center pointer-events-none bottom-10 z-40 gap-4">

        {{-- Frame Kotak (Visual Guide) --}}
        <div class="w-64 h-64 border-2 border-white/30 rounded-3xl relative mb-4">
            <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-emerald-500 rounded-tl-xl -mt-1 -ml-1"></div>
            <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-emerald-500 rounded-tr-xl -mt-1 -mr-1"></div>
            <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-emerald-500 rounded-bl-xl -mb-1 -ml-1"></div>
            <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-emerald-500 rounded-br-xl -mb-1 -mr-1"></div>
        </div>

        <span class="px-4 py-2 text-xs font-medium text-white border rounded-full bg-black/60 backdrop-blur-md border-white/10 shadow-xl">
            Arahkan ke QR Code
        </span>

        {{-- FITUR UPLOAD GAMBAR (Fallback Penting!) --}}
        <div class="pointer-events-auto">
            <input type="file" id="qr-input-file" accept="image/*" class="hidden" onchange="scanFromFile(this)">
            <button onclick="document.getElementById('qr-input-file').click()"
                class="text-white text-xs flex items-center gap-2 opacity-70 hover:opacity-100 transition mt-2">
                <i class="fa-regular fa-image"></i> Scan dari Galeri
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

        // Konstanta ID Elemen
        const elReader = "reader";
        const elLoader = document.getElementById('camera-loading');
        const elError = document.getElementById('camera-error');

        // Fungsi Utama: Mulai Scanner
        const initScanner = async () => {
            // Reset UI
            elLoader.classList.remove('hidden');
            elError.classList.add('hidden');

            // Cek apakah library termuat
            if (typeof Html5Qrcode === "undefined") {
                console.error("Library Html5Qrcode belum siap.");
                return;
            }

            try {
                // 1. Dapatkan daftar kamera dulu (Supaya bisa pilih kamera belakang yang BENAR)
                const devices = await Html5Qrcode.getCameras();

                if (devices && devices.length) {
                    // Cari kamera belakang (biasanya ID terakhir atau label mengandung 'back'/'environment')
                    let cameraId = devices[0].id; // Default

                    // Logic memilih kamera belakang yang lebih pintar
                    const backCamera = devices.find(device =>
                        device.label.toLowerCase().includes('back') ||
                        device.label.toLowerCase().includes('environment')
                    );

                    if (backCamera) {
                        cameraId = backCamera.id;
                    } else if (devices.length > 1) {
                        // Jika tidak ada label 'back', biasanya kamera terakhir adalah kamera belakang di Android
                        cameraId = devices[devices.length - 1].id;
                    }

                    // 2. Inisialisasi Instance
                    // verbose: false agar console tidak penuh spam
                    html5QrCode = new Html5Qrcode(elReader, { verbose: false });

                    // 3. Start Scanning
                    await html5QrCode.start(
                        cameraId,
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 },
                            aspectRatio: window.innerWidth / window.innerHeight // Penting agar tidak gepeng
                        },
                        (decodedText, decodedResult) => {
                            onScanSuccess(decodedText);
                        },
                        (errorMessage) => {
                            // Abaikan error scanning frame kosong
                        }
                    );

                    // Sembunyikan loader jika berhasil start
                    elLoader.classList.add('hidden');

                } else {
                    // Tidak ada kamera ditemukan
                    throw new Error("No camera found");
                }
            } catch (err) {
                console.error("Error Camera:", err);
                elLoader.classList.add('hidden');
                elError.classList.remove('hidden');
            }
        };

        // Fungsi saat QR berhasil dibaca
        const onScanSuccess = (text) => {
            console.log("QR Found:", text);

            // Pause scanner dulu biar gak trigger berkali-kali
            if(html5QrCode) html5QrCode.pause();

            // Logic Navigasi
            if (text.startsWith('SIPAY:STORE:')) {
                let parts = text.split(':');
                Livewire.navigate('/pay/' + parts[2]);
            } else if (/^\d+$/.test(text) && text.length >= 10) {
                Livewire.navigate('/transfer/' + text);
            } else {
                // Jika QR Salah
                alert("QR Code tidak valid!");
                // Resume scanner jika user klik OK
                if(html5QrCode) html5QrCode.resume();
            }
        };

        // Fungsi Scan dari File/Galeri (Fallback)
        window.scanFromFile = (input) => {
            if (input.files.length === 0) return;

            const imageFile = input.files[0];

            // Gunakan instance baru atau yang sudah ada (tapi logic file scan beda class)
            // Kita pakai Html5QrcodeScanner static method untuk file
            const html5QrCodeFile = new Html5Qrcode(elReader);

            html5QrCodeFile.scanFile(imageFile, true)
                .then(decodedText => {
                    onScanSuccess(decodedText);
                })
                .catch(err => {
                    alert("Gagal membaca QR dari gambar. Pastikan gambar jelas.");
                    console.error(err);
                });
        };

        // EVENT LISTENER LIVEWIRE (Kunci agar tidak error di SPA)

        // 1. Saat halaman selesai dimuat (Navigasi SPA)
        document.addEventListener('livewire:navigated', () => {
            // Beri sedikit delay agar DOM benar-benar siap
            setTimeout(() => {
                initScanner();
            }, 300);
        });

        // 2. Bersihkan kamera saat pindah halaman (PENTING!)
        document.addEventListener('livewire:navigating', () => {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                }).catch(err => console.log('Failed to stop', err));
            }
        });

    </script>
    @endscript

    <style>
        /* Styling Video agar Fullscreen & Tidak Gepeng */
        #reader {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important; /* Kunci agar full screen */
        }
    </style>
</div>