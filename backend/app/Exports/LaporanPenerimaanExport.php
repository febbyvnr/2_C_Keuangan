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
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo Sekolah');
        $drawing->setPath(public_path('logo.png')); 
        $drawing->setHeight(60);
        $drawing->setCoordinates('G1');\
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(5);

        return $drawing;
    }

  public function registerEvents(): array
{
    return [
        AfterSheet::class => function ($event) {

            $sheet = $event->sheet;

            // =====================
            // TITLE
            // =====================
            $sheet->setCellValue('A1', 'SMK BOPKRI 2 YOGYAKARTA');
            $sheet->setCellValue('A2', 'LAPORAN PENERIMAAN KAS (BKM)');
            $sheet->setCellValue('A3', 'Periode: ' . ($this->start ?? '-') . ' s/d ' . ($this->end ?? '-'));

            $sheet->mergeCells('A1:D1');
            $sheet->mergeCells('A2:D2');
            $sheet->mergeCells('A3:D3');

            // STYLE TITLE
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('A3')->getFont()->setSize(11);

            $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

            // =====================
            // HEADER TABLE
            // =====================
            $headerRow = 5;

            $sheet->setCellValue("A$headerRow", 'Tanggal');
            $sheet->setCellValue("B$headerRow", 'Jenis Penerimaan');
            $sheet->setCellValue("C$headerRow", 'Uraian');
            $sheet->setCellValue("D$headerRow", 'Jumlah (Rp)');

            // STYLE HEADER
            $sheet->getStyle("A$headerRow:D$headerRow")->getFont()->setBold(true);

            $sheet->getStyle("A$headerRow:D$headerRow")->getAlignment()
                ->setHorizontal('center');

            $sheet->getStyle("A$headerRow:D$headerRow")->getFill()
                ->setFillType('solid')
                ->getStartColor()->setARGB('FFEFEFEF');

            // =====================
            // AUTO WIDTH
            // =====================
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // =====================
            // DATA RANGE
            // =====================
            $startData = 6;
            $endData = $this->rowCount + 5;

            // FORMAT ANGKA
            $sheet->getStyle("D$startData:D$endData")
                ->getNumberFormat()
                ->setFormatCode('#,##0');

            // ALIGNMENT
            $sheet->getStyle("A$startData:A$endData")
                ->getAlignment()->setHorizontal('center');

            $sheet->getStyle("D$startData:D$endData")
                ->getAlignment()->setHorizontal('right');

            // =====================
            // BORDER TABLE
            // =====================
            $sheet->getStyle("A$headerRow:D$endData")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // =====================
            // TOTAL
            // =====================
            $totalRow = $endData + 1;

            $sheet->setCellValue("C$totalRow", 'TOTAL');
            $sheet->setCellValue("D$totalRow", $this->total);

            $sheet->getStyle("C$totalRow:D$totalRow")->getFont()->setBold(true);

            // =====================
            // BORDER TOTAL
            // =====================
            $sheet->getStyle("A$totalRow:D$totalRow")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        },
    ];
}
}