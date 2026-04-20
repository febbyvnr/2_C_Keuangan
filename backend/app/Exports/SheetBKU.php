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

    public function __construct($data, $role = 'Bendahara') 
    {
        $this->data = $data;
        $this->role = $role;
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
                // 🔥 AMBIL DATA DARI DB (NIP)
                // =====================
                $pejabat = DB::table('tr_jabatan as tj')
                    ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                    ->where('rj.DESKRIPSI_JABATAN', $this->role)
                    ->whereNull('tj.TGL_SELESAI_JABATAN')
                    ->select('tj.NIP_KARYAWAN')
                    ->first();

                $nip = $pejabat->NIP_KARYAWAN ?? '-';

                // 🔥 Nama sementara (karena belum ada tabel karyawan)
                $nama = $this->role === 'Kepala Sekolah'
                    ? 'Nama Kepala Sekolah'
                    : 'Nama Bendahara';

                // =====================
                // TITLE
                // =====================
                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM (BKU)');
                $sheet->setCellValue('A4', 'Periode Laporan');

                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');

                $sheet->getStyle('A2:A4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(17);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(15);

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

                // =====================
                // WIDTH
                // =====================
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(40);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(22);

                // =====================
                // DATA
                // =====================
                $startData = $headerRow + 1;
                $row = $startData;
                $no = 1;
                $saldoAkhir = 0;

                foreach ($this->data as $item) {

                    $sheet->setCellValue("A$row", $no);
                    $sheet->setCellValue("B$row", date('d-m-Y', strtotime($item->tanggal)));
                    $sheet->setCellValue("C$row", $item->uraian);
                    $sheet->setCellValue("D$row", $item->debit);
                    $sheet->setCellValue("E$row", $item->kredit);
                    $sheet->setCellValue("F$row", $item->saldo);

                    $saldoAkhir = $item->saldo;

                    $row++;
                    $no++;
                }

                $endData = $row - 1;

                // FORMAT NUMBER (NO RP)
                $sheet->getStyle("D$startData:F$endData")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // BORDER
                $sheet->getStyle("A$headerRow:F$endData")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // =====================
                // 🔥 AUTO FILTER
                // =====================
                $sheet->setAutoFilter("A$headerRow:F$endData");

                // =====================
                // SALDO AKHIR
                // =====================
                $totalRow = $endData + 2;

                $sheet->setCellValue("E$totalRow", 'SALDO AKHIR');
                $sheet->setCellValue("F$totalRow", $saldoAkhir);

                $sheet->getStyle("F$totalRow")->getNumberFormat()
                    ->setFormatCode('#,##0');

                // =====================
                // 🔥 FOOTER (SESUAI REFERENSI DOSEN)
                // =====================
                $footer = $totalRow + 4;

                // ROLE
                $sheet->mergeCells("B$footer:E$footer");
                $sheet->setCellValue("B$footer", $this->role . ',');
                $sheet->getStyle("B$footer")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // NAMA
                $sheet->mergeCells("B" . ($footer + 2) . ":E" . ($footer + 2));
                $sheet->setCellValue("B" . ($footer + 2), $nama);
                $sheet->getStyle("B" . ($footer + 2))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // GARIS
                $sheet->mergeCells("B" . ($footer + 4) . ":E" . ($footer + 4));
                $sheet->setCellValue("B" . ($footer + 4), '--------------------------');
                $sheet->getStyle("B" . ($footer + 4))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // NIP (DARI DB)
                $sheet->mergeCells("B" . ($footer + 5) . ":E" . ($footer + 5));
                $sheet->setCellValue("B" . ($footer + 5), 'NIP: ' . $nip);
                $sheet->getStyle("B" . ($footer + 5))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // TANGGAL (KANAN)
                $sheet->setCellValue("F" . ($footer + 6), 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("F" . ($footer + 6))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // FREEZE
                $sheet->freezePane("A7");
            },
        ];
    }
}