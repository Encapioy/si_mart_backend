<div class="h-screen bg-black relative flex flex-col justify-center">
    <a href="{{ route('dashboard') }}"
        class="absolute top-6 left-6 z-50 text-white bg-white/20 p-2 rounded-full backdrop-blur">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </a>

    <div id="reader" class="w-full h-full object-cover"></div>

    <div class="absolute bottom-20 w-full text-center pointer-events-none">
        <span class="bg-black/50 text-white px-4 py-2 rounded-full text-sm backdrop-blur">Arahkan ke QR Toko</span>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => { // Ganti jadi livewire:initialized biar aman
            const onScanSuccess = (decodedText, decodedResult) => {
                // 1. DEBUGGING: Munculkan isi QR di layar biar ketahuan isinya apa
                console.log("Hasil Scan:", decodedText);

                // Cek apakah formatnya benar
                if (decodedText.startsWith('SIPAY:STORE:')) {
                    // Matikan kamera dulu biar gak berat
                    html5QrcodeScanner.clear();

                    // Ambil ID (Split tulisan berdasarkan titik dua)
                    let parts = decodedText.split(':');
                    let storeId = parts[2]; // Ambil angka ID-nya

                    // Redirect
                    window.location.href = "/pay/" + storeId;
                }
                // 2. JIKA FORMAT SALAH (Misal user scan QR GoPay atau QR link biasa)
                else {
                    alert("QR Code Terbaca: " + decodedText + "\n\nTapi format salah! Harus diawali SIPAY:STORE:");
                }
            };

            const onScanFailure = (error) => {
                // Biarkan kosong biar gak spam error di console saat kamera lagi nyari fokus
                // console.warn(`Code scan error = ${error}`);
            };

            // Config kamera
            let config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0

                videoConstraints: {
                    facingMode: "environment"
                }
            };

            const html5QrcodeScanner = new Html5QrcodeScanner("reader", config, /* verbose= */ false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        });
    </script>
    <style>
        #reader {
            border: none !important;
        }

        video {
            object-fit: cover;
            height: 100vh !important;
        }
    </style>
</div>