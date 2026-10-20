@extends('peserta.layouts.app')

@section('title', 'Pembayaran Paket')

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <a href="{{ route('peserta.langganan.index') }}" class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700 font-medium mb-4">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Paket
            </a>
            <h1 class="text-3xl font-bold text-slate-900">Konfirmasi Pembayaran</h1>
            <p class="text-slate-600 mt-2">Lakukan pembayaran dan upload bukti transaksi untuk mengaktifkan paket</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Form Upload Bukti --}}
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Langkah-Langkah Pembayaran</h2>
                    </div>
                    <div class="card-body space-y-6">
                        {{-- Step 1: Informasi Rekening --}}
                        <div class="border-b pb-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                                    1
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-slate-900 mb-3">Catat Nomor Pembayaran</h3>
                                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200 font-mono text-sm text-slate-700 break-all">
                                        {{ $bayar->nomor_pembayaran }}
                                    </div>
                                    <p class="text-xs text-slate-500 mt-2">Gunakan nomor ini sebagai referensi pembayaran</p>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Metode Pembayaran --}}
                        <div class="border-b pb-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                                    2
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-slate-900 mb-4">Pilih Metode Pembayaran</h3>
                                    <div class="space-y-3">
                                        {{-- QRIS --}}
                                        <label class="flex items-center p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                                            <input type="radio" name="metode" value="qris" checked class="w-4 h-4 text-primary-600">
                                            <div class="ml-3 flex-1">
                                                <div class="font-semibold text-slate-900">QRIS</div>
                                                <div class="text-sm text-slate-500">Scan dengan aplikasi e-wallet favorit Anda</div>
                                            </div>
                                            <svg class="w-6 h-6 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zm-8 8h6v6H7v-6zm2 2v2h2v-2H9zm8-2h6v6h-6v-6zm2 2v2h2v-2h-2z"/>
                                            </svg>
                                        </label>

                                        {{-- Transfer Bank --}}
                                        <label class="flex items-center p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                                            <input type="radio" name="metode" value="transfer" class="w-4 h-4 text-primary-600">
                                            <div class="ml-3 flex-1">
                                                <div class="font-semibold text-slate-900">Transfer Bank</div>
                                                <div class="text-sm text-slate-500">Transfer ke rekening bank yang tersedia</div>
                                            </div>
                                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </label>

                                        {{-- E-Wallet --}}
                                        <label class="flex items-center p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                                            <input type="radio" name="metode" value="ewallet" class="w-4 h-4 text-primary-600">
                                            <div class="ml-3 flex-1">
                                                <div class="font-semibold text-slate-900">E-Wallet</div>
                                                <div class="text-sm text-slate-500">GCash, OVO, Dana, atau e-wallet lainnya</div>
                                            </div>
                                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Upload Bukti --}}
                        <div>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                                    3
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-slate-900 mb-4">Upload Bukti Pembayaran</h3>
                                    
                                    @if ($errors->any())
                                        <div class="bg-danger-50 border border-danger-200 rounded-lg p-4 mb-4">
                                            <p class="text-danger-700 text-sm font-medium mb-2">Terjadi kesalahan:</p>
                                            <ul class="text-danger-600 text-sm space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>• {{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form action="{{ route('peserta.langganan.upload-bukti', $bayar->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf

                                        {{-- Upload Area --}}
                                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:border-primary-400 hover:bg-primary-50/30 transition cursor-pointer" id="upload-area">
                                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                            </svg>
                                            <p class="text-slate-600 font-medium mb-1">Drag & drop gambar atau klik untuk upload</p>
                                            <p class="text-slate-500 text-sm">Format: JPG, PNG, GIF (Maks 5 MB)</p>
                                            <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" class="hidden" accept="image/*" required>
                                        </div>

                                        {{-- Preview --}}
                                        <div id="preview-container" class="hidden">
                                            <img id="preview-image" src="" alt="Preview" class="max-w-full h-48 object-contain rounded-lg border border-slate-200">
                                            <button type="button" id="clear-image" class="text-sm text-danger-600 hover:text-danger-700 mt-2">Hapus dan ganti gambar</button>
                                        </div>

                                        {{-- Catatan Peserta --}}
                                        <div>
                                            <label for="catatan_peserta" class="block text-sm font-medium text-slate-700 mb-2">
                                                Catatan (Opsional)
                                            </label>
                                            <textarea 
                                                name="catatan_peserta" 
                                                id="catatan_peserta" 
                                                rows="3"
                                                class="input w-full"
                                                placeholder="Contoh: Transfer dari BCA atas nama Budi Santoso"
                                                maxlength="500"
                                            ></textarea>
                                            <p class="text-xs text-slate-500 mt-1">Tambahkan informasi untuk membantu admin verifikasi</p>
                                        </div>

                                        {{-- Submit --}}
                                        <button type="submit" class="btn btn-primary w-full">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                            Upload Bukti Pembayaran
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Pembayaran --}}
            <div class="lg:col-span-1">
                <div class="card sticky top-24">
                    <div class="card-header">
                        <h3 class="card-title">Ringkasan Pembayaran</h3>
                    </div>
                    <div class="card-body space-y-4">
                        {{-- Paket Info --}}
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Paket</p>
                            <p class="font-semibold text-slate-900">{{ $bayar->paket->nama_paket }}</p>
                        </div>

                        <hr class="border-slate-200">

                        {{-- Harga --}}
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Nominal Pembayaran</p>
                            <p class="text-2xl font-bold text-primary-600">
                                Rp {{ number_format($bayar->jumlah, 0, ',', '.') }}
                            </p>
                        </div>

                        <hr class="border-slate-200">

                        {{-- Status --}}
                        <div>
                            <p class="text-sm text-slate-600 mb-2">Status</p>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-warning-100 text-warning-700">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 11-2 0 1 1 0 012 0zm-1 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                Menunggu Verifikasi
                            </div>
                        </div>

                        <hr class="border-slate-200">

                        {{-- Info Berlaku --}}
                        <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-600 space-y-2">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-success-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Berlaku <strong>{{ $bayar->paket->durasi_hari }} hari</strong> setelah verifikasi</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-success-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>{{ $bayar->paket->kuota_ujian ?? 'Unlimited' }}</strong> akses ujian</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('bukti_pembayaran');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        const clearImageBtn = document.getElementById('clear-image');

        // Upload area click
        uploadArea.addEventListener('click', () => fileInput.click());

        // Drag & drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('border-primary-400', 'bg-primary-50');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('border-primary-400', 'bg-primary-50');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('border-primary-400', 'bg-primary-50');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                showPreview();
            }
        });

        // File input change
        fileInput.addEventListener('change', showPreview);

        function showPreview() {
            const file = fileInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    uploadArea.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        clearImageBtn.addEventListener('click', () => {
            fileInput.value = '';
            previewContainer.classList.add('hidden');
            uploadArea.classList.remove('hidden');
        });
    </script>
    @endpush
@endsection
