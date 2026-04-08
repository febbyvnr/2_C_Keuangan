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
        $drawing->setPath(public_path('logo.png')); // taruh di public/
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet;

                // TITLE
                $sheet->setCellValue('A1', 'LAPORAN PENERIMAAN KAS (BKM)');
                $sheet->setCellValue('A2', 'Periode: ' . $this->start . ' s/d ' . $this->end);

                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');

                $sheet->getStyle('A1:A2')->getFont()->setBold(true);

                // Geser tabel
                $sheet->insertNewRowBefore(4, 2);

                // TOTAL
                $totalRow = $this->rowCount + 6;

                $sheet->setCellValue('C' . $totalRow, 'TOTAL');
                $sheet->setCellValue('D' . $totalRow, $this->total);

                $sheet->getStyle('C' . $totalRow . ':D' . $totalRow)
                      ->getFont()->setBold(true);
            },
        ];
    }
}