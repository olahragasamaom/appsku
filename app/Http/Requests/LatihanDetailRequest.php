<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LatihanDetailRequest extends FormRequest
{
    /**
     * Authorization akan ditangani middleware route
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk transaksi yang kompleks.
     * Harus memvalidasi header (transaksi) dan child (items array).
     */
    public function rules(): array
    {
        return [
            // Validasi header
            'nama_transaksi' => ['required', 'string', 'max:255'],
            'latihan_kategori_id' => ['required', 'exists:latihan_kategori,id'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],

            // Validasi baris item (array dari child rows)
            // 'items' adalah array yang dikirim dari form
            'items' => ['required', 'array', 'min:1'],

            // Validasi elemen di dalam array items
            'items.*.latihan_produk_id' => ['required', 'exists:latihan_produk,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.harga' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_transaksi.required' => 'Nama transaksi wajib diisi.',
            'latihan_kategori_id.required' => 'Kategori wajib dipilih.',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'items.required' => 'Minimal harus ada 1 item produk.',
            'items.min' => 'Minimal harus ada 1 item produk.',
            'items.*.latihan_produk_id.required' => 'Produk pada baris item wajib dipilih.',
            'items.*.qty.required' => 'Jumlah (Qty) wajib diisi.',
            'items.*.qty.min' => 'Jumlah minimal 1.',
            'items.*.harga.required' => 'Harga wajib diisi.',
        ];
    }
}
