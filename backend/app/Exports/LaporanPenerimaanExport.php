<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPenerimaanExport implements FromCollection, WithHeadings, WithEvents, WithDrawings
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
        return [
            'Tanggal',
            'Jenis Penerimaan',
            'Uraian',
            'Jumlah (Rp)'
        ];
    }

    public function drawings()
    {
        // Logo kiri
        $drawingLeft = new Drawing();
        $drawingLeft->setName('Logo Kiri');
        $drawingLeft->setDescription('Logo Sekolah Kiri');
        $drawingLeft->setPath(public_path('logo.png'));
        $drawingLeft->setHeight(80);
        $drawingLeft->setCoordinates('A1');
        $drawingLeft->setOffsetX(5);
        $drawingLeft->setOffsetY(5);

        // Logo kanan
        $drawingRight = new Drawing();
        $drawingRight->setName('Logo Kanan');
        $drawingRight->setDescription('Logo Sekolah Kanan');
        $drawingRight->setPath(public_path('logo.png'));
        $drawingRight->setHeight(80);
        $drawingRight->setCoordinates('F1');
        $drawingRight->setOffsetX(5);
        $drawingRight->setOffsetY(5);

        return [$drawingLeft, $drawingRight];
    }

   public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet;

                // =====================
                // TITLE & HEADER SEKOLAH
                // =====================
                $sheet->setCellValue('A1', 'YAYASAN BOPKRI');
                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN PENERIMAAN KAS (BKM)');
                $sheet->setCellValue('A4', 'Periode: ' . ($this->start ?? 'AWAL') . ' s/d ' . ($this->end ?? 'AKHIR'));

                // Merge cells untuk title
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');

                // Style Title
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A4')->getFont()->setSize(11)->setItalic(true);

                $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Background abu-abu muda untuk header sekolah
                $sheet->getStyle('A1:F4')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF5F5F5');

                // =====================
                // HEADER TABLE
                // =====================
                $headerRow = 6; // geser karena ada baris title lebih banyak

                // Set header values
                $sheet->setCellValue("A$headerRow", 'NO');
                $sheet->setCellValue("B$headerRow", 'TANGGAL');
                $sheet->setCellValue("C$headerRow", 'JENIS PENERIMAAN');
                $sheet->setCellValue("D$headerRow", 'URAIAN');
                $sheet->setCellValue("E$headerRow", 'JUMLAH (Rp)');
                $sheet->setCellValue("F$headerRow", 'KETERANGAN');

                // Style Header Table
                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle("A$headerRow:F$headerRow")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Warna header table (biru tua)
                $sheet->getStyle("A$headerRow:F$headerRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF2E75B6');

                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->getColor()->setARGB('FFFFFFFF');

                // =====================
                // AUTO WIDTH
                // =====================
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);

                // =====================
                // DATA RANGE
                // =====================
                $startData = $headerRow + 1;
                $endData = $headerRow + $this->rowCount;

                // Isi nomor urut dan data
                if ($this->rowCount > 0) {
                    $dataCollection = $this->collection();
                    $row = $startData;
                    $no = 1;
                    foreach ($dataCollection as $item) {
                        $sheet->setCellValue("A$row", $no);
                        $sheet->setCellValue("B$row", $item->tanggal);
                        $sheet->setCellValue("C$row", $item->jenis);
                        $sheet->setCellValue("D$row", $item->uraian);
                        $sheet->setCellValue("E$row", $item->jumlah);
                        $sheet->setCellValue("F$row", '-'); // default keterangan kosong
                        $row++;
                        $no++;
                    }
                }

                // Format angka Rupiah
                $sheet->getStyle("E$startData:E$endData")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Alignment data
                $sheet->getStyle("A$startData:A$endData")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B$startData:B$endData")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E$startData:E$endData")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F$startData:F$endData")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Border semua sel data
                $sheet->getStyle("A$headerRow:F$endData")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Warna baris header table
                $sheet->getStyle("A$headerRow:F$headerRow")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // =====================
                // TOTAL
                // =====================
                $totalRow = $endData + 1;

                $sheet->setCellValue("D$totalRow", 'TOTAL PENERIMAAN');
                $sheet->setCellValue("E$totalRow", $this->total);
                $sheet->setCellValue("F$totalRow", '');

                $sheet->getStyle("D$totalRow:E$totalRow")->getFont()->setBold(true);
                $sheet->getStyle("D$totalRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E$totalRow")->getNumberFormat()->setFormatCode('#,##0');

                // Background total (kuning muda)
                $sheet->getStyle("D$totalRow:E$totalRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFEB9C');

                // Border total
                $sheet->getStyle("A$totalRow:F$totalRow")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // =====================
                // FOOTER (TTD)
                // =====================
                $footerRow = $totalRow + 2;
                
                $sheet->setCellValue("D$footerRow", 'Mengetahui,');
                $sheet->setCellValue("F$footerRow", 'Yogyakarta, ' . date('d F Y'));
                
                $sheet->setCellValue("D" . ($footerRow+1), 'Kepala Sekolah');
                $sheet->setCellValue("F" . ($footerRow+1), 'Bendahara');
                
                $sheet->setCellValue("D" . ($footerRow+3), '(_______________________)');
                $sheet->setCellValue("F" . ($footerRow+3), '(_______________________)');
                
                $sheet->getStyle("D$footerRow:F" . ($footerRow+3))->getFont()->setSize(10);
                $sheet->getStyle("D$footerRow:F" . ($footerRow+3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // =====================
                // FREEZE PANE 
                // =====================
                $sheet->freezePane("A" . ($headerRow + 1));
            },
        ];
    }
}