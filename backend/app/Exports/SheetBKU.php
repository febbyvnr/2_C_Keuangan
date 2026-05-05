<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SheetBKU implements WithEvents, WithTitle
{
    protected $data;
    protected $role;
    protected $nip;
    protected $nama;
    protected $nip_ttd;

    public function __construct($data, $role = 'Bendahara', $nip = null, $nama = null, $nip_ttd = null) 
    {
        $this->data = $data;
        $this->role = $role;
        $this->nip = $nip;
        $this->nama = $nama;
        $this->nip_ttd = $nip_ttd;
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
                // WIDTH (TAMBAHAN SESUAI REFERENSI)
                // =====================
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(35);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(20);

                // =====================
                // TITLE
                // =====================
                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM (BKU)');
                $sheet->setCellValue('A4', 'Periode Laporan');

                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');

                $sheet->getStyle('A2:F4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);

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

                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()
                    ->getColor()->setARGB('FFFFFFFF');

                // FILTER (DITAMBAHKAN SESUAI REFERENSI)
                $sheet->setAutoFilter("A$headerRow:F$headerRow");

                // =====================
                // DATA
                // =====================
                $row = $headerRow + 1;
                $no = 1;
                $saldoAkhir = 0;

               foreach ($this->data as $item) {
                $sheet->setCellValue("A$row", $no++);
                $sheet->setCellValue("B$row", $item['tanggal']); 
                $sheet->setCellValue("C$row", $item['uraian']);
                $sheet->setCellValue("D$row", $item['debit']);
                $sheet->setCellValue("E$row", $item['kredit']);
                $sheet->setCellValue("F$row", $item['saldo']);

    $saldoAkhir = $item['saldo'];
    $row++;
}

                $endData = $row - 1;

                // FORMAT ANGKA (TANPA RP)
                $sheet->getStyle("D" . ($headerRow+1) . ":F$endData")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // WRAP TEXT
                $sheet->getStyle("C" . ($headerRow+1) . ":C$endData")
                    ->getAlignment()->setWrapText(true);

                // BORDER
                $sheet->getStyle("A$headerRow:F$endData")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // =====================
                // SALDO AKHIR
                // =====================
                $totalRow = $endData + 2;

                $sheet->setCellValue("E$totalRow", 'SALDO AKHIR');
                $sheet->setCellValue("F$totalRow", $saldoAkhir);

                $sheet->getStyle("E$totalRow:F$totalRow")->getFont()->setBold(true);

                $sheet->getStyle("F$totalRow")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->getStyle("E$totalRow:F$totalRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFE699');

                // =====================
                // FOOTER
                // =====================
                $footerRow = $totalRow + 4;

                $role = ucfirst($this->role ?? 'Bendahara');
                $nama = $this->nama ?? '-';
                $nip = $this->nip_ttd ?? '-';

                $sheet->mergeCells("B" . ($footerRow+1) . ":E" . ($footerRow+1));
                $sheet->mergeCells("B" . ($footerRow+3) . ":E" . ($footerRow+3));
                $sheet->mergeCells("B" . ($footerRow+7) . ":E" . ($footerRow+7));
                $sheet->mergeCells("B" . ($footerRow+8) . ":E" . ($footerRow+8));

                $sheet->setCellValue("B" . ($footerRow+1), $role . ',');
                $sheet->setCellValue("B" . ($footerRow+3), $nama);
                $sheet->setCellValue("B" . ($footerRow+7), '-------------------------');
                $sheet->setCellValue("B" . ($footerRow+8), 'NIP: ' . $nip);

                $sheet->getStyle("B" . ($footerRow+1) . ":E" . ($footerRow+8))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue("F" . ($footerRow+10), 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("F" . ($footerRow+10))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->freezePane("A7");
            },
        ];
    }
}