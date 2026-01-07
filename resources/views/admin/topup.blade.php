<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Finance | SI PAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Hilangkan scrollbar default */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
    </style>
</head>

<body class="h-screen w-screen overflow-hidden flex bg-white text-slate-800">

    <div class="w-1/2 h-full flex flex-col justify-center items-center relative p-12">

        <div class="absolute top-8 left-10 flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">S</div>
            <span class="font-semibold tracking-tight text-slate-900">SI PAY <span
                    class="text-slate-400 font-normal">Finance</span></span>
        </div>

        <div class="w-full max-w-md">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Top Up Saldo</h1>
                <p class="text-slate-500 text-sm mt-1">Isi saldo siswa/user dengan cepat dan aman.</p>
            </div>

            <form id="topupForm" onsubmit="handleTopUp(event)" autocomplete="off">

                <div class="mb-5 group">
                    <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Target User</label>
                    <div
                        class="flex items-center bg-slate-50 rounded-xl px-4 py-3 transition-all group-focus-within:ring-2 group-focus-within:ring-blue-500/20 group-focus-within:bg-white border border-transparent group-focus-within:border-blue-500">
                        <i class="fa-solid fa-address-book text-slate-400 mr-3"></i>

                        <input type="text" id="target_user"
                            class="bg-transparent border-none w-full text-sm font-medium focus:outline-none text-slate-700 placeholder-slate-400"
                            placeholder="Ketik No HP / Email / Scan Kartu" required>
                    </div>
                </div>

                <div class="mb-6 group">
                    <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Nominal Top
                        Up</label>
                    <div
                        class="flex items-center bg-slate-50 rounded-xl px-4 py-3 transition-all group-focus-within:ring-2 group-focus-within:ring-emerald-500/20 group-focus-within:bg-white border border-transparent group-focus-within:border-emerald-500">
                        <span class="text-slate-400 font-semibold mr-3 text-sm">Rp</span>
                        <input type="number" id="amount"
                            class="bg-transparent border-none w-full text-lg font-bold tracking-wide focus:outline-none text-slate-800 placeholder-slate-300"
                            placeholder="0" min="1000" oninput="calculateTotal()" required>
                    </div>

                    <div
                        class="mt-3 bg-white border border-slate-100 rounded-lg p-3 flex justify-between items-center shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-slate-400 uppercase">Biaya Admin</span>
                            <span class="text-xs font-semibold text-slate-600">Rp 100</span>
                        </div>
                        <div class="h-6 w-px bg-slate-100 mx-2"></div>
                        <div class="flex flex-col text-right">
                            <span class="text-[10px] text-slate-400 uppercase">Diterima User</span>
                            <span id="preview_receive" class="text-sm font-bold text-emerald-600">Rp 0</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="col-span-2 group">
                        <label class="block text-[10px] font-bold text-slate-400 mb-1">ADMIN USERNAME</label>
                        <input type="text" id="admin_username"
                            class="w-full bg-slate-50 rounded-lg px-3 py-2 text-sm border border-transparent focus:bg-white focus:border-slate-300 focus:outline-none transition"
                            placeholder="Username" required>
                    </div>
                    <div class="col-span-1 group">
                        <label class="block text-[10px] font-bold text-slate-400 mb-1">PIN</label>
                        <input type="password" id="admin_pin" maxlength="6"
                            class="w-full bg-slate-50 rounded-lg px-3 py-2 text-sm border border-transparent focus:bg-white focus:border-slate-300 focus:outline-none transition text-center tracking-widest"
                            placeholder="•••" required>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-slate-900 hover:bg-black text-white font-medium py-3.5 rounded-xl shadow-lg shadow-slate-200 transition-all active:scale-[0.98] flex justify-center items-center gap-2 text-sm">
                    <span>Konfirmasi Transaksi</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div id="alertBox" class="hidden mt-4 p-3 rounded-lg text-xs font-medium text-center animate-pulse"></div>
        </div>

        <p class="absolute bottom-6 text-[10px] text-slate-300">Protected by 256-bit Encryption</p>
    </div>

    <div class="w-1/2 h-full flex flex-col border-l border-slate-100">

        <div class="h-[40%] bg-slate-900 p-10 flex flex-col justify-center relative">
            <h3 class="text-slate-400 text-xs font-semibold uppercase tracking-widest mb-8">Overview Hari Ini</h3>

            <div class="grid grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Rp 145.2jt</h2>
                    <p class="text-slate-500 text-xs mt-1">Total Perputaran Uang</p>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-emerald-400 tracking-tight">Rp 52.3rb</h2>
                    <p class="text-slate-500 text-xs mt-1">Accumulated Fee (Admin)</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-800 rounded-md text-white"><i class="fa-solid fa-receipt"></i></div>
                    <div>
                        <p class="text-white font-semibold text-lg">1,204</p>
                        <p class="text-slate-500 text-[10px]">Transaksi Sukses</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-800 rounded-md text-blue-400"><i class="fa-solid fa-server"></i></div>
                    <div>
                        <p class="text-blue-400 font-semibold text-lg">Online</p>
                        <p class="text-slate-500 text-[10px]">VPS Connection</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="h-[60%] bg-slate-50 p-8 overflow-hidden flex flex-col">
            <div class="flex justify-between items-end mb-6">
                <div>
                    <h3 class="text-slate-800 font-bold text-lg">Live Reports</h3>
                    <p class="text-slate-400 text-xs">Laporan kendala user secara realtime.</p>
                </div>
                <div class="flex items-center gap-1">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-red-500 ml-1">LIVE</span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-2 custom-scrollbar">

                <div
                    class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-start gap-3 hover:shadow-md transition cursor-pointer">
                    <div class="mt-1 min-w-[8px] h-2 rounded-full bg-orange-400"></div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-semibold text-slate-800">Transaksi Pending (Kantin)</h4>
                            <span class="text-[10px] text-slate-400">2m</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">User <span class="text-slate-700 font-medium">Ahmad (XII
                                RPL)</span> melaporkan saldo terpotong tapi struk tidak keluar.</p>
                    </div>
                </div>

                <div
                    class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-start gap-3 hover:shadow-md transition cursor-pointer">
                    <div class="mt-1 min-w-[8px] h-2 rounded-full bg-blue-400"></div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-semibold text-slate-800">Lupa PIN</h4>
                            <span class="text-[10px] text-slate-400">15m</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">User <span class="text-slate-700 font-medium">Siti (XI
                                TKJ)</span> meminta reset PIN.</p>
                    </div>
                </div>

                <div
                    class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-start gap-3 hover:shadow-md transition cursor-pointer opacity-70">
                    <div class="mt-1 min-w-[8px] h-2 rounded-full bg-slate-300"></div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-semibold text-slate-600">NFC Tidak Terbaca</h4>
                            <span class="text-[10px] text-slate-400">1h</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Masalah pada alat reader Pos 2.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // 1. Hitung Preview Nominal
        function calculateTotal() {
            const amount = document.getElementById('amount').value;
            const preview = document.getElementById('preview_receive');

            if (amount >= 100) {
                const received = amount - 100;
                preview.innerText = "Rp " + parseInt(received).toLocaleString('id-ID');
            } else {
                preview.innerText = "Rp 0";
            }
        }

        // 2. Handle Submit (REAL REQUEST)
        async function handleTopUp(e) {
            e.preventDefault();

            const btn = e.target.querySelector('button');
            const originalText = btn.innerHTML;
            const alertBox = document.getElementById('alertBox');

            // Loading State
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> MEMPROSES...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.disabled = true;

            // Ambil Data Form
            const inputData = {
                target_user: document.getElementById('target_user').value,
                amount: document.getElementById('amount').value,
                admin_username: document.getElementById('admin_username').value,
                admin_pin: document.getElementById('admin_pin').value,
            };

            try {
                // TEMBAK KE API VPS (Pastikan URL-nya benar)
                // Karena kita di server yang sama, pakai relative path '/api/admin/topup' aman.
                const response = await fetch('/api/admin/topup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                        // KITA HAPUS HEADER 'Authorization' KARENA SEDANG DIMATIKAN
                    },
                    body: JSON.stringify(inputData)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Terjadi kesalahan server');
                }

                // SUKSES
                alertBox.className = "mt-4 p-3 rounded-lg text-xs font-medium text-center bg-emerald-50 text-emerald-600 border border-emerald-100";
                alertBox.innerHTML = `<strong><i class="fa-solid fa-check-circle"></i> Berhasil!</strong> ${result.message}`;
                alertBox.classList.remove('hidden');

                // Reset Form
                document.getElementById('topupForm').reset();
                document.getElementById('preview_receive').innerText = "Rp 0";

            } catch (error) {
                // GAGAL
                console.error(error);
                alertBox.className = "mt-4 p-3 rounded-lg text-xs font-medium text-center bg-red-50 text-red-600 border border-red-100";
                alertBox.innerHTML = `<strong><i class="fa-solid fa-triangle-exclamation"></i> Gagal:</strong> ${error.message}`;
                alertBox.classList.remove('hidden');
            } finally {
                // Reset Tombol
                btn.innerHTML = originalText;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
                btn.disabled = false;
                setTimeout(() => { alertBox.classList.add('hidden'); }, 5000);
            }
        }
    </script>
</body>

</html>