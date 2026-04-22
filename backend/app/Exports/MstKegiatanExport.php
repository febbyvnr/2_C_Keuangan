<?php

namespace App\Exports;

use App\Models\MstKegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MstKegiatanExport implements FromCollection, WithCustomStartCell, WithStyles, WithEvents, ShouldAutoSize
{
    protected array $filters;
    protected int $dataCount = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function collection(): Collection
    {
        $search = trim((string) ($this->filters['search'] ?? ''));

        $query = MstKegiatan::query()
            ->with(['parent'])
            ->where('IS_DELETE', 0)
            ->orderBy('ID_KEGIATAN', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('DESKRIPSI_KEGIATAN', 'like', "%{$search}%")
                ->orWhere('ID_KEGIATAN', 'like', "%{$search}%");
            });
        }

        $data = $query->get()->values()->map(function ($item, $index) {
            return [
                'NO' => $index + 1,
                'KODE_KEGIATAN' => $item->ID_KEGIATAN,
                'DESKRIPSI_KEGIATAN' => $item->DESKRIPSI_KEGIATAN,
                'PARENT_KEGIATAN' => optional($item->parent)->DESKRIPSI_KEGIATAN ?? '-',
            ];
        });

        $this->dataCount = $data->count();

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            3 => [
                'font' => [
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            6 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2F75B5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');
                $sheet->mergeCells('A3:D3');

                $sheet->setCellValue('A1', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A2', 'DAFTAR DATA KEGIATAN');
                $sheet->setCellValue('A3', 'Data Master Kegiatan');

                // Header tabel
                $sheet->setCellValue('A6', 'NO');
                $sheet->setCellValue('B6', 'KODE KEGIATAN');
                $sheet->setCellValue('C6', 'DESKRIPSI KEGIATAN');
                $sheet->setCellValue('D6', 'PARENT KEGIATAN');

                $endRow = 6 + $this->dataCount;

                // Border tabel
                $sheet->getStyle("A6:D{$endRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Alignment
                $sheet->getStyle("A6:D{$endRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A6:B{$endRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("D6:D{$endRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(45);
                $sheet->getColumnDimension('D')->setWidth(20);

                // Tinggi baris
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(6)->setRowHeight(20);

                // Footer
                $footerRow1 = $endRow + 3;
                $footerRow2 = $footerRow1 + 1;

                $sheet->mergeCells("C{$footerRow1}:D{$footerRow1}");
                $sheet->mergeCells("C{$footerRow2}:D{$footerRow2}");

                $sheet->setCellValue("C{$footerRow1}", 'Yogyakarta, ' . now()->translatedFormat('d F Y'));
                $sheet->setCellValue("C{$footerRow2}", 'By: Admin / Bendahara');

                $sheet->getStyle("C{$footerRow1}:D{$footerRow2}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
            },
        ];
    }
}