<div class="space-y-6">

    {{-- SECTION 1: KARTU RINGKASAN UTAMA --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Total Uang Beredar --}}
        <div
            class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
            <p class="text-slate-300 text-xs font-bold uppercase tracking-widest mb-1">Total Uang Beredar</p>
            <h2 class="text-3xl font-extrabold">Rp {{ number_format($totalUangBeredar, 0, ',', '.') }}</h2>
            <p class="text-xs text-slate-400 mt-2">Akumulasi Saldo User + Merchant</p>
        </div>

        {{-- Saldo di User --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-blue-50 text-blue-600 p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span
                    class="text-xs font-bold bg-blue-100 text-blue-700 px-2 py-1 rounded-full">{{ $persenUser }}%</span>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Di Dompet Siswa/User</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">Rp {{ number_format($totalUserSaldo, 0, ',', '.') }}</h3>
        </div>

        {{-- Saldo di Merchant --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-orange-50 text-orange-600 p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <span
                    class="text-xs font-bold bg-orange-100 text-orange-700 px-2 py-1 rounded-full">{{ $persenMerchant }}%</span>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Di Dompet Merchant</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">Rp
                {{ number_format($totalMerchantBalance, 0, ',', '.') }}</h3>
        </div>
    </div>

    {{-- SECTION 2: CHART & TOP LIST --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- CHART 1: Komposisi Uang (Existing) --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm lg:col-span-1">
            <h3 class="text-slate-800 font-bold mb-6">Komposisi Uang</h3>
            {{-- Tempat Render Chart --}}
            <div wire:ignore>
                <div id="moneyDistributionChart" class="flex justify-center min-h-[320px]"></div>
            </div>
        </div>

        {{-- CHART 2: Top 5 Saldo Siswa --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <h3 class="text-slate-800 font-bold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-users text-indigo-500"></i> Top 5 Saldo Siswa
        </h3>
        <div wire:ignore class="flex-1 flex items-center justify-center">
            <div id="topUsersChart" class="w-full h-full min-h-[300px]"></div>
        </div>
    </div>

    {{-- CHART 3: Top 5 Saldo Merchant --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <h3 class="text-slate-800 font-bold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-shop text-orange-500"></i> Top 5 Saldo Merchant
        </h3>
        <div wire:ignore class="flex-1 flex items-center justify-center">
            <div id="topMerchantsChart" class="w-full h-full min-h-[300px]"></div>
        </div>
    </div>

    {{-- CHART 4: Top 5 Pendapatan Toko --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col">
        <h3 class="text-slate-800 font-bold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-cash-register text-emerald-500"></i> Top 5 Omset Toko
        </h3>
        <div wire:ignore class="flex-1 flex items-center justify-center">
            <div id="topStoresChart" class="w-full h-full min-h-[300px]"></div>
        </div>
    </div>
</div>
@push('scripts')
{{-- Load Library --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener('livewire:navigated', () => {

        // --- FUNGSI HELPER UNTUK MEMBUAT PIE CHART (BIAR GAK CAPEK KETIK ULANG) ---
        function renderPieChart(elementId, labels, series, colors) {
            const el = document.getElementById(elementId);
            if (!el) return;
            el.innerHTML = ''; // Reset canvas

            // Cek jika data kosong
            if (series.length === 0 || series.every(item => item === 0)) {
                el.innerHTML = '<div class="flex items-center justify-center h-full text-slate-400 text-sm">Belum ada data</div>';
                return;
            }

            const options = {
                series: series,
                chart: {
                    type: 'donut', // Bisa ganti 'pie' kalau mau full
                    height: 320,
                    fontFamily: 'Inter, sans-serif',
                },
                labels: labels,
                colors: colors,
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: { show: true, fontSize: '11px' },
                                value: {
                                    show: true,
                                    fontSize: '14px',
                                    fontWeight: 700,
                                    formatter: (val) => "Rp " + (val / 1000).toFixed(0) + "k"
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '10px',
                                    formatter: function (w) {
                                        let val = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return (val / 1000000).toFixed(1) + " Juta";
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false }, // Matikan angka numpuk di chart
                stroke: { show: true, width: 2, colors: ['#ffffff'] },
                legend: { position: 'bottom', fontSize: '11px' },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                }
            };

            const chart = new ApexCharts(el, options);
            chart.render();
        }

        // --- 1. RENDER CHART KOMPOSISI UANG (MANUAL KARENA CUMA 2 DATA) ---
        // (Logika yang sebelumnya kita buat)
        const chartEl1 = document.getElementById('moneyDistributionChart');
        if (chartEl1) {
            chartEl1.innerHTML = '';
            var options1 = {
                series: [{{ $totalUserSaldo ?? 0 }}, {{ $totalMerchantBalance ?? 0 }}],
                chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
                labels: ['Siswa', 'Merchant'],
                colors: ['#3B82F6', '#F97316'], // Biru & Orange
                plotOptions: { pie: { donut: { size: '70%' } } },
                dataLabels: { enabled: false },
                legend: { position: 'bottom' },
                tooltip: { y: { formatter: (val) => "Rp " + new Intl.NumberFormat('id-ID').format(val) } }
            };
            new ApexCharts(chartEl1, options1).render();
        }


        // --- 2. RENDER CHART TOP SISWA (PAKAI HELPER) ---
        renderPieChart(
            'topUsersChart',
            @json($chartUserLabels),
            @json($chartUserValues),
            ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff'] // Gradasi Indigo
        );

        // --- 3. RENDER CHART TOP MERCHANT (PAKAI HELPER) ---
        renderPieChart(
            'topMerchantsChart',
            @json($chartMerchantLabels),
            @json($chartMerchantValues),
            ['#f97316', '#fb923c', '#fdba74', '#fed7aa', '#ffedd5'] // Gradasi Orange
        );

        // --- 4. RENDER CHART TOP TOKO (PAKAI HELPER) ---
        renderPieChart(
            'topStoresChart',
            @json($chartStoreLabels),
            @json($chartStoreValues),
            ['#10b981', '#34d399', '#6ee7b7', '#a7f3d0', '#d1fae5'] // Gradasi Emerald
        );

    });
</script>
@endpush