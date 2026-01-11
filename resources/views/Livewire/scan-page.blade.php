<div class="h-screen bg-black relative flex flex-col justify-center">
    <a href="{{ route('dashboard') }}"
        class="absolute top-6 left-6 z-50 text-white bg-white/20 p-2 rounded-full backdrop-blur hover:bg-white/30 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </a>

    <div id="reader" class="w-full h-full object-cover"></div>

    <div class="absolute bottom-20 w-full text-center pointer-events-none">
        <span class="bg-black/50 text-white px-4 py-2 rounded-full text-sm backdrop-blur border border-white/20">
            Arahkan ke QR Toko atau Teman
        </span>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {

            const onScanSuccess = (decodedText, decodedResult) => {
                console.log("QR Terbaca:", decodedText);

                // --- LOGIKA PEMILAHAN QR ---

                // 1. CEK QR TOKO (Format: SIPAY:STORE:...)
                if (decodedText.startsWith('SIPAY:STORE:')) {
                    // Hentikan kamera
                    html5QrcodeScanner.clear();

                    // Ambil ID Toko (Format: SIPAY:STORE:ID:NAMA)
                    let parts = decodedText.split(':');
                    let storeId = parts[2];

                    // Redirect ke Halaman Bayar Toko
                    // Pastikan route ini ada di web.php: Route::get('/pay/{storeId}', ...)
                    window.location.href = "/pay/" + storeId;
                }

                // 2. CEK QR USER (Format: Angka Murni 12 Digit, misal: 202612345678)
                // Logika: Apakah isinya hanya angka? DAN panjangnya >= 10?
                else if (/^\d+$/.test(decodedText) && decodedText.length >= 10) {
                    // Hentikan kamera
                    html5QrcodeScanner.clear();

                    let memberId = decodedText;

                    // Redirect ke Halaman Transfer User
                    // Pastikan route ini ada di web.php: Route::get('/transfer/{memberId}', ...)
                    window.location.href = "/transfer/" + memberId;
                }

                // 3. QR TIDAK DIKENALI
                else {
                    // Alert sementara untuk debugging, nanti bisa diganti SweetAlert
                    alert("QR Code tidak valid!\n\nIsi: " + decodedText);
                }
            };

            const onScanFailure = (error) => {
                // Biarkan kosong biar console bersih
            };

            // --- KONFIGURASI SCANNER ---
            let config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                videoConstraints: {
                    facingMode: "environment" // Kamera Belakang
                }
            };

            const html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        });
    </script>

    <style>
        /* Styling agar kamera full screen dan rapi */
        #reader {
            border: none !important;
            width: 100%;
            height: 100%;
        }

        #reader__scan_region {
            min-height: 100vh;
        }

        video {
            object-fit: cover;
            height: 100vh !important;
            width: 100vw !important;
            transform: scaleX(-1);
            /* Opsional: Mirroring kalau perlu */
        }

        /* Sembunyikan tombol bawaan library yang mengganggu */
        #reader__dashboard_section_csr span,
        #reader__dashboard_section_swaplink {
            display: none !important;
        }
    </style>
</div>