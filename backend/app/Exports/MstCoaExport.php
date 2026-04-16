<?php

namespace App\Exports;

use App\Models\MstCoa;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MstCoaExport implements WithEvents
{
    protected array $filters;
    protected int $rowCount = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $search = trim((string) ($this->filters['search'] ?? ''));

        $query = MstCoa::query()
            ->with('parent')
            ->active()
            ->orderBy('KODE_COA', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('KODE_COA', 'like', "%{$search}%")
                  ->orWhere('DESKRIPSI_COA', 'like', "%{$search}%");
            });
        }

        $data = $query->get();
        $this->rowCount = $data->count();

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet->getDelegate();
                $dataCollection = $this->collection();


                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'DAFTAR CHART OF ACCOUNTS (COA)');
                $sheet->setCellValue('A4', 'Data Master COA');

                $sheet->mergeCells('A2:D2');
                $sheet->mergeCells('A3:D3');
                $sheet->mergeCells('A4:D4');

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(17);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('A4')->getFont()->setSize(11);

                $sheet->getStyle('A2:D4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getRowDimension(2)->setRowHeight(26);
                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension(4)->setRowHeight(22);

                $headerRow = 6;

                $sheet->setCellValue("A{$headerRow}", 'NO');
                $sheet->setCellValue("B{$headerRow}", 'KODE COA');
                $sheet->setCellValue("C{$headerRow}", 'DESKRIPSI COA');
                $sheet->setCellValue("D{$headerRow}", 'PARENT COA');

                $sheet->getStyle("A{$headerRow}:D{$headerRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$headerRow}:D{$headerRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A{$headerRow}:D{$headerRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF2E75B6');

                $sheet->getStyle("A{$headerRow}:D{$headerRow}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(40);
                $sheet->getColumnDimension('D')->setWidth(18);


                $sheet->getStyle("B:B")->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle("D:D")->getNumberFormat()->setFormatCode('@');

                $startData = $headerRow + 1;
                $row = $startData;
                $no = 1;

                foreach ($dataCollection as $item) {
                    $level = substr_count($item->KODE_COA, '.') + 1;
                    $indent = str_repeat('→ ', $level - 1);
                    $deskripsi = $indent . $item->DESKRIPSI_COA;

                    $parentKode = optional($item->parent)->KODE_COA ?? '-';

                    $sheet->setCellValueExplicit("A{$row}", (string) $no, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("B{$row}", (string) $item->KODE_COA, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("C{$row}", (string) $deskripsi, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("D{$row}", (string) $parentKode, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                    if ($level == 1) {
                        $sheet->getStyle("C{$row}")->getFont()->setBold(true);
                    }

                    $row++;
                    $no++;
                }

                $endData = $row - 1;

                if ($endData >= $startData) {

                    $sheet->getStyle("A{$headerRow}:D{$endData}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);


                    $sheet->getStyle("A{$startData}:A{$endData}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("B{$startData}:B{$endData}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("C{$startData}:C{$endData}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    $sheet->getStyle("D{$startData}:D{$endData}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("A{$startData}:D{$endData}")
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


                    $sheet->getStyle("B{$startData}:D{$endData}")
                        ->getAlignment()->setWrapText(true);


                    for ($i = $startData; $i <= $endData; $i++) {
                        $sheet->getRowDimension($i)->setRowHeight(-1);
                    }
                }

                $footerRow = max($endData, $headerRow) + 4;

                $sheet->setCellValue("D" . ($footerRow + 1), 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("D" . ($footerRow + 1))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->setCellValue("D" . ($footerRow + 2), 'By: Admin / Bendahara');
                $sheet->getStyle("D" . ($footerRow + 2))
                    ->getFont()->setBold(true);

                $sheet->getStyle("D" . ($footerRow + 2))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->freezePane('A7');
            },
        ];
    }
}