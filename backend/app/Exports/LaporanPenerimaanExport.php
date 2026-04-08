<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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
         $drawing->setCoordinates('E1');

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
            $sheet->setCellValue('A1', 'LAPORAN PENERIMAAN KAS (BKM)');
            $sheet->setCellValue('A2', 'Periode: ' . ($this->start ?? '-') . ' s/d ' . ($this->end ?? '-'));

            $sheet->mergeCells('A1:D1');
            $sheet->mergeCells('A2:D2');

            // 🔥 STYLE TITLE
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A2')->getFont()->setSize(12);

            $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');

            // =====================
            // GESER TABLE
            // =====================
            $sheet->insertNewRowBefore(4, 2);

            // =====================
            // HEADER TABLE STYLE
            // =====================
            $headerRange = 'A4:D4';

            $sheet->getStyle($headerRange)->getFont()->setBold(true);

            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');

            // background abu soft
            $sheet->getStyle($headerRange)->getFill()->setFillType('solid')
                ->getStartColor()->setARGB('FFEFEFEF');

            // =====================
            // AUTO WIDTH
            // =====================
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // =====================
            // FORMAT ANGKA (RUPIAH)
            // =====================
            $lastRow = $this->rowCount + 5;

            $sheet->getStyle('D5:D' . $lastRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0');

            // =====================
            // BORDER TABLE
            // =====================
            $tableRange = 'A4:D' . $lastRow;

            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // =====================
            // TOTAL
            // =====================
            $totalRow = $this->rowCount + 6;

            $sheet->setCellValue('C' . $totalRow, 'TOTAL');
            $sheet->setCellValue('D' . $totalRow, $this->total);

            $sheet->getStyle('C' . $totalRow . ':D' . $totalRow)
                ->getFont()->setBold(true);

            // =====================
            // ALIGNMENT
            // =====================
            $sheet->getStyle('A5:A' . $lastRow)->getAlignment()->setHorizontal('center'); // tanggal
            $sheet->getStyle('D5:D' . $lastRow)->getAlignment()->setHorizontal('right'); // jumlah
        },
    ];
}
}