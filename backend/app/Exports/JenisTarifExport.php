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
                $sheet->setCellValue('A3', 'LAPORAN JENIS TARIF');
                $sheet->mergeCells('A2:B2');
                $sheet->mergeCells('A3:B3');

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A2:A3')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // SPACING JUDUL
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(12);
                $sheet->getRowDimension(5)->setRowHeight(6);
                $sheet->getRowDimension(6)->setRowHeight(8);

                $sheet->mergeCells('A5:B5');
                $sheet->getStyle('A5:B5')
                    ->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_THICK);

                // =====================
                // HEADER TABLE
                // =====================
                $headerRow = 7;

                $sheet->setCellValue("A$headerRow", 'NO');
                $sheet->setCellValue("B$headerRow", 'DESKRIPSI JENIS TARIF');

                $sheet->getStyle("A$headerRow:B$headerRow")->getFont()->setBold(true);
                $sheet->getStyle("A$headerRow:B$headerRow")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setShowGridlines(false);

                // WIDTH
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(55);

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
                $totalRow = $endData + 1;

                $sheet->setCellValue("A$totalRow", 'TOTAL JENIS TARIF');
                $sheet->setCellValue("B$totalRow", $no - 1);
                $sheet->getStyle("A$totalRow:B$totalRow")->getFont()->setBold(true);
                $sheet->getStyle("A$totalRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B$totalRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A$headerRow:B$totalRow")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // =====================
                // STYLING DATA (Wrap & Border)
                // =====================
                $sheet->getStyle("B$startData:B$endData")
                    ->getAlignment()->setWrapText(true);

                // AUTO HEIGHT
                for ($i = $startData; $i <= $endData; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(-1);
                }

                // =====================
                // FOOTER (Tanda Tangan)
                // =====================
                $footerRow = $totalRow + 3;

                $role = $this->role ?: 'Bendahara';
                $nama = $this->nama ?: '-';
                $nip = $this->nip ?: '-';

                // TTD
                $sheet->mergeCells("A" . ($footerRow + 2) . ":B" . ($footerRow + 2));
                $sheet->mergeCells("A" . ($footerRow + 4) . ":B" . ($footerRow + 4));
                $sheet->mergeCells("A" . ($footerRow + 8) . ":B" . ($footerRow + 8));
                $sheet->mergeCells("A" . ($footerRow + 9) . ":B" . ($footerRow + 9));

                $sheet->setCellValue("A" . ($footerRow + 2), $role . ',');
                $sheet->setCellValue("A" . ($footerRow + 4), $nama);
                $sheet->setCellValue("A" . ($footerRow + 8), '-------------------------');
                $sheet->setCellValue("A" . ($footerRow + 9), 'NIP: ' . $nip);

                $sheet->getStyle("A" . ($footerRow + 2) . ":A" . ($footerRow + 9))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $dateRow = $footerRow + 11;
                $sheet->mergeCells("A$dateRow:B$dateRow");
                $sheet->setCellValue("A$dateRow", 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("A$dateRow")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            },
        ];
    }
}