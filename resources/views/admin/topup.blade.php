<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Top Up | SI PAY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Style untuk list autocomplete */
        .autocomplete-items {
            position: absolute;
            border: 1px solid #e2e8f0;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border-radius: 0 0 0.5rem 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #e2e8f0;
        }
        .autocomplete-items div:hover {
            background-color: #f1f5f9;
        }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden flex bg-white text-slate-800">

    <div class="w-1/2 h-full flex flex-col justify-center items-center relative p-12 bg-white z-10">

        <div class="w-full max-w-md">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Top Up Saldo</h1>
                <p class="text-slate-500 text-sm">Minimal transaksi Rp 100.000</p>
            </div>

            <form id="topupForm" onsubmit="confirmTransaction(event)" autocomplete="off">

                <div class="mb-5 relative group">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Cari Username Siswa</label>
                    <div class="flex items-center bg-slate-50 rounded-xl px-4 py-3 border border-transparent focus-within:border-blue-500 focus-within:bg-white transition">
                        <i class="fa-solid fa-user-graduate text-slate-400 mr-3"></i>
                        <input type="text" id="target_username" class="bg-transparent w-full text-sm font-bold focus:outline-none text-slate-800"
                        placeholder="Ketik nama atau username..." oninput="searchUserAPI(this.value)">
                    </div>
                    <div id="userList" class="autocomplete-items hidden"></div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nominal (Min 100k)</label>
                    <div class="flex items-center bg-slate-50 rounded-xl px-4 py-3 border border-transparent focus-within:border-emerald-500 focus-within:bg-white transition mb-2">
                        <span class="text-slate-400 font-bold mr-3 text-sm">Rp</span>
                        <input type="number" id="amount" class="bg-transparent w-full text-lg font-bold focus:outline-none text-emerald-700 placeholder-slate-300"
                        placeholder="0" min="100000">
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="setAmount(100000)" class="flex-1 py-2 text-xs font-bold bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition">100k</button>
                        <button type="button" onclick="setAmount(150000)" class="flex-1 py-2 text-xs font-bold bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition">150k</button>
                        <button type="button" onclick="setAmount(200000)" class="flex-1 py-2 text-xs font-bold bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition">200k</button>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 mb-1">NAMA KASIR</label>
                        <div class="relative">
                            <select id="cashier_id" class="w-full bg-slate-50 rounded-lg px-3 py-2 text-sm border border-transparent focus:bg-white focus:border-slate-300 focus:outline-none appearance-none cursor-pointer font-medium">
                                <option value="" disabled selected>Pilih Nama...</option>
                                </select>
                            <i class="fa-solid fa-chevron-down absolute right-3 top-3 text-xs text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 mb-1">PIN KASIR</label>
                        <input type="password" id="cashier_pin" maxlength="6" class="w-full bg-slate-50 rounded-lg px-3 py-2 text-sm border border-transparent focus:bg-white focus:border-slate-300 focus:outline-none text-center tracking-widest font-bold" placeholder="******">
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-bold py-3.5 rounded-xl shadow-lg transition transform active:scale-[0.98]">
                    PROSES TOP UP
                </button>
            </form>
        </div>

        <p class="absolute bottom-6 text-[10px] text-slate-300">Protected System | v2.0</p>
    </div>

    <div class="w-1/2 h-full flex flex-col border-l border-slate-100 bg-slate-50">
        <div class="h-2/5 bg-slate-900 p-10 text-white flex flex-col justify-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-blue-500 rounded-full blur-3xl opacity-20 -mr-10 -mt-10"></div>

            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Status Keuangan</h2>
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h1 class="text-3xl font-bold">Rp 145.2jt</h1>
                    <p class="text-slate-500 text-xs mt-1">Uang Beredar</p>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-emerald-400">Online</h1>
                    <p class="text-slate-500 text-xs mt-1">Database Status</p>
                </div>
            </div>
        </div>
        <div class="h-3/5 p-8 flex items-center justify-center text-slate-300">
            <div class="text-center">
                <i class="fa-solid fa-chart-pie text-4xl mb-2"></i>
                <p class="text-sm">Menunggu Transaksi Baru...</p>
            </div>
        </div>
    </div>

    <script>
        // 1. SAAT HALAMAN DIMUAT: AMBIL DAFTAR KASIR
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const res = await fetch('/admin/ajax/cashiers', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const cashiers = await res.json();

                const select = document.getElementById('cashier_id');
                cashiers.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.innerText = c.nama_admin;
                    select.appendChild(opt);
                });
            } catch (err) {
                console.error("Gagal ambil data kasir", err);
            }
        });

        // 2. FITUR AUTOCOMPLETE USERNAME
            let timeout = null;

            async function searchUserAPI(query) {
                const list = document.getElementById('userList');

                // UBAH 1: Izinkan pencarian mulai dari 1 huruf biar lebih responsif
                if (query.length < 1) {
                    list.classList.add('hidden');
                    list.innerHTML = '';
                    return;
                }

                // UBAH 2: Tampilkan status "Sedang mencari..." sebelum request jalan
                list.classList.remove('hidden');
                list.innerHTML = '<div class="text-xs text-slate-400 p-3 italic">Mencari...</div>';

                clearTimeout(timeout);
                timeout = setTimeout(async () => {
                    try {
                        // Pastikan URL ini sesuai dengan route di web.php
                        const res = await fetch(`/admin/ajax/search-user?q=${query}`, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });

                        // Cek jika error dari server (Misal 404 atau 500)
                        if (!res.ok) {
                            console.error("Server Error:", res.status);
                            list.innerHTML = '<div class="text-xs text-red-500 p-3">Terjadi kesalahan server.</div>';
                            return;
                        }

                        const users = await res.json();
                        list.innerHTML = ''; // Hapus tulisan "Mencari..."

                        if (users.length > 0) {
                            users.forEach(u => {
                                const item = document.createElement('div');
                                // Pastikan key JSON sesuai controller ('username' & 'nama_lengkap')
                                item.innerHTML = `
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-700 text-sm">${u.username}</span>
                            <span class="text-[10px] text-slate-400 uppercase">${u.nama_lengkap}</span>
                        </div>
                    `;
                                // Saat diklik
                                item.onclick = () => {
                                    document.getElementById('target_username').value = u.username;
                                    list.classList.add('hidden'); // Sembunyikan list
                                };
                                list.appendChild(item);
                            });
                        } else {
                            // UBAH 3: Tampilkan pesan jika user tidak ditemukan (Jangan di-hidden)
                            list.innerHTML = '<div class="text-xs text-slate-400 p-3">User tidak ditemukan.</div>';
                        }
                    } catch (e) {
                        console.error(e);
                        list.innerHTML = '<div class="text-xs text-red-500 p-3">Gagal memuat data.</div>';
                    }
                }, 300);
            }

        // 3. FITUR QUICK AMOUNT
        function setAmount(val) {
            document.getElementById('amount').value = val;
        }

        // 4. KONFIRMASI & SUBMIT
        async function confirmTransaction(e) {
            e.preventDefault();

            // Ambil Data
            const username = document.getElementById('target_username').value;
            const amount = document.getElementById('amount').value;
            const cashierId = document.getElementById('cashier_id').value;
            const cashierName = document.getElementById('cashier_id').options[document.getElementById('cashier_id').selectedIndex]?.text;
            const pin = document.getElementById('cashier_pin').value;

            // Validasi Sederhana
            if(!username || !amount || !cashierId || !pin) {
                Swal.fire('Error', 'Mohon lengkapi semua form!', 'error');
                return;
            }
            if(amount < 100000) {
                Swal.fire('Error', 'Minimal Top Up Rp 100.000!', 'warning');
                return;
            }

            // TAMPILKAN POPUP KONFIRMASI
            const result = await Swal.fire({
                title: 'Konfirmasi Top Up',
                html: `
                    <div class="text-left text-sm">
                        <p class="mb-1">Tujuan: <b>${username}</b></p>
                        <p class="mb-1">Nominal: <b class="text-emerald-600">Rp ${parseInt(amount).toLocaleString('id-ID')}</b></p>
                        <p>Kasir: <b>${cashierName}</b></p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#d33',
                confirmButtonText: 'YA, KIRIM SEKARANG',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                processTopUp(username, amount, cashierId, pin);
            }
        }

        // 5. PROSES KE SERVER
        async function processTopUp(username, amount, cashierId, pin) {
            // Show Loading
            Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });

            try {
                const response = await fetch('/api/admin/topup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Pakai Token Auth Laravel
                    },
                    body: JSON.stringify({
                        target_username: username,
                        amount: amount,
                        cashier_id: cashierId,
                        cashier_pin: pin
                    })
                });

                const res = await response.json();

                if (!response.ok) throw new Error(res.message || 'Gagal');

                // Sukses
                Swal.fire('Berhasil!', `Saldo berhasil dikirim ke ${res.data.penerima}`, 'success');

                // Reset Form
                document.getElementById('topupForm').reset();

            } catch (error) {
                Swal.fire('Gagal!', error.message, 'error');
            }
        }
    </script>
</body>
</html>