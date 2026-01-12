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

        {{-- KOLOM KIRI: Pie Chart --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm lg:col-span-1">
            <h3 class="text-slate-800 font-bold mb-6">Komposisi Uang</h3>
            {{-- Tempat Render Chart --}}
            <div id="moneyDistributionChart" class="flex justify-center"></div>
        </div>

        {{-- KOLOM KANAN: Top List (Tabular) --}}
        <div
            class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- Top 5 Sultan (User) --}}
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b pb-2">Top 5 Saldo
                    Siswa</h4>
                <div class="space-y-4">
                    @foreach($topUsers as $index => $user)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 truncate w-24 md:w-32">
                                        {{ $user->nama_lengkap }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $user->username }}</p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-bold text-slate-800 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                {{ number_format($user->saldo / 1000, 0) }}k
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top 5 Merchant --}}
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b pb-2">Top 5 Omset
                    Merchant</h4>
                <div class="space-y-4">
                    @foreach($topMerchants as $index => $merchant)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500 group-hover:bg-orange-100 group-hover:text-orange-600 transition">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 truncate w-24 md:w-32">
                                        {{ $merchant->nama_lengkap }}</p>
                                    <p class="text-[10px] text-slate-400">Merchant</p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded-md border border-orange-100">
                                {{ number_format($merchant->merchant_balance / 1000, 0) }}k
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

{{-- SCRIPT CHART (Letakkan di stack scripts layout utama atau disini) --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            var options = {
                series: [{{ $totalUserSaldo }}, {{ $totalMerchantBalance }}],
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'inherit'
                },
                labels: ['Saldo User', 'Saldo Merchant'],
                colors: ['#3B82F6', '#F97316'], // Blue-500 & Orange-500
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Beredar',
                                    formatter: function (w) {
                                        // Formatter Rupiah Singkat (Juta/Ribu)
                                        let val = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return "Rp " + (val / 1000000).toFixed(1) + "Jt";
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false // Matikan angka di dalam chart biar bersih
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#moneyDistributionChart"), options);
            chart.render();
        });
    </script>
    
@endpush