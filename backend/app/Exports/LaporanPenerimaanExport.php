<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPenerimaanExport implements WithEvents
{
    protected $start, $end, $sumberDana;
    protected $total = 0;
    protected $rowCount = 0;

    public function __construct($start, $end, $sumberDana)
    {
        $this->start = $start;
        $this->end = $end;
        $this->sumberDana = $sumberDana;
    }

    public function collection()
    {
        $query = DB::table('tr_penerimaan as p') 
            ->join('ref_penerimaan as rp', 'p.ID_REF_PENERIMAAN', '=', 'rp.ID_REF_PENERIMAAN')
            ->select(
                'p.TANGGAL_TR_PENERIMAAN as tanggal',
                'rp.DESKRIPSI_REF_PENERIMAAN as jenis',
                'p.DESKRIPSI_TR_PENERIMAAN as uraian',
                'p.JUMLAH_TR_PENERIMAAN as jumlah'
            )
            ->whereNotNull('p.NIP_PENERIMA'); 

        if ($this->start && $this->end) {
            $query->whereBetween('p.TANGGAL_TR_PENERIMAAN', [$this->start, $this->end]);
        }

        if ($this->sumberDana) {
            $query->where('p.ID_REF_DANA', $this->sumberDana);
        }

        $data = $query->orderBy('p.TANGGAL_TR_PENERIMAAN', 'asc')->get();

        $this->total = $data->sum('jumlah');
        $this->rowCount = $data->count();

        return $data;
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
                $sheet->setCellValue('A3', 'LAPORAN PENERIMAAN (KM)');
                $sheet->setCellValue('A4', 'Periode: ' . ($this->start ?? 'AWAL') . ' s/d ' . ($this->end ?? 'AKHIR'));

                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                $sheet->mergeCells('A4:E4');

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
                $sheet->setCellValue("B$headerRow", 'TANGGAL');
                $sheet->setCellValue("C$headerRow", 'JENIS PENERIMAAN');
                $sheet->setCellValue("D$headerRow", 'URAIAN');
                $sheet->setCellValue("E$headerRow", 'JUMLAH');

                $sheet->getStyle("A$headerRow:E$headerRow")->getFont()->setBold(true);
                $sheet->getStyle("A$headerRow:E$headerRow")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A$headerRow:E$headerRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF2E75B6');

                $sheet->getStyle("A$headerRow:E$headerRow")->getFont()->getColor()->setARGB('FFFFFFFF');

                // WIDTH
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(32);
                $sheet->getColumnDimension('E')->setWidth(20);

                // =====================
                // DATA
                // =====================
                $startData = $headerRow + 1;
                $row = $startData;
                $no = 1;

                $dataCollection = $this->collection();

                foreach ($dataCollection as $item) {
                    $sheet->setCellValue("A$row", $no);
                    $sheet->setCellValue("B$row", $item->tanggal);
                    $sheet->setCellValue("C$row", $item->jenis);
                    $sheet->setCellValue("D$row", $item->uraian);
                    $sheet->setCellValue("E$row", $item->jumlah);
                    $row++;
                    $no++;
                }

                $endData = $row - 1;

                // FORMAT RP
                $sheet->getStyle("E$startData:E$endData")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');

                // BORDER
                $sheet->getStyle("A$headerRow:E$endData")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // =====================
                // TOTAL
                // =====================
                $totalRow = $endData + 1;

                $sheet->setCellValue("D$totalRow", 'TOTAL PENERIMAAN');
                $sheet->setCellValue("E$totalRow", $this->total);

                $sheet->getStyle("D$totalRow:E$totalRow")->getFont()->setBold(true);

                $sheet->getStyle("E$totalRow")->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');

                $sheet->getStyle("D$totalRow:E$totalRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFE699');

               // =====================
// FOOTER (FIX CENTER)
// =====================
$footerRow = $totalRow + 6;

// 🔥 MERGE biar bener-bener tengah
$sheet->mergeCells("B$footerRow:C$footerRow"); // Bendahara
$sheet->mergeCells("D$footerRow:E$footerRow"); // Kepsek

$sheet->mergeCells("B" . ($footerRow+5) . ":C" . ($footerRow+5));
$sheet->mergeCells("B" . ($footerRow+6) . ":C" . ($footerRow+6));

$sheet->mergeCells("D" . ($footerRow+5) . ":E" . ($footerRow+5));
$sheet->mergeCells("D" . ($footerRow+6) . ":E" . ($footerRow+6));

// JABATAN
$sheet->setCellValue("B$footerRow", 'Bendahara');
$sheet->setCellValue("D$footerRow", 'Kepala Sekolah');

// NAMA
$sheet->setCellValue("B" . ($footerRow+5), 'Rina Putri, S.E.');
$sheet->setCellValue("B" . ($footerRow+6), 'NIP: 1987654321');

$sheet->setCellValue("D" . ($footerRow+5), 'Drs. Budi Santoso');
$sheet->setCellValue("D" . ($footerRow+6), 'NIP: 1976543210');

// 🔥 CENTER SEMUA
$sheet->getStyle("B$footerRow:B" . ($footerRow+6))
    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->getStyle("D$footerRow:D" . ($footerRow+6))
    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// BOLD JABATAN
$sheet->getStyle("B$footerRow")->getFont()->setBold(true);
$sheet->getStyle("D$footerRow")->getFont()->setBold(true);

                // =====================
                // TANGGAL (DITURUNKAN BANGET)
                // =====================
                $sheet->setCellValue("E" . ($footerRow+11), 'Yogyakarta, ' . date('d F Y'));

                $sheet->getStyle("E" . ($footerRow+11))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // FREEZE
                $sheet->freezePane("A7");
            },
        ];
    }
}