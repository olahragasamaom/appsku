@extends('layouts.guest')

@section('title', 'Panritta - Platform Simulasi & Tryout CAT CPNS Terbaik')
@section('description', 'Belajar, tryout, dan persiapkan diri lulus CPNS/Sekolah Kedinasan dengan platform simulasi CAT (Computer Assisted Test) yang dirancang 100% mirip dengan aslinya.')
@section('keywords', 'tryout cpns, simulasi cat bkn, bimbel cpns, sekolah kedinasan, soal skd, soal skb, passing grade cpns, panritta')

@section('og_title', 'Panritta - Simulasi CAT CPNS & Kedinasan')
@section('og_description', 'Raih NIP impianmu dengan simulasi ujian CAT persis aslinya. Pembahasan lengkap, perankingan real-time, dan analitik kelulusan.')

@section('content')
    {{-- Navbar (Custom for Landing Page) --}}
    <nav class="fixed top-0 inset-x-0 bg-white/80 backdrop-blur-md border-b border-slate-200 z-50 transition-all duration-200" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center text-white font-bold text-lg shadow-sm">P</div>
                    <span class="font-extrabold text-xl tracking-tight text-slate-900">Panritta<span class="text-primary-600">.</span></span>
                </div>
                
                <div class="hidden md:flex items-center gap-8">
                    <a href="#fitur" class="text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors">Fitur</a>
                    <a href="#paket" class="text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors">Paket Belajar</a>
                    <a href="{{ route('peserta.ujian.offline.portal') }}" class="text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors">Portal Offline</a>
                </div>

                <div class="flex items-center gap-3">
                    @if(auth()->check() && auth()->user()->isPeserta())
                        <a href="{{ route('peserta.dashboard') }}" class="btn btn-ghost text-slate-600 hidden sm:inline-flex">Dashboard Saya</a>
                        <a href="{{ route('peserta.ujian.offline.portal') }}" class="btn btn-primary">Ujian Hari Ini</a>
                    @elseif(auth()->check() && auth()->user()->isSuperadmin())
                        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-primary">Panel Admin</a>
                    @else
                        <a href="{{ route('peserta.login') }}" class="btn btn-ghost text-slate-600 hidden sm:inline-flex">Masuk</a>
                        <a href="{{ route('peserta.register') }}" class="btn btn-primary shadow-sm shadow-primary-500/30">Daftar Sekarang</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-[0.03]"></div>
        <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-l from-primary-50 to-transparent -z-10 hidden lg:block"></div>
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 w-96 h-96 bg-primary-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
        <div class="absolute top-0 right-48 translate-y-12 w-72 h-72 bg-secondary-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-center">
                <div class="max-w-2xl text-center lg:text-left mx-auto lg:mx-0">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-sm font-medium mb-6 border border-primary-100">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                        </span>
                        Persiapan CPNS & Kedinasan 2026
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.1] mb-6">
                        Lulus CPNS Lebih Mudah dengan <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-secondary-600">Simulasi CAT Asli</span>
                    </h1>
                    <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                        Rasakan pengalaman ujian yang sebenarnya sebelum hari H. Platform Panritta dirancang 100% mirip dengan sistem CAT BKN. Lengkap dengan perankingan nasional, pembahasan mendalam, dan statistik kelulusan.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="{{ route('peserta.register') }}" class="btn btn-primary px-8 py-4 text-base font-semibold w-full sm:w-auto shadow-xl shadow-primary-500/20 hover:-translate-y-0.5 transition-all">
                            Mulai Belajar Gratis
                        </a>
                        <a href="#fitur" class="btn btn-secondary px-8 py-4 text-base font-medium w-full sm:w-auto hover:bg-slate-50 transition-colors">
                            Pelajari Fitur
                        </a>
                    </div>
                    <div class="mt-10 flex items-center justify-center lg:justify-start gap-4 text-sm text-slate-500 font-medium">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-slate-200 border-2 border-white flex items-center justify-center text-xs">👩</div>
                            <div class="w-8 h-8 rounded-full bg-slate-300 border-2 border-white flex items-center justify-center text-xs">👨</div>
                            <div class="w-8 h-8 rounded-full bg-slate-400 border-2 border-white flex items-center justify-center text-xs">👧</div>
                            <div class="w-8 h-8 rounded-full bg-slate-100 border-2 border-white flex items-center justify-center text-xs">+1k</div>
                        </div>
                        <p>Bergabung dengan 1,000+ calon ASN lainnya.</p>
                    </div>
                </div>
                <div class="relative lg:ml-auto mx-auto w-full max-w-lg lg:max-w-none">
                    <div class="relative rounded-2xl bg-white p-2 shadow-2xl shadow-slate-200/50 border border-slate-100 transform lg:rotate-2 hover:rotate-0 transition-transform duration-500">
                        <div class="absolute inset-0 border border-slate-200/50 rounded-2xl pointer-events-none"></div>
                        <div class="rounded-xl overflow-hidden bg-slate-50 aspect-[4/3] flex items-center justify-center">
                            {{-- Mockup CAT Screen --}}
                            <div class="w-full h-full p-4 flex flex-col">
                                <div class="flex justify-between items-center pb-3 border-b border-slate-200 mb-4">
                                    <div class="font-bold text-slate-800">TWK - Pancasila</div>
                                    <div class="bg-slate-800 text-white px-3 py-1 rounded text-sm font-mono">59:59</div>
                                </div>
                                <div class="flex gap-4 flex-1">
                                    <div class="flex-1 space-y-3">
                                        <div class="h-4 bg-slate-200 rounded w-full"></div>
                                        <div class="h-4 bg-slate-200 rounded w-5/6"></div>
                                        <div class="h-4 bg-slate-200 rounded w-4/6"></div>
                                        
                                        <div class="mt-6 space-y-2">
                                            <div class="h-10 border border-primary-500 bg-primary-50 rounded-lg flex items-center px-3">
                                                <div class="w-4 h-4 rounded-full border-4 border-primary-500 mr-2"></div>
                                                <div class="h-3 bg-primary-200 rounded w-1/2"></div>
                                            </div>
                                            <div class="h-10 border border-slate-200 rounded-lg flex items-center px-3">
                                                <div class="w-4 h-4 rounded-full border border-slate-300 mr-2"></div>
                                                <div class="h-3 bg-slate-200 rounded w-2/3"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-1/3 flex flex-col gap-2">
                                        <div class="text-xs font-bold text-slate-400 mb-1">NAVIGASI SOal</div>
                                        <div class="grid grid-cols-4 gap-1.5">
                                            <div class="aspect-square bg-primary-500 rounded flex items-center justify-center text-white text-xs font-bold">1</div>
                                            <div class="aspect-square bg-white border border-slate-200 rounded flex items-center justify-center text-slate-400 text-xs font-bold">2</div>
                                            <div class="aspect-square bg-white border border-slate-200 rounded flex items-center justify-center text-slate-400 text-xs font-bold">3</div>
                                            <div class="aspect-square bg-white border border-slate-200 rounded flex items-center justify-center text-slate-400 text-xs font-bold">4</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Fitur Section --}}
    <section id="fitur" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">Mengapa Memilih Panritta?</h2>
                <p class="text-lg text-slate-600">Sistem kami dibangun secara spesifik untuk mereplikasi tekanan, format, dan sistem penilaian ujian CPNS & Kedinasan sesungguhnya.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card border-0 shadow-sm bg-slate-50 hover:bg-white hover:shadow-xl transition-all duration-300 group">
                    <div class="card-body p-8">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Sistem CAT BKN Asli</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Layout, sistem navigasi, waktu hitung mundur, hingga cara memindahkan jawaban didesain 100% mirip aplikasi CAT BKN. Hilangkan rasa gugup di hari H.
                        </p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm bg-slate-50 hover:bg-white hover:shadow-xl transition-all duration-300 group">
                    <div class="card-body p-8">
                        <div class="w-12 h-12 rounded-xl bg-secondary-100 text-secondary-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Passing Grade & Analitik</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Otomatis menghitung passing grade per kategori (TWK, TIU, TKP). Ketahui langsung apakah Anda lulus passing grade atau tidak begitu ujian selesai.
                        </p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm bg-slate-50 hover:bg-white hover:shadow-xl transition-all duration-300 group">
                    <div class="card-body p-8">
                        <div class="w-12 h-12 rounded-xl bg-success-100 text-success-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Pembahasan Tuntas</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Akses riwayat ujian kapan saja dan pelajari letak kesalahan Anda. Tersedia pembahasan lengkap berupa teks, trik cepat, dan gambar ilustrasi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Paket Section --}}
    <section id="paket" class="py-20 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">Pilih Paket Tryout Sesuai Targetmu</h2>
                <p class="text-lg text-slate-600">Investasi terbaik untuk masa depan karirmu. Sekali bayar untuk akses ratusan bank soal berkualitas.</p>
            </div>

            @if(isset($pakets) && $pakets->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    @foreach($pakets as $paket)
                        <div class="card flex flex-col shadow-sm hover:shadow-xl transition-shadow relative overflow-hidden border-2 {{ $loop->iteration === 2 ? 'border-primary-500 scale-105 z-10' : 'border-transparent' }}">
                            @if($loop->iteration === 2)
                                <div class="absolute top-0 inset-x-0 bg-primary-500 text-white text-center text-xs font-bold uppercase tracking-wider py-1.5">
                                    Paling Populer
                                </div>
                            @endif
                            
                            <div class="card-body p-8 pt-10 flex flex-col flex-1">
                                <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $paket->nama_paket }}</h3>
                                <p class="text-sm text-slate-500 mb-6 min-h-[40px]">{{ $paket->deskripsi ?? 'Akses persiapan ujian lengkap.' }}</p>
                                
                                <div class="mb-6 flex items-baseline gap-1">
                                    <span class="text-4xl font-extrabold text-slate-900">
                                        {{ $paket->isGratis() ? 'Gratis' : 'Rp' . number_format($paket->harga, 0, ',', '.') }}
                                    </span>
                                    @if(! $paket->isGratis())
                                        <span class="text-slate-500 font-medium">/{{ $paket->durasi_hari }} Hari</span>
                                    @endif
                                </div>

                                <ul class="space-y-4 mb-8 flex-1">
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-slate-600 text-sm">
                                            Akses {{ $paket->kuota_ujian ? $paket->kuota_ujian . ' kali Ujian' : 'Ujian Sepuasnya (Unlimited)' }}
                                        </span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-slate-600 text-sm">Update Bank Soal Terbaru</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-slate-600 text-sm">Simulasi Waktu Nyata</span>
                                    </li>
                                    
                                    @if($paket->video_pembahasan)
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-slate-600 text-sm font-medium">Video Pembahasan Soal</span>
                                    </li>
                                    @endif
                                    
                                    @if($paket->analitik)
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-slate-600 text-sm font-medium">Grafik Analitik Kelulusan</span>
                                    </li>
                                    @endif
                                </ul>

                                <a href="{{ route('peserta.register') }}" class="btn {{ $loop->iteration === 2 ? 'btn-primary' : 'btn-secondary' }} w-full justify-center py-3">
                                    {{ $paket->isGratis() ? 'Daftar Gratis' : 'Pilih Paket' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center p-12 bg-white rounded-2xl border border-slate-200">
                    <p class="text-slate-500">Belum ada paket yang tersedia saat ini. Silakan kembali lagi nanti.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Call To Action --}}
    <section class="py-20 bg-primary-900 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-600 rounded-full mix-blend-screen filter blur-3xl opacity-50"></div>
        
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl font-bold text-white mb-6">Siap Menjadi ASN Tahun Ini?</h2>
            <p class="text-lg text-primary-100 mb-8 max-w-2xl mx-auto">
                Jangan biarkan persaingan menyingkirkan mimpimu. Mulai asah kemampuanmu dengan ribuan soal prediksi akurat di Panritta.
            </p>
            <a href="{{ route('peserta.register') }}" class="btn bg-white text-primary-800 hover:bg-slate-50 border-none px-8 py-4 text-lg font-bold shadow-xl shadow-black/10 hover:-translate-y-0.5 transition-all">
                Buat Akun Sekarang
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-slate-900 pt-16 pb-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold text-lg">P</div>
                        <span class="font-extrabold text-xl tracking-tight text-white">Panritta<span class="text-primary-500">.</span></span>
                    </div>
                    <p class="text-slate-400 leading-relaxed max-w-sm">
                        Lembaga Bimbingan Belajar CPNS & Kedinasan terpercaya. Kami memadukan materi berkualitas tinggi dengan platform teknologi mutakhir untuk memaksimalkan peluang kelulusan Anda.
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-4">Akses Cepat</h3>
                    <ul class="space-y-3">
                        <li><a href="#fitur" class="text-slate-400 hover:text-white transition-colors">Fitur CAT</a></li>
                        <li><a href="#paket" class="text-slate-400 hover:text-white transition-colors">Daftar Paket</a></li>
                        <li><a href="{{ route('peserta.ujian.offline.portal') }}" class="text-slate-400 hover:text-white transition-colors">Portal Offline Class</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-4">Bantuan</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('peserta.login') }}" class="text-slate-400 hover:text-white transition-colors">Login Peserta</a></li>
                        <li><a href="{{ route('superadmin.login') }}" class="text-slate-400 hover:text-white transition-colors">Login Admin</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 border-t border-slate-800 text-center md:text-left flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-slate-500 text-sm">
                    &copy; {{ date('Y') }} Panritta CPNS. Hak Cipta Dilindungi.
                </p>
            </div>
        </div>
    </footer>

    {{-- Script untuk Navbar Shrink saat di-scroll --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.getElementById('navbar');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 20) {
                    navbar.classList.add('shadow-sm');
                } else {
                    navbar.classList.remove('shadow-sm');
                }
            });
        });
    </script>
@endsection