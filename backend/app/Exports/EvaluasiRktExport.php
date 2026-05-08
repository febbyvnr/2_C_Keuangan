<?php

namespace App\Exports;

use App\Models\EvaluasiRkt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EvaluasiRktExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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

    public function title(): string
    {
        return 'Evaluasi RKT';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 35,
            'C' => 25,
            'D' => 18,
            'E' => 45,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Header row styling
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFDBFE']]],
        ]);

        // Data rows — zebra stripe
        for ($row = 2; $row <= $lastRow; $row++) {
            $color = ($row % 2 === 0) ? 'FFEFF6FF' : 'FFFFFFFF';
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $color]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFDBFE']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        $sheet->getRowDimension(1)->setRowHeight(20);

        return [];
    }
}
