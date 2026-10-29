<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Ujian - Panritta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    {!! app('App\Services\FormulaRenderer')->getKaTeXScripts() !!}
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">

<header class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center text-white font-bold text-lg shadow-sm">P</div>
            <span class="font-bold text-slate-800">Panritta<span class="text-primary-600">.</span></span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-600 hidden sm:inline">{{ $peserta->nama_peserta }}</span>
            <form method="POST" action="{{ route('peserta.ujian.offline.logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm text-slate-600">Logout</button>
            </form>
        </div>
    </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-6">
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-success-50 border border-success-200 text-success-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-danger-50 border border-danger-200 text-danger-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Selamat datang, {{ $peserta->nama_peserta }}</h1>
        <p class="text-slate-500 mt-1">Nomor Peserta: <strong>{{ $peserta->nomor_peserta }}</strong></p>
    </div>

    <section>
        <h2 class="text-lg font-semibold text-slate-700 mb-3">Daftar Ujian</h2>
        
        @if($ujians->isEmpty())
            <div class="card">
                <div class="card-body text-center text-slate-500 py-8">
                    <p>Tidak ada ujian offline yang tersedia saat ini.</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($ujians as $ujian)
                    @php
                        $isUjianAktif = $ujian->status === 'aktif';
                        $isPesertaAktif = (bool) $peserta->is_active;
                        $ujianPeserta = $peserta->ujianPeserta;
                        $sudahSelesai = $ujianPeserta && $ujianPeserta->status === 'selesai';
                        $isPaused = $ujianPeserta && $ujianPeserta->is_paused;
                        $canStart = $isUjianAktif && $isPesertaAktif && !$sudahSelesai && !$isPaused;
                    @endphp
                    
                    <div class="card border-l-4 {{ $canStart ? 'border-l-success-500' : 'border-l-slate-300' }}">
                        <div class="card-body">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-slate-800 mb-2">{{ $ujian->nama_ujian }}</h3>
                                    
                                    <div class="flex flex-wrap gap-2 mb-3">
                                         <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-100 text-secondary-700">
                                             Offline
                                         </span>
                                         
                                         @if($isUjianAktif)
                                             <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-700">
                                                 Aktif
                                             </span>
                                         @else
                                             <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-700">
                                                 {{ ucfirst($ujian->status) }}
                                             </span>
                                         @endif

                                           @if($sudahSelesai)
                                               <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                                   ✓ Sudah Mengerjakan
                                               </span>
                                           @elseif($isPaused)
                                               <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-danger-100 text-danger-700">
                                                   ⏸ Dihentikan Sementara
                                               </span>
                                           @elseif($isPesertaAktif)
                                               <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-700">
                                                   ✓ Diaktifkan
                                               </span>
                                           @else
                                               <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-700">
                                                   ⏳ Menunggu Aktivasi
                                               </span>
                                           @endif
                                     </div>

                                    <div class="text-sm text-slate-600 space-y-1">
                                        @if($ujian->tanggal_ujian)
                                            <p>📅 {{ $ujian->tanggal_ujian->format('d M Y, H:i') }} WIB</p>
                                        @endif
                                        @if($ujian->durasi_ujian)
                                            <p>⏱ Durasi: {{ $ujian->durasi_ujian }} menit</p>
                                        @endif
                                        <p>📝 {{ $ujian->jumlah_soal }} soal</p>
                                    </div>

                                    @if(!$canStart)
                                         <div class="mt-3 text-sm space-y-1">
                                             @if(!$isUjianAktif)
                                                 <p class="text-warning-600">⚠️ Ujian belum diaktifkan oleh admin</p>
                                             @endif
                                             @if(!$isPesertaAktif)
                                                 <p class="text-warning-600">⏳ Akun Anda belum diaktifkan. Silakan hubungi pengawas untuk aktivasi.</p>
                                             @endif
                                         </div>
                                     @endif
                                </div>

                                 <div class="flex-shrink-0">
                                      @if($sudahSelesai)
                                          <a href="{{ route('peserta.ujian.hasil', $ujian->id) }}" class="btn btn-secondary">
                                              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                              </svg>
                                              Lihat Hasil
                                          </a>
                                      @elseif($isPaused)
                                          <button type="button" class="btn btn-secondary" disabled>
                                              Anda Dihentikan Sementara Oleh Admin
                                          </button>
                                      @elseif($canStart)
                                          <button type="button" 
                                                  onclick="showExamWarning({{ $ujian->id }}, '{{ $ujian->nama_ujian }}')"
                                                  class="btn btn-primary">
                                              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                              </svg>
                                              Ikuti Ujian
                                          </button>
                                          <form id="examForm-{{ $ujian->id }}" method="POST" action="{{ route('peserta.offline.start', $ujian->id) }}" style="display: none;">
                                              @csrf
                                          </form>
                                      @else
                                          <button type="button" class="btn btn-secondary" disabled>
                                              Belum Tersedia
                                          </button>
                                      @endif
                                   </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</main>

<!-- Modal Konfirmasi Ujian -->
<div id="examWarningModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full flex flex-col h-[90vh] max-h-[90vh]">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-800 px-6 py-8 text-white flex-shrink-0">
            <h2 class="text-2xl font-bold mb-2">Peraturan Ujian</h2>
            <p class="text-primary-100" id="modalUjianNama"></p>
        </div>

        <!-- Content (Scrollable) -->
        <div class="px-4 py-6 space-y-4 overflow-y-auto flex-1">
            <div class="space-y-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="flex items-center justify-center h-7 w-7 rounded-full bg-danger-100">
                            <svg class="h-4 w-4 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M9 3h6a9 9 0 019 9v6a9 9 0 01-9 9H9a9 9 0 01-9-9V9a9 9 0 019-9z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-sm">Ketenangan Selama Ujian</h3>
                        <p class="text-slate-600 mt-0.5 text-xs">Menjaga ketenangan di ruangan ujian sangat penting. Tidak diperbolehkan berbicara dengan peserta lain atau mengganggu konsentrasi peserta lain.</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="flex items-center justify-center h-7 w-7 rounded-full bg-success-100">
                            <svg class="h-4 w-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-sm">Berdoa Sebelum Memulai</h3>
                        <p class="text-slate-600 mt-0.5 text-xs">Sebelum memulai ujian, sempatkan diri untuk berdoa agar dapat mengerjakan soal dengan baik dan lancar.</p>
                    </div>
                </div>
            </div>

            <div class="bg-primary-50 border-l-4 border-primary-500 p-3 rounded">
                <p class="text-primary-900 text-xs">
                    <strong>Catatan:</strong> Dengan menekan tombol "Saya Setuju, Mulai Ujian", Anda telah memahami dan menyetujui semua peraturan ujian di atas.
                </p>
            </div>

            <label class="flex items-start gap-2 p-3 bg-slate-50 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-100 transition-colors">
                <input type="checkbox" 
                       id="agreementCheckbox"
                       onchange="updateSubmitButton()"
                       class="w-4 h-4 mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-600 flex-shrink-0">
                <span class="text-xs text-slate-700">
                    <strong>Saya telah membaca dan memahami</strong> semua peraturan dan informasi ujian di atas, serta bersedia mematuhi semua ketentuan yang berlaku.
                </span>
            </label>
        </div>

        <!-- Footer (Fixed) -->
        <div class="bg-slate-50 px-6 py-4 flex gap-3 justify-end border-t border-slate-200 flex-shrink-0">
            <button type="button" 
                    onclick="closeExamWarning()"
                    class="btn btn-ghost">
                Batal
            </button>
            <button type="button" 
                    id="submitExamButton"
                    onclick="submitExamForm()"
                    disabled
                    class="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                Saya Setuju, Mulai Ujian
            </button>
        </div>
    </div>
</div>

<script>
    let currentUjianId = null;

    function showExamWarning(ujianId, ujianName) {
        currentUjianId = ujianId;
        document.getElementById('modalUjianNama').textContent = ujianName;
        document.getElementById('agreementCheckbox').checked = false;
        updateSubmitButton();
        document.getElementById('examWarningModal').classList.remove('hidden');
    }

    function updateSubmitButton() {
        const checkbox = document.getElementById('agreementCheckbox');
        const submitBtn = document.getElementById('submitExamButton');
        submitBtn.disabled = !checkbox.checked;
    }

    function closeExamWarning() {
        document.getElementById('examWarningModal').classList.add('hidden');
        currentUjianId = null;
    }

    function submitExamForm() {
        if (currentUjianId) {
            document.getElementById('examForm-' + currentUjianId).submit();
        }
    }

    // Close modal when clicking outside
    document.getElementById('examWarningModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeExamWarning();
        }
    });
</script>

</body>
</html>
