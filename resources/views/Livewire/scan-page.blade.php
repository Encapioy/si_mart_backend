<div class="relative flex flex-col justify-center h-screen bg-black">
    
    <a href="{{ route('dashboard') }}" wire:navigate
        class="absolute z-50 p-2 transition rounded-full top-6 left-6 text-white bg-white/20 backdrop-blur hover:bg-white/30">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </a>

    <div wire:ignore class="w-full h-full">
        <div id="reader" class="w-full h-full"></div>
    </div>

    <div class="absolute w-full text-center pointer-events-none bottom-24">
        <span class="px-4 py-2 text-sm text-white border rounded-full bg-black/50 backdrop-blur border-white/20">
            Arahkan ke QR Toko atau Member Card
        </span>
    </div>

    @assets
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    @endassets

    @script
    <script>
        let html5Qrcode;

        // Fungsi inisialisasi kamera
        const startScanner = () => {
            html5Qrcode = new Html5Qrcode("reader");

            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            // Mulai Scanner (Pakai Environment/Kamera Belakang)
            html5Qrcode.start(
                { facingMode: "environment" },
                config,
                (decodedText, decodedResult) => {
                    // --- SUKSES SCAN ---
                    console.log("QR Terbaca:", decodedText);

                    // Matikan kamera agar tidak scan ganda
                    html5Qrcode.stop().then(() => {
                        handleScanResult(decodedText);
                    });
                },
                (errorMessage) => {
                    // Scan gagal (biasa terjadi saat mencari QR), abaikan saja
                }
            ).catch(err => {
                console.error("Gagal membuka kamera:", err);
                alert("Izin kamera diperlukan untuk fitur ini.");
            });
        };

        // Logika Pemilahan QR
        const handleScanResult = (text) => {
            // 1. CEK QR TOKO (Format: SIPAY:STORE:...)
            if (text.startsWith('SIPAY:STORE:')) {
                let parts = text.split(':');
                let storeId = parts[2]; // Ambil ID

                // Gunakan Livewire.navigate biar smooth (SPA)
                Livewire.navigate('/pay/' + storeId);
            }
            // 2. CEK QR USER (Angka 10+ digit)
            else if (/^\d+$/.test(text) && text.length >= 10) {
                Livewire.navigate('/transfer/' + text);
            }
            // 3. QR TIDAK VALID
            else {
                alert("QR Code tidak dikenali!");
                // Nyalakan kamera lagi jika mau scan ulang
                window.location.reload();
            }
        };

        // Jalankan saat komponen dimuat
        startScanner();

        // Bersihkan saat user meninggalkan halaman (PENTING di SPA)
        document.addEventListener('livewire:navigating', () => {
            if (html5Qrcode) {
                html5Qrcode.stop().catch(err => console.log('Stop failed', err));
            }
        });
    </script>
    @endscript

    <style>
        /* Paksa video memenuhi layar */
        #reader video {
            object-fit: cover;
            width: 100% !important;
            height: 100vh !important;
            border-radius: 0 !important;
        }

        /* Hilangkan mirroring untuk kamera belakang agar tulisan QR terbaca */
        video {
            transform: none !important;
        }
    </style>
</div>