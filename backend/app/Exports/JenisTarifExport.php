<?php

namespace App\Exports;

use App\Models\RefJenisTarif;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Auth;

class JenisTarifExport implements WithEvents
{
    protected $role;
    protected $nip;
    protected $nama;

    public function __construct($role = null, $nip = null, $nama = null)
    {
        // Mengambil role dari Auth jika tidak dilempar dari controller
        $this->role = $role ?? (Auth::user()->role ?? 'Bendahara');
        $this->nip = $nip ?? (Auth::user()->nip ?? null);
        $this->nama = $nama ?? (Auth::user()->name ?? '-');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;

                // =====================
                // TITLE (Merged A & B)
                // =====================
                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'DATA JENIS TARIF');

                $sheet->mergeCells('A2:B2');
                $sheet->mergeCells('A3:B3');
                $sheet->mergeCells('A4:B4');

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(17);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('A4')->getFont()->setSize(11);

                $sheet->getStyle('A2:A4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // SPACING JUDUL
                $sheet->getRowDimension(2)->setRowHeight(26);
                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension(4)->setRowHeight(22);

                // =====================
                // HEADER TABLE
                // =====================
                $headerRow = 6;

                $sheet->setCellValue("A$headerRow", 'NO');
                $sheet->setCellValue("B$headerRow", 'DESKRIPSI JENIS TARIF');

                $sheet->getStyle("A$headerRow:B$headerRow")->getFont()->setBold(true);
                $sheet->getStyle("A$headerRow:B$headerRow")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A$headerRow:B$headerRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF2E75B6');

                $sheet->getStyle("A$headerRow:B$headerRow")->getFont()->getColor()->setARGB('FFFFFFFF');

                // DROPDOWN FILTER
                $sheet->setAutoFilter("A$headerRow:B$headerRow");

                // WIDTH
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(50);

                // =====================
                // DATA
                // =====================
                $data = RefJenisTarif::select('DESKRIPSI_JENIS_TARIF')->get();
                $startData = $headerRow + 1;
                $row = $startData;
                $no = 1;

                foreach ($data as $item) {
                    $sheet->setCellValue("A$row", $no);
                    $sheet->setCellValue("B$row", $item->DESKRIPSI_JENIS_TARIF);
                    
                    // Aliginment nomor di tengah
                    $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    $row++;
                    $no++;
                }

                $endData = $row - 1;

                // =====================
                // STYLING DATA (Wrap & Border)
                // =====================
                $sheet->getStyle("B$startData:B$endData")
                    ->getAlignment()->setWrapText(true);

                // BORDER
                $sheet->getStyle("A$headerRow:B$endData")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // AUTO HEIGHT
                for ($i = $startData; $i <= $endData; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(-1);
                }

                // =====================
                // FOOTER (Tanda Tangan)
                // =====================
                $footerRow = $endData + 3;

                $role = $this->role ?: 'Bendahara';
                $nama = $this->nama ?: '-';
                $nip = $this->nip ?: '-';

                // TANGGAL
                $sheet->setCellValue("B" . ($footerRow), 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("B" . ($footerRow))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // TTD
                $sheet->setCellValue("B" . ($footerRow + 2), $role . ',');
                $sheet->setCellValue("B" . ($footerRow + 4), $nama);
                $sheet->setCellValue("B" . ($footerRow + 8), '-------------------------');
                $sheet->setCellValue("B" . ($footerRow + 9), 'NIP: ' . $nip);

                $sheet->getStyle("B" . ($footerRow + 2) . ":B" . ($footerRow + 9))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // FREEZE PANE
                $sheet->freezePane("A7");
            },
        ];
    }
}