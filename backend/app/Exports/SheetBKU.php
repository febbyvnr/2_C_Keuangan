<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SheetBKU implements WithEvents,withTitle
{
    protected $data;
    protected $role;
    protected $nip;

    public function __construct($data, $role = 'Bendahara', $nip = null) 
    {
        $this->data = $data;
        $this->role = $role;
        $this->nip = $nip; 
    }

    public function title(): string
    {
        return 'BKU';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet;

                // =====================
                // TITLE
                // =====================
                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM (BKU)');
                $sheet->setCellValue('A4', 'Periode Laporan');

                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(17);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('A4')->getFont()->setSize(11);

                $sheet->getStyle('A2:A4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // =====================
                // HEADER TABLE
                // =====================
                $headerRow = 6;

                $sheet->setCellValue("A$headerRow", 'NO');
                $sheet->setCellValue("B$headerRow", 'TANGGAL');
                $sheet->setCellValue("C$headerRow", 'URAIAN');
                $sheet->setCellValue("D$headerRow", 'DEBIT');
                $sheet->setCellValue("E$headerRow", 'KREDIT');
                $sheet->setCellValue("F$headerRow", 'SALDO');

                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->setBold(true);
                $sheet->getStyle("A$headerRow:F$headerRow")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A$headerRow:F$headerRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF2E75B6');

                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->getColor()->setARGB('FFFFFFFF');

                // WIDTH
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(35);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(18);

                // =====================
                // DATA
                // =====================
                $startData = $headerRow + 1;
                $row = $startData;
                $no = 1;

                $saldoAkhir = 0;

                foreach ($this->data as $item) {

                    $sheet->setCellValue("A$row", $no);
                    $sheet->setCellValue("B$row", $item->tanggal);
                    $sheet->setCellValue("C$row", $item->uraian);
                    $sheet->setCellValue("D$row", $item->debit);
                    $sheet->setCellValue("E$row", $item->kredit);
                    $sheet->setCellValue("F$row", $item->saldo);

                    $saldoAkhir = $item->saldo;

                    $row++;
                    $no++;
                }

                $endData = $row - 1;

                // =====================
                // FORMAT RP
                // =====================
                $sheet->getStyle("D$startData:F$endData")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');

                // =====================
                // WRAP TEXT
                // =====================
                $sheet->getStyle("C$startData:C$endData")
                    ->getAlignment()->setWrapText(true);

                // AUTO HEIGHT
                for ($i = $startData; $i <= $endData; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(-1);
                }

                // =====================
                // BORDER
                // =====================
                $sheet->getStyle("A$headerRow:F$endData")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->setAutoFilter("A$headerRow:F$headerRow");

                // =====================
                // SALDO AKHIR
                // =====================
                $totalRow = $endData + 2;

                $sheet->setCellValue("E$totalRow", 'SALDO AKHIR');
                $sheet->setCellValue("F$totalRow", $saldoAkhir);

                $sheet->getStyle("E$totalRow:F$totalRow")->getFont()->setBold(true);

                $sheet->getStyle("F$totalRow")->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');

                $sheet->getStyle("E$totalRow:F$totalRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFE699');
                
                $footerRow = $totalRow + 2;

                $sheet->setCellValue("F$footerRow", 'Yogyakarta, ' . date('d F Y'));
                $sheet->setCellValue("F" . ($footerRow + 1), 'By: ' . $this->role);
                $sheet->setCellValue("F" . ($footerRow + 2), 'NIP: ' . $this->nip);

                // ALIGN RIGHT
                $sheet->getStyle("F$footerRow:F" . ($footerRow + 1))
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // =====================
                // FREEZE
                // =====================
                $sheet->freezePane("A7");
            },
        ];
    }
}