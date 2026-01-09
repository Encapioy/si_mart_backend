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
        document.addEventListener('DOMContentLoaded', () => {
            const onScanSuccess = (decodedText) => {
                // Format: SIPAY:STORE:45:NamaToko
                if (decodedText.startsWith('SIPAY:STORE:')) {
                    html5QrcodeScanner.clear();
                    let storeId = decodedText.split(':')[2];
                    window.location.href = "/pay/" + storeId; // Redirect ke halaman bayar
                }
            };
            const html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 }, false);
            html5QrcodeScanner.render(onScanSuccess);
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