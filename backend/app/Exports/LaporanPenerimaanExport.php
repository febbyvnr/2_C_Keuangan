<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPenerimaanExport implements WithEvents, WithDrawings
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

    $data = $query
        ->orderBy('p.TANGGAL_TR_PENERIMAAN', 'asc')
        ->get();

    $this->total = $data->sum('jumlah');
    $this->rowCount = $data->count();

    return $data;
    }

   public function headings(): array
{
    return []; // FIX: hilangkan auto heading biar ga tabrakan
}

public function drawings()
{
    // Logo kiri (diperkecil)
    $drawingLeft = new Drawing();
    $drawingLeft->setName('Logo Kiri');
    $drawingLeft->setPath(public_path('logo.png'));
    $drawingLeft->setHeight(70); // FIX: lebih kecil
    $drawingLeft->setCoordinates('B2');
    $drawingLeft->setOffsetX(25);
    $drawingLeft->setOffsetY(5);

    // Logo kanan
    $drawingRight = new Drawing();
    $drawingRight->setName('Logo Kanan');
    $drawingRight->setPath(public_path('logo.png'));
    $drawingRight->setHeight(70); // FIX
    $drawingRight->setCoordinates('E2');
    $drawingRight->setOffsetX(-25);
    $drawingRight->setOffsetY(5);

    return [$drawingLeft, $drawingRight];
}

public function registerEvents(): array
{
    return [
        AfterSheet::class => function ($event) {

            $sheet = $event->sheet;

            // HAPUS ROW DEFAULT
            $sheet->getDelegate()->removeRow(1);

            // =====================
            // TITLE (SPACING DIRAPIKAN)
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

            // JARAK ANTAR JUDUL (FIX)
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
            // FOOTER (FIX POSISI)
            // =====================
            $footerRow = $totalRow + 4; // FIX: lebih turun

            // Bendahara (SEJAJAR KOLOM C)
            $sheet->setCellValue("C$footerRow", 'Bendahara');

            // Kepala Sekolah (kanan)
            $sheet->setCellValue("E$footerRow", 'Kepala Sekolah');

            // JARAK KE NAMA (FIX)
            $sheet->setCellValue("C" . ($footerRow+3), 'Rina Putri, S.E.');
            $sheet->setCellValue("C" . ($footerRow+4), 'NIP: 1987654321');

            $sheet->setCellValue("E" . ($footerRow+3), 'Drs. Budi Santoso');
            $sheet->setCellValue("E" . ($footerRow+4), 'NIP: 1976543210');

            // ALIGN
            $sheet->getStyle("C$footerRow:C" . ($footerRow+4))
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("E$footerRow:E" . ($footerRow+4))
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("C$footerRow")->getFont()->setBold(true);
            $sheet->getStyle("E$footerRow")->getFont()->setBold(true);

            // =====================
            // TANGGAL (LEBIH TURUN)
            // =====================
            $sheet->setCellValue("E" . ($footerRow+7), 'Yogyakarta, ' . date('d F Y'));

            $sheet->getStyle("E" . ($footerRow+7))
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // =====================
            // FREEZE
            // =====================
            $sheet->freezePane("A7");
        },
    ];
}
}