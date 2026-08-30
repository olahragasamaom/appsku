<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\PesertaLangganan;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CONTROLLER: DashboardController
 * ================================
 * Menangani halaman beranda/dashboard utama untuk peserta (siswa) yang sudah login.
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard peserta.
     * Mengambil data:
     * 1. Ujian dari paket yang sedang aktif dilanggan oleh peserta.
     * 2. Riwayat ujian yang sudah pernah dikerjakan (selesai).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Ambil paket-paket langganan yang sedang AKTIF untuk user ini
        $langgananAktif = PesertaLangganan::query()
            ->with('paket.ujians') // Eager load relasi paket -> ujian-ujian di dalamnya
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('berakhir_pada')->orWhere('berakhir_pada', '>', now());
            })
            ->get();

        // Kumpulkan semua ujian online dari paket-paket yang aktif tersebut
        $ujianDariPaket = collect();
        foreach ($langgananAktif as $langganan) {
            foreach ($langganan->paket->ujians as $ujian) {
                if ($ujian->status === 'aktif' && $this->isUjianAccessible($ujian)) {
                    $ujianDariPaket->push($ujian);
                }
            }
        }
        $ujianDariPaket = $ujianDariPaket->unique('id');

        // 2. Ambil ujian offline yang dialokasikan khusus untuk user ini (jika ada)
        // (Misal admin secara manual memasukkan user ke ujian offline)
        $allocations = UjianPeserta::query()
            ->with('ujian')
            ->where('user_id', $user->id)
            ->whereIn('status', ['terdaftar', 'sedang_ujian', 'diblokir'])
            ->whereHas('ujian', fn ($query) => $query->where('status', 'aktif')->where('tipe_ujian', 'offline_kelas'))
            ->get();

        // 3. Ambil riwayat ujian yang sudah SELESAI
        $riwayat = UjianPeserta::query()
            ->with('ujian')
            ->where('user_id', $user->id)
            ->where('status', 'selesai')
            ->latest('waktu_selesai')
            ->get();

        return view('peserta.dashboard', compact('ujianDariPaket', 'allocations', 'riwayat'));
    }

    /**
     * Cek apakah ujian masih bisa diakses berdasarkan tanggal ujian dan batas keterlambatan.
     * - Jika tanggal_ujian null: ujian bisa diakses kapan saja (no time restriction).
     * - Jika tanggal_ujian ada dan batas_keterlambatan null: hanya bisa diakses setelah tanggal_ujian.
     * - Jika keduanya ada: bisa diakses dari tanggal_ujian hingga batas_keterlambatan.
     */
    private function isUjianAccessible(Ujian $ujian): bool
    {
        // Jika tidak ada tanggal ujian, ujian bisa diakses kapan saja
        if (! $ujian->tanggal_ujian) {
            return true;
        }

        $now = now();

        // Jika belum waktunya ujian (sebelum tanggal_ujian), tidak bisa diakses
        if ($now->lt($ujian->tanggal_ujian)) {
            return false;
        }

        // Jika sudah lewat tanggal ujian, cek batas keterlambatan
        if ($ujian->batas_keterlambatan) {
            // Bisa diakses sampai batas_keterlambatan
            return $now->lte($ujian->batas_keterlambatan);
        }

        // Jika tidak ada batas keterlambatan, ujian bisa diakses selamanya setelah tanggal_ujian
        return true;
    }
}
