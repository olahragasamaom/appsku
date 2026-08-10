<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * CONTROLLER: Dashboard 2 (Capaian Indikator Kinerja Utama / IKU)
 *
 * Dashboard ini menampilkan ringkasan performa pencapaian target strategis
 * universitas berdasarkan Key Performance Indicator (IKU).
 *
 * CATATAN: Untuk saat ini seluruh data masih STATIS (hardcoded) sebagai
 * contoh tampilan. Nantinya bisa diganti dengan query ke database.
 */
class Dashboard2Controller extends Controller
{
    public function index(): View
    {
        // 1. Ringkasan Capaian (3 gauge chart di atas)
        $ringkasan = [
            'perkin' => [
                'label' => 'Capaian Perkin',
                'deskripsi' => 'Persentase Jumlah Capaian Perjanjian Kinerja',
                'nilai' => 85.0,
                'keterangan' => 'Efektifitas Kontrak Kinerja',
                'warna' => '#22c55e', // hijau
            ],
            'kokin' => [
                'label' => 'Capaian Kokin',
                'deskripsi' => 'Persentase jumlah capaian komitmen kinerja.',
                'nilai' => 78.2,
                'keterangan' => 'Kepatuhan Target Unit',
                'warna' => '#3b82f6', // biru
            ],
            'renstra' => [
                'label' => 'Capaian Renstra',
                'deskripsi' => 'Persentase jumlah capaian rencana strategis',
                'nilai' => 84.3,
                'keterangan' => 'Keselarasan Visi Strategis',
                'warna' => '#f97316', // oranye
            ],
        ];

        // 2. Capaian Indikator Terendah (tabel)
        $indikatorTerendah = [
            ['uraian' => 'Persentase PTKI (Prodi) yang Terakreditasi', 'capaian' => 90, 'status' => 'emas', 'warna' => 'kuning'],
            ['uraian' => 'Rasio Pertumbuhan Jumlah Mahasiswa', 'capaian' => 90, 'status' => 'hijau', 'warna' => 'hijau'],
            ['uraian' => 'Indeks Akurasi Proyeksi Pendapatan dan Belanja BLU', 'capaian' => 90, 'status' => 'kuning', 'warna' => 'kuning'],
            ['uraian' => 'Persentase Peningkatan Mahasiswa Asing', 'capaian' => 90, 'status' => 'kuning', 'warna' => 'kuning'],
            ['uraian' => 'Persentase Mahasiswa Magister/Doktor yang Memiliki Nilai TOEFL > 450 dan TOAFL > 70', 'capaian' => 90, 'status' => 'hijau', 'warna' => 'hijau'],
        ];

        // 3. Capaian Jabatan Tertinggi (tabel)
        $jabatanTertinggi = [
            ['jabatan' => 'Dekan Fakultas Tarbiyah dan Keguruan Dekan F...', 'capaian' => 120, 'status' => 'emas', 'warna' => 'kuning'],
            ['jabatan' => 'Ketua LP2M', 'capaian' => 90, 'status' => 'hijau', 'warna' => 'hijau'],
            ['jabatan' => 'Dekan Fakultas Sains dan Teknologi', 'capaian' => 84, 'status' => 'kuning', 'warna' => 'kuning'],
            ['jabatan' => 'Kepala Biro AUPK', 'capaian' => 84, 'status' => 'kuning', 'warna' => 'kuning'],
            ['jabatan' => 'Dekan Fakultas Ushuluddin', 'capaian' => 89, 'status' => 'hijau', 'warna' => 'hijau'],
        ];

        // 4. Capaian Jabatan Terendah (tabel)
        $jabatanTerendah = [
            ['jabatan' => 'Kepala UPT Perpustakaan', 'capaian' => 43, 'status' => 'merah', 'warna' => 'merah'],
            ['jabatan' => 'Dekan Fakultas Dakwah dan Komunikasi', 'capaian' => 41, 'status' => 'merah', 'warna' => 'merah'],
            ['jabatan' => 'Ketua LPM', 'capaian' => 42, 'status' => 'merah', 'warna' => 'merah'],
            ['jabatan' => 'Kepala Pusat Bahasa', 'capaian' => 43, 'status' => 'merah', 'warna' => 'merah'],
            ['jabatan' => 'Dekan Fakultas Kedokteran', 'capaian' => 40, 'status' => 'merah', 'warna' => 'merah'],
        ];

        // 5. Daftar Capaian IKU Jabatan (tabel lengkap)
        $ikuJabatan = [
            ['uraian' => 'IKU Universitas', 'perkin' => 90, 'kokin' => 67, 'renstra' => 97, 'rata' => 90, 'status' => 'emas', 'warna' => 'kuning'],
            ['uraian' => 'Rektor (Kontrak Kinerja Kementerian Keuangan)', 'perkin' => 85, 'kokin' => 90, 'renstra' => 90, 'rata' => 90, 'status' => 'hijau', 'warna' => 'hijau'],
            ['uraian' => 'Rektor (Perjanjian Kinerja Kementerian Agama)', 'perkin' => 90, 'kokin' => 65, 'renstra' => 90, 'rata' => 90, 'status' => 'kuning', 'warna' => 'kuning'],
            ['uraian' => 'Wakil Rektor Bidang Akademik', 'perkin' => 93, 'kokin' => 90, 'renstra' => 93, 'rata' => 90, 'status' => 'emas', 'warna' => 'kuning'],
            ['uraian' => 'Wakil Rektor Bidang Administrasi Umum, Perencanaan & Keuangan', 'perkin' => 90, 'kokin' => 90, 'renstra' => 87, 'rata' => 90, 'status' => 'kuning', 'warna' => 'kuning'],
            ['uraian' => 'Wakil Rektor Bidang Kemahasiswaan', 'perkin' => 90, 'kokin' => 90, 'renstra' => 93, 'rata' => 90, 'status' => 'hijau', 'warna' => 'hijau'],
        ];

        // 6. Opsi tahun akademik untuk dropdown filter
        $tahunAkademik = [
            '2024/2025 (Ganjil)',
            '2023/2024 (Genap)',
            '2023/2024 (Ganjil)',
        ];

        return view('superadmin.dashboard2', compact(
            'ringkasan',
            'indikatorTerendah',
            'jabatanTertinggi',
            'jabatanTerendah',
            'ikuJabatan',
            'tahunAkademik'
        ));
    }
}
