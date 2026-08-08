<?php

namespace App\Exports;

use App\Models\Ujian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class UjianRankingExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    protected int $rowNumber = 0;

    public function __construct(
        protected Ujian $ujian
    ) {}

    public function collection(): Collection
    {
        return $this->ujian->peserta()
            ->with('user')
            ->orderByDesc('total_nilai')
            ->get()
            ->values();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Rank', 'Nama', 'Username', 'Total Nilai', 'Status', 'Kelulusan'];
    }

    /**
     * @param  \App\Models\UjianPeserta  $item
     * @return array<int, string>
     */
    public function map($item): array
    {
        $this->rowNumber++;

        $kelulusan = match ($item->lulus) {
            true => 'Lulus',
            false => 'Tidak Lulus',
            default => '-',
        };

        return [
            (string) $this->rowNumber,
            $item->user?->name ?? '-',
            $item->user?->username ?? '-',
            $item->total_nilai !== null ? (string) $item->total_nilai : '-',
            $item->status,
            $kelulusan,
        ];
    }

    public function title(): string
    {
        return 'Perankingan Peserta';
    }
}
