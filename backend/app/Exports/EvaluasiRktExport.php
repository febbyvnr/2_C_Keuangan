<?php

namespace App\Exports;

use App\Models\EvaluasiRkt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EvaluasiRktExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = EvaluasiRkt::with(['programKerja', 'refPm']);

        if (!empty($this->filters['ID_PROGRAM_KERJA'])) {
            $query->where('ID_PROGRAM_KERJA', $this->filters['ID_PROGRAM_KERJA']);
        }

        if (!empty($this->filters['ID_REF_PM'])) {
            $query->where('ID_REF_PM', $this->filters['ID_REF_PM']);
        }

        if (!empty($this->filters['TGL_PM'])) {
            $query->where('TGL_PM', 'like', $this->filters['TGL_PM'] . '%');
        }

        if (!empty($this->filters['keyword'])) {
            $query->where('DESKRIPSI_TR_PM', 'like', '%' . $this->filters['keyword'] . '%');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID Evaluasi',
            'Program Kerja (RKT)',
            'Jenis PM',
            'Tanggal Evaluasi',
            'Deskripsi',
        ];
    }

    public function map($row): array
    {
        return [
            $row->ID_PM,
            $row->programKerja?->PROGRAM_KERJA ?? $row->ID_PROGRAM_KERJA,
            $row->refPm?->NAMA_PM ?? $row->ID_REF_PM,
            $row->TGL_PM?->format('Y-m-d') ?? '-',
            $row->DESKRIPSI_TR_PM ?? '-',
        ];
    }
}
