<div class="p-6 bg-white rounded-lg shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Verifikasi Merchant (Pending)</h2>
        <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded">
            Menunggu: {{ $pendingUsers->total() }}
        </span>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- TABEL USER --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Nama / NIK</th>
                    <th class="px-6 py-3">Dokumen</th>
                    <th class="px-6 py-3">Alamat KTP</th>
                    <th class="px-6 py-3">Tanggal Upload</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingUsers as $u)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $u->nama_lengkap }}</div>
                            <div class="text-xs text-gray-400">NIK: {{ $u->nik }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                {{-- Tombol Preview KTP --}}
                                <button wire:click="viewImage('{{ $u->foto_ktp }}')"
                                    class="px-2 py-1 text-xs border rounded hover:bg-gray-100">
                                    📷 KTP
                                </button>
                                {{-- Tombol Preview Selfie --}}
                                <button wire:click="viewImage('{{ $u->foto_selfie_ktp }}')"
                                    class="px-2 py-1 text-xs border rounded hover:bg-gray-100">
                                    🤳 Selfie
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 max-w-xs truncate">
                            {{ $u->alamat_ktp }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $u->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                {{-- Tombol Approve --}}
                                <button wire:click="approve({{ $u->id }})"
                                    onclick="confirm('Yakin terima user ini?') || event.stopImmediatePropagation()"
                                    class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-xs px-3 py-2">
                                    Terima
                                </button>

                                {{-- Tombol Reject (Buka Modal) --}}
                                <button wire:click="confirmReject({{ $u->id }})"
                                    class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-xs px-3 py-2">
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                            Tidak ada pengajuan verifikasi saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pendingUsers->links() }}
    </div>

    {{-- MODAL PREVIEW IMAGE --}}
    @if($showImageModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
            wire:click="$set('showImageModal', false)">
            <div class="relative bg-white p-2 rounded-lg max-w-3xl max-h-[90vh] overflow-auto">
                <img src="{{ $activeImage }}" class="max-w-full h-auto rounded">
                <button
                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold"
                    wire:click="$set('showImageModal', false)">
                    &times;
                </button>
            </div>
        </div>
    @endif

    {{-- MODAL REJECT WITH REASON [CATATAN 3] --}}
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Tolak Verifikasi</h3>

                <p class="text-sm text-gray-600 mb-2">
                    Berikan alasan penolakan untuk <b>{{ $selectedUser->nama_lengkap }}</b>:
                </p>

                <textarea wire:model="alasan_penolakan"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                    rows="3" placeholder="Contoh: Foto KTP buram, harap upload ulang."></textarea>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showRejectModal', false)"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button wire:click="submitReject"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Kirim Penolakan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>