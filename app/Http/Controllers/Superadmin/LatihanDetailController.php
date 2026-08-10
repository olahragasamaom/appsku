<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LatihanDetailRequest;
use App\Models\LatihanDetail;
use App\Models\LatihanKategori;
use App\Models\LatihanProduk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LatihanDetailController extends Controller
{
    /**
     * Tampilkan daftar transaksi (Header).
     */
    public function index(): View
    {
        // Eager load relasi 'kategori' agar tidak N+1 query problem
        $details = LatihanDetail::with('kategori')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('superadmin.latihan-detail.index', compact('details'));
    }

    /**
     * Form transaksi baru.
     */
    public function create(): View
    {
        $kategoris = LatihanKategori::orderBy('nama')->get();

        // Jangan load semua produk di sini. Pencarian produk akan
        // dilakukan via AJAX/API karena asumsikan datanya banyak.
        return view('superadmin.latihan-detail.create', compact('kategoris'));
    }

    /**
     * API untuk pencarian produk di Modal Picker.
     * Mengembalikan response JSON.
     */
    public function searchProduk(Request $request)
    {
        $query = $request->input('q');

        $produks = LatihanProduk::query()
            ->when($query, function ($q) use ($query) {
                $q->where('nama', 'like', "%{$query}%")
                    ->orWhere('kode_produk', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json($produks);
    }

    /**
     * Simpan header dan child items menggunakan Database Transaction
     * agar data tidak parsial tersimpan jika error di tengah jalan.
     */
    public function store(LatihanDetailRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            // 1. Simpan Header
            $headerData = $request->validated();

            // Hitung total manual dari items
            $total = 0;
            foreach ($request->input('items', []) as $item) {
                $total += ($item['qty'] * $item['harga']);
            }
            $headerData['total'] = $total;

            $detail = LatihanDetail::create($headerData);

            // 2. Simpan Child Items
            // createMany akan meng-instantiate model LatihanDetailItem dan
            // memanggil trigger booted() yang kita buat untuk isi 'subtotal'.
            $detail->items()->createMany($request->input('items', []));
        });

        return redirect()->route('superadmin.latihan-detail.index')
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    /**
     * Form edit transaksi.
     */
    public function edit(LatihanDetail $latihan_detail): View
    {
        $kategoris = LatihanKategori::orderBy('nama')->get();

        // Load items relasi berserta data produknya
        $latihan_detail->load('items.produk');

        return view('superadmin.latihan-detail.edit', [
            'detail' => $latihan_detail,
            'kategoris' => $kategoris,
        ]);
    }

    /**
     * Update header dan child items.
     */
    public function update(LatihanDetailRequest $request, LatihanDetail $latihan_detail): RedirectResponse
    {
        DB::transaction(function () use ($request, $latihan_detail) {
            $headerData = $request->validated();

            $total = 0;
            foreach ($request->input('items', []) as $item) {
                $total += ($item['qty'] * $item['harga']);
            }
            $headerData['total'] = $total;

            // 1. Update Header
            $latihan_detail->update($headerData);

            // 2. Hapus child lama
            $latihan_detail->items()->delete();

            // 3. Insert child baru
            $latihan_detail->items()->createMany($request->input('items', []));
        });

        return redirect()->route('superadmin.latihan-detail.index')
            ->with('success', 'Transaksi berhasil diupdate.');
    }

    /**
     * Hapus transaksi.
     * Karena di migration ada cascadeOnDelete, child rows (items)
     * akan ikut terhapus otomatis oleh database (atau Eloquent cascade).
     */
    public function destroy(LatihanDetail $latihan_detail): RedirectResponse
    {
        $latihan_detail->delete();

        return redirect()->route('superadmin.latihan-detail.index')
            ->with('success', 'Transaksi berhasil dihapus beserta seluruh itemnya.');
    }
}
