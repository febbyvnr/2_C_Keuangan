<?php

namespace App\Exports;

use App\Models\RefJenisPembayaran;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Auth;

class RefJenisPembayaranExport implements WithEvents
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
                $sheet->setCellValue('A3', 'LAPORAN METODE PEMBAYARAN');

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
                $sheet->setCellValue("B$headerRow", 'DESKRIPSI METODE PEMBAYARAN');

                $sheet->getStyle("A$headerRow:B$headerRow")
                    ->getFont()->setBold(true);

                $sheet->getStyle("A$headerRow:B$headerRow")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setShowGridlines(false);

                // WIDTH
                $sheet->getColumnDimension('A')->setWidth(16);
                $sheet->getColumnDimension('B')->setWidth(48);

                // =====================
                // DATA
                // =====================
                $data = RefJenisPembayaran::select('DESKRIPSI_METODE_PEMBAYARAN')->get();

                $startData = $headerRow + 1;
                $row = $startData;
                $no = 1;

                foreach ($data as $item) {
                    $sheet->setCellValue("A$row", $no);
                    $sheet->setCellValue("B$row", $item->DESKRIPSI_METODE_PEMBAYARAN);

                    // Alignment nomor di tengah
                    $sheet->getStyle("A$row")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $row++;
                    $no++;
                }

                $endData = $row - 1;
                $totalRow = $endData + 1;

                // TOTAL
                $sheet->setCellValue("A$totalRow", 'TOTAL METODE PEMBAYARAN');
                $sheet->setCellValue("B$totalRow", $no - 1);

                $sheet->getStyle("A$totalRow:B$totalRow")
                    ->getFont()->setBold(true);

                $sheet->getStyle("A$totalRow")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("B$totalRow")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // BORDER
                $sheet->getStyle("A$headerRow:B$totalRow")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // =====================
                // STYLING DATA (Wrap)
                // =====================
                $sheet->getStyle("B$startData:B$endData")
                    ->getAlignment()
                    ->setWrapText(true);

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
                $dateRow = $footerRow + 2;
                $roleRow = $footerRow + 3;
                $nameRow = $footerRow + 7;
                $lineRow = $footerRow + 8;
                $nipRow = $footerRow + 9;

                $sheet->setCellValue("B$dateRow", 'Yogyakarta, ' . date('d F Y'));
                $sheet->setCellValue("B$roleRow", $role . ',');
                $sheet->setCellValue("B$nameRow", $nama);
                $sheet->setCellValue("B$lineRow", '-------------------------');
                $sheet->setCellValue("B$nipRow", 'NIP: ' . $nip);

                $sheet->getStyle("B$dateRow:B$nipRow")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}