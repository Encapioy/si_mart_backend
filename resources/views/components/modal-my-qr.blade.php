@php
    $user = auth()->user();
    // Pastikan variabel memberId tersedia, jika tidak ada fallback ke string kosong agar tidak error
    $memberId = $user->member_id ?? '';
@endphp

{{-- 2. MODAL POPUP --}}
<div id="modal-qr" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeQrModal()"></div>

    <div class="absolute inset-x-0 bottom-0 md:inset-0 md:flex md:items-center md:justify-center pointer-events-none">
        <div id="modal-qr-content"
            class="bg-white w-full md:w-[400px] rounded-t-[2rem] md:rounded-[2rem] p-0 relative pointer-events-auto transform transition-transform duration-300 translate-y-full md:translate-y-0 overflow-hidden">

            {{-- Header --}}
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

            {{-- Body --}}
            <div class="px-6 pb-8 -mt-10 relative z-20">
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-100 mb-6">
                    <div class="flex justify-center mb-4">
                        {{-- Wadah QR --}}
                        <div id="qrcode-canvas" class="p-2 border-2 border-dashed border-blue-100 rounded-xl"></div>
                    </div>

                    <div class="text-center">
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">Member ID</p>
                        <div onclick="copyId()"
                            class="bg-slate-50 py-2 px-4 rounded-lg border border-slate-100 flex items-center justify-center gap-2 cursor-pointer active:scale-95 transition">
                            <span class="font-mono text-lg font-bold text-slate-700 tracking-wider">
                                {{ $memberId }}
                            </span>
                            <i class="fa-regular fa-copy text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <button onclick="downloadQr()"
                    class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold text-sm shadow-lg shadow-slate-900/20 hover:bg-black transition flex items-center justify-center gap-2 active:scale-[0.98]">
                    <i class="fa-solid fa-download"></i> Simpan ke Galeri
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #userbar {
    /* Pastikan transisi aktif untuk semua perubahan transform */
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
}
</style>

@assets
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endassets

<script>
    // 1. Gunakan 'var' atau cek 'window' object agar tidak error saat navigasi SPA
    // Ini akan mencegah error "Identifier has already been declared"
    if (typeof window.qrGenerated === 'undefined') {
        window.qrGenerated = false;
    }

    // 2. Gunakan window.namaFungsi agar fungsi tetap bisa diakses global
    // meskipun halaman ditukar oleh Livewire
    window.openQrModal = function() {
        const qrValue = @json($memberId);

        if (!qrValue) {
            alert("Member ID tidak ditemukan!");
            return;
        }

        const modal = document.getElementById('modal-qr');
        const content = document.getElementById('modal-qr-content');
        const userbar = document.getElementById('userbar');

        modal.classList.remove('hidden');
        setTimeout(() => content.classList.remove('translate-y-full'), 10);

        if (userbar) userbar.classList.add('translate-y-[20vh]');

        // Gunakan window.qrGenerated sebagai flag
        if (!window.qrGenerated && typeof QRCode !== 'undefined') {
            const canvasEl = document.getElementById("qrcode-canvas");
            if (canvasEl) {
                canvasEl.innerHTML = "";
                new QRCode(canvasEl, {
                    text: qrValue,
                    width: 180,
                    height: 180,
                    colorDark: "#1e293b",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                window.qrGenerated = true;
            }
        }
    };

    window.closeQrModal = function() {
        const modal = document.getElementById('modal-qr');
        const content = document.getElementById('modal-qr-content');
        const userbar = document.getElementById('userbar');

        if (content) content.classList.add('translate-y-full');
        if (userbar) userbar.classList.remove('translate-y-[20vh]');

        setTimeout(() => {
            if (modal) modal.classList.add('hidden');
        }, 300);
    };

    window.downloadQr = function() {
        const img = document.querySelector('#qrcode-canvas img');
        if (img) {
            const link = document.createElement('a');
            link.href = img.src;
            link.download = 'QR-MEMBER-{{ $memberId }}.png';
            link.click();
        }
    };

    window.copyId = function() {
        const qrValue = @json($memberId);
        navigator.clipboard.writeText(qrValue);
        alert('ID disalin!');
    };
</script>