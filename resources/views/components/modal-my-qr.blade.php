@php
    $user = auth()->user();
    // Pastikan data tersedia (fallback jika null karena database baru reset)
    $memberId = $user->member_id ?? 'MEMBER-001';
    $userName = $user->nama_lengkap ?? 'Nama Pengguna'; // Kita butuh nama untuk ditampilkan di kartu
    $nmid = 'ID' . rand(1000000000000, 9999999999999); // Simulasi NMID
@endphp

{{-- 1. MODAL POPUP (Tampilan UI untuk User) --}}
<div id="modal-qr" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeQrModal()"></div>

    <div class="absolute inset-x-0 bottom-0 md:inset-0 md:flex md:items-center md:justify-center pointer-events-none">
        <div id="modal-qr-content"
            class="bg-white w-full md:w-[400px] rounded-t-[2rem] md:rounded-[2rem] p-0 relative pointer-events-auto transform transition-transform duration-300 translate-y-full md:translate-y-0 overflow-hidden">

            {{-- Header Modal --}}
            <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 pt-8 pb-16 px-6 text-center">
                <h3 class="text-xl font-bold text-white relative z-10">QR Code User</h3>
                <p class="text-blue-100 text-xs mt-1 relative z-10">Gunakan Member ID untuk transaksi</p>
                <button onclick="closeQrModal()"
                    class="absolute top-4 right-4 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-md transition">
                    <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            {{-- Body Modal --}}
            <div class="px-6 pb-8 -mt-10 relative z-20">
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-100 mb-6">
                    <div class="flex justify-center mb-4">
                        {{-- Wadah QR untuk dilihat di Layar HP --}}
                        <div id="qrcode-canvas" class="p-2 border-2 border-dashed border-blue-100 rounded-xl"></div>
                    </div>

                    <div class="text-center">
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">Member ID</p>
                        <div onclick="copyId()"
                            class="bg-slate-50 py-2 px-4 rounded-lg border border-slate-100 flex items-center justify-center gap-2 cursor-pointer active:scale-95 transition">
                            <span
                                class="font-mono text-lg font-bold text-slate-700 tracking-wider">{{ $memberId }}</span>
                            <i class="fa-regular fa-copy text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Tombol Download yang sudah diupdate fungsinya --}}
                <button onclick="downloadQrStruk()" id="btn-download"
                    class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold text-sm shadow-lg shadow-slate-900/20 hover:bg-black transition flex items-center justify-center gap-2 active:scale-[0.98]">
                    <i class="fa-solid fa-download"></i> Simpan Gambar
                </button>
            </div>
        </div>
    </div>
</div>

{{--
2. TEMPLATE KARTU TERSEMBUNYI (HIDDEN TEMPLATE)
Bagian ini tidak terlihat user (left: -9999px), tapi inilah yang akan "difoto"
oleh script menjadi gambar PNG. Desainnya dibuat mirip SI PAY.
--}}
<div style="position: fixed; left: -9999px; top: 0; z-index: -1;">
    <div id="si-pay-card"
        class="w-[375px] bg-white rounded-xl overflow-hidden font-sans relative flex flex-col items-center"
        style="font-family: sans-serif;">

        <div class="p-8 pb-4 w-full flex flex-col items-center bg-white">
            {{-- Logo Header --}}
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 border-2 border-[#00897B] rounded text-[#00897B] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-[#00897B] tracking-tight">SI PAY</h1>
            </div>

            <div class="w-full h-[2px] bg-[#00897B] mb-6"></div>
            <p class="text-sm font-semibold tracking-[0.1em] text-gray-800 mb-2">SCAN UNTUK BAYAR</p>

            {{-- Nama User --}}
            <h2 class="text-2xl font-bold text-black uppercase mb-6 text-center leading-tight">
                {{ $userName }}
            </h2>

            {{-- Tempat QR Code Khusus Cetak (High Res) --}}
            <div class="p-1 border border-gray-200 rounded-lg mb-6 bg-white">
                <div id="qrcode-print-target"></div>
            </div>

            <p class="text-[10px] text-gray-400 font-mono tracking-wide">NMID: {{ $nmid }}</p>
            <p class="text-[10px] text-gray-400 font-medium mb-4">Powered by SI PAY</p>
        </div>

        {{-- Footer Merah --}}
        <div class="w-full bg-[#C62828] text-white text-center py-3 mt-auto">
            <p class="text-[10px] font-bold tracking-wider">GPN - GERBANG PEMBAYARAN NASIONAL</p>
        </div>
    </div>
</div>

<style>
    #userbar {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
    }
</style>

{{-- 3. ASSETS & SCRIPT --}}
@assets
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
{{-- Tambahkan html2canvas --}}
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
@endassets

<script>
    if (typeof window.qrGenerated === 'undefined') window.qrGenerated = false;

    window.openQrModal = function () {
        const qrValue = @json($memberId);
        if (!qrValue) { alert("Member ID belum ada!"); return; }

        const modal = document.getElementById('modal-qr');
        const content = document.getElementById('modal-qr-content');
        const userbar = document.getElementById('userbar');

        modal.classList.remove('hidden');
        setTimeout(() => content.classList.remove('translate-y-full'), 10);
        if (userbar) userbar.classList.add('translate-y-[20vh]');

        if (!window.qrGenerated && typeof QRCode !== 'undefined') {
            // GENERATE 1: Untuk Tampilan Modal
            const canvasEl = document.getElementById("qrcode-canvas");
            if (canvasEl) {
                canvasEl.innerHTML = "";
                new QRCode(canvasEl, {
                    text: qrValue, width: 180, height: 180,
                    colorDark: "#1e293b", colorLight: "#ffffff", correctLevel: QRCode.CorrectLevel.H
                });
            }

            // GENERATE 2: Untuk Template Cetak (SI PAY)
            // Kita buat ini lebih besar dan hitam pekat agar hasil download tajam
            const printEl = document.getElementById("qrcode-print-target");
            if (printEl) {
                printEl.innerHTML = "";
                new QRCode(printEl, {
                    text: qrValue, width: 220, height: 220,
                    colorDark: "#000000", colorLight: "#ffffff", correctLevel: QRCode.CorrectLevel.H
                });
            }
            window.qrGenerated = true;
        }
    };

    window.closeQrModal = function () {
        const modal = document.getElementById('modal-qr');
        const content = document.getElementById('modal-qr-content');
        const userbar = document.getElementById('userbar');

        if (content) content.classList.add('translate-y-full');
        if (userbar) userbar.classList.remove('translate-y-[20vh]');

        setTimeout(() => {
            if (modal) modal.classList.add('hidden');
        }, 300);
    };

    // FUNGSI BARU: Render gambar menggunakan html2canvas
    window.downloadQrStruk = function () {
        const btn = document.getElementById('btn-download');
        const originalText = btn.innerHTML;

        // Kasih loading state
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
        btn.disabled = true;

        const elementToCapture = document.getElementById('si-pay-card');

        html2canvas(elementToCapture, {
            scale: 3, // Resolusi tinggi (3x) agar tidak pecah
            backgroundColor: null,
            useCORS: true
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'QR-SI-PAY-{{ Str::slug($userName) }}.png';
            link.href = canvas.toDataURL("image/png");
            link.click();

            btn.innerHTML = originalText;
            btn.disabled = false;
        }).catch(err => {
            console.error(err);
            alert("Gagal membuat gambar.");
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    };

    window.copyId = function () {
        const qrValue = @json($memberId);
        navigator.clipboard.writeText(qrValue);
        alert('ID disalin!');
    };
</script>