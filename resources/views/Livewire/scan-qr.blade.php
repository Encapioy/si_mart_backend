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
    // Gunakan event listener Livewire biar lebih stabil saat loading
    document.addEventListener('livewire:initialized', () => {

        const onScanSuccess = (decodedText, decodedResult) => {
            // DEBUG: Cek isi QR di Console browser (opsional)
            console.log("QR Terbaca:", decodedText);

            // Format yang diharapkan: SIPAY:STORE:45:NamaToko
            if (decodedText.startsWith('SIPAY:STORE:')) {
                // 1. Matikan kamera biar gak berat
                html5QrcodeScanner.clear();

                // 2. Ambil ID Toko (Split string berdasarkan titik dua)
                let parts = decodedText.split(':');
                let storeId = parts[2]; // Index 2 adalah ID-nya

                // 3. Pindah ke halaman bayar
                window.location.href = "/pay/" + storeId;
            }
            else {
                // INI BAGIAN PENTING BUAT DEBUGGING
                // Kalau QR terbaca tapi format salah, dia bakal teriak.
                // Jadi kamu tau kameranya jalan, cuma QR-nya yang salah.
                alert("QR Code Terbaca: " + decodedText + "\n\nFormat salah! QR Toko SI Pay harus diawali 'SIPAY:STORE:'");
            }
        };

        const onScanFailure = (error) => {
            // Biarkan kosong atau console.warn aja biar gak spam alert saat kamera cari fokus
            // console.warn(`Code scan error = ${error}`);
        };

        // --- KONFIGURASI SCANNER ---
        let config = {
            fps: 10, // Scan 10 frame per detik
            qrbox: { width: 250, height: 250 }, // Ukuran kotak fokus

            // PAKSA KAMERA BELAKANG
            videoConstraints: {
                facingMode: "environment"
            }
        };

        // Inisialisasi Scanner
        // Parameter ketiga 'false' artinya jangan tampilkan pesan log yang berisik
        const html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);

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