<div class="min-h-screen bg-white font-sans pb-24 relative">

    {{-- 1. HEADER IMAGE & NAVIGATION --}}
    <div class="relative w-full h-[45vh]">
        {{-- Back Button --}}
        <a href="{{ route('dashboard') }}"
            class="absolute top-6 left-6 z-20 w-10 h-10 bg-black/30 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-black/50 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>

        {{-- Main Image (Original High Res) --}}
        <img src="{{ asset('storage/ads/' . $ad->banner_original) }}" class="w-full h-full object-cover"
            alt="{{ $ad->title }}">

        {{-- Gradient Fade at Bottom --}}
        <div class="absolute bottom-0 left-0 w-full h-24 bg-gradient-to-t from-white via-white/80 to-transparent"></div>
    </div>

    {{-- 2. CONTENT SECTION --}}
    <div class="px-6 relative mt-10 z-10">

        {{-- Official Badge --}}
        <div
            class="inline-block bg-emerald-50 text-emerald-700 text-[10px] font-bold px-3 py-1 rounded-full border border-emerald-100 mb-3 uppercase tracking-wider shadow-sm">
            Official Promo
        </div>

        {{-- Title --}}
        <h1 class="text-2xl font-extrabold text-slate-900 leading-tight mb-6">
            {{ $ad->title }}
        </h1>

        {{-- Store Info Card --}}
        <div
            class="bg-white rounded-2xl p-4 shadow-[0_4px_20px_-5px_rgba(0,0,0,0.05)] border border-slate-100 flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                {{-- Store Logo / Placeholder --}}
                <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-50">
                    @if($ad->store->gambar)
                        <img src="{{ asset('storage/stores/' . $ad->store->gambar) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-400">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z">
                                </path>
                            </svg>
                        </div>
                    @endif
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-medium">Disponsori oleh</p>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1">
                        {{ $ad->store->nama_toko }}
                        {{-- Verified Blue Tick --}}
                        <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </h3>
                </div>
            </div>

            {{-- Follow Button (Opsional) --}}
            {{-- <button
                class="text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition">
                Visit
            </button> --}}
        </div>

        {{-- Description --}}
        <div class="prose prose-sm prose-slate max-w-none text-slate-600 leading-relaxed mb-10">
            <p>{{ $ad->caption }}</p>
        </div>

    </div>

    {{-- 3. BOTTOM FIXED ACTION BAR --}}
    <div
        class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-100 p-4 pb-8 z-30 shadow-[0_-5px_20px_rgba(0,0,0,0.05)]">
        <div class="max-w-xl mx-auto flex items-center justify-between gap-4">

            {{-- Countdown Timer (Logic Alpine JS) --}}
            <div x-data="countdown('{{ $ad->end_time }}')" x-init="startTimer()" class="text-left">
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Berakhir Dalam</p>
                <div class="text-lg font-black text-red-500 font-mono tracking-tight" x-text="timeLeft">
                    --:--:--
                </div>
            </div>

            {{-- WhatsApp Button --}}
            @php
                // Logic Nomor HP
                $no_hp = $ad->store->owner->no_hp ?? ''; // Sesuaikan dengan nama kolom di DB kamu (misal: no_hp, phone, telephone)

                // Format ke 628xxx
                if (Str::startsWith($no_hp, '0')) {
                    $no_hp = '62' . substr($no_hp, 1);
                }

                // Pesan Template
                $pesan = urlencode("Halo *{$ad->store->nama_toko}*, saya tertarik dengan iklan *{$ad->title}* di aplikasi SI Pay.");
                $waLink = "https://wa.me/{$no_hp}?text={$pesan}";
            @endphp

            @if($no_hp)
                <a href="{{ $waLink }}" target="_blank"
                    class="flex-1 bg-emerald-600 text-white font-bold text-sm py-3.5 px-6 rounded-xl shadow-lg shadow-emerald-200 flex items-center justify-center gap-2 hover:bg-emerald-700 transition active:scale-95">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                    </svg>
                    Tanya Penjual
                </a>
            @else
                <button disabled
                    class="flex-1 bg-slate-300 text-white font-bold text-sm py-3.5 px-6 rounded-xl flex items-center justify-center gap-2 cursor-not-allowed">
                    Nomor Tidak Tersedia
                </button>
            @endif

        </div>
    </div>

    {{-- Script Countdown --}}
    <script>
        function countdown(endTime) {
            return {
                endTime: new Date(endTime).getTime(),
                timeLeft: '',
                startTimer() {
                    this.updateTimer();
                    setInterval(() => {
                        this.updateTimer();
                    }, 1000);
                },
                updateTimer() {
                    const now = new Date().getTime();
                    const distance = this.endTime - now;

                    if (distance < 0) {
                        this.timeLeft = "EXPIRED";
                        return;
                    }

                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Format 00:00:00
                    this.timeLeft =
                        (hours < 10 ? "0" + hours : hours) + ":" +
                        (minutes < 10 ? "0" + minutes : minutes) + ":" +
                        (seconds < 10 ? "0" + seconds : seconds);
                }
            }
        }
    </script>
</div>