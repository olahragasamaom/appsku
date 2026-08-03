<?php

namespace App\Exports;

use App\Models\Ujian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class UjianPesertaAkunExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    protected int $rowNumber = 0;

    public function __construct(
        protected Ujian $ujian
    ) {}

    public function collection(): Collection
    {
        return $this->ujian->peserta()->with('user')->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['No', 'Nama', 'Username', 'Email', 'Password'];
    }

    /**
     * @param  \App\Models\UjianPeserta  $item
     * @return array<int, string>
     */
    public function map($item): array
    {
        $this->rowNumber++;

        return [
            (string) $this->rowNumber,
            $item->user?->name ?? '-',
            $item->user?->username ?? '-',
            $item->user?->email ?? '-',
            '(sesuai yang dibuat admin)',
        ];
    }

    public function title(): string
    {
        return 'Daftar Akun Peserta';
    }
}
