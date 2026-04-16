<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Auth;

class LaporanPengeluaranExport implements WithEvents
{
    protected $start, $end, $sumberDana;
    protected $total = 0;
    protected $rowCount = 0;
    protected $role = 'Bendahara'; 

    public function __construct($start, $end, $sumberDana, $role = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->sumberDana = $sumberDana;
        $this->role = $role ?? 'Bendahara';
    }

    public function collection()
    {
        $query = DB::table('tr_pm as tp')
            ->join('ref_pm as rp', 'tp.ID_PM', '=', 'rp.ID_TR_PM')
            ->join('fpd_anggaran as fa', 'rp.ID_PROGRAM_KERJA', '=', 'fa.ID_PROGRAM_KERJA')
            ->join('dtl_fpd as df', 'fa.ID_DT_PROGKER', '=', 'df.ID_DT_PROGKER')
            ->join('dtl_program_kerja as dpk', 'fa.ID_PROGRAM_KERJA', '=', 'dpk.ID_PROGRAM_KERJA')
            ->join('mst_program_kerja as mpk', 'dpk.ID_PROGRAM_KERJA', '=', 'mpk.ID_PROGRAM_KERJA')
            ->join('ref_sumber_dana as rsd', 'dpk.ID_REF_DANA', '=', 'rsd.ID_REF_DANA')
            ->select(
                'tp.TGL_PM as tanggal',
                'mpk.PROGRAM_KERJA as program',
                'tp.DESKRIPSI_TR_PM as uraian',
                DB::raw('(df.QTY * df.HARGA_SATUAN) as nominal'),
                'rsd.DESKRIPSI_SUMBER_DANA as sumber_dana'
            );

        if ($this->start && $this->end) {
            $query->whereBetween('tp.TGL_PM', [$this->start, $this->end]);
        }

        if ($this->sumberDana) {
            $query->where('dpk.ID_REF_DANA', $this->sumberDana);
        }

        $data = $query->orderBy('tp.TGL_PM', 'asc')->get();

        $this->total = $data->sum('nominal');
        $this->rowCount = $data->count();

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;

                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN PENGELUARAN (KK)');
                $sheet->setCellValue('A4', 'Periode: ' . ($this->start ?? 'AWAL') . ' s/d ' . ($this->end ?? 'AKHIR'));

                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(17);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('A2:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $headerRow = 6;
                $sheet->setCellValue("A$headerRow", 'NO');
                $sheet->setCellValue("B$headerRow", 'TANGGAL');
                $sheet->setCellValue("C$headerRow", 'PROGRAM KERJA');
                $sheet->setCellValue("D$headerRow", 'SUMBER DANA');
                $sheet->setCellValue("E$headerRow", 'URAIAN');
                $sheet->setCellValue("F$headerRow", 'NOMINAL');

                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle("A$headerRow:F$headerRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A$headerRow:F$headerRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC00000');

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(35);
                $sheet->getColumnDimension('F')->setWidth(20);

                $row = $headerRow + 1;
                $no = 1;
                foreach ($this->collection() as $item) {
                    $sheet->setCellValue("A$row", $no);
                    $sheet->setCellValue("B$row", $item->tanggal);
                    $sheet->setCellValue("C$row", $item->program);
                    $sheet->setCellValue("D$row", $item->sumber_dana);
                    $sheet->setCellValue("E$row", $item->uraian);
                    $sheet->setCellValue("F$row", $item->nominal);
                    $row++;
                    $no++;
                }

                $endData = $row - 1;
                $sheet->getStyle("C7:E$endData")->getAlignment()->setWrapText(true);
                $sheet->getStyle("F7:F$endData")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("A$headerRow:F$endData")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $totalRow = $endData + 2;
                $sheet->setCellValue("E$totalRow", 'TOTAL PENGELUARAN');
                $sheet->setCellValue("F$totalRow", $this->total);
                $sheet->getStyle("E$totalRow:F$totalRow")->getFont()->setBold(true);
                $sheet->getStyle("F$totalRow")->getNumberFormat()->setFormatCode('"Rp" #,##0');

                $footerRow = $totalRow + 4;
                $sheet->setCellValue("F" . $footerRow, 'Yogyakarta, ' . date('d F Y'));
                $sheet->setCellValue("F" . ($footerRow + 1), 'Mengetahui,');
                $sheet->setCellValue("F" . ($footerRow + 2), $this->role);
                $sheet->setCellValue("F" . ($footerRow + 5), Auth::user()->name ?? '.......................');
                $sheet->getStyle("F$footerRow:F" . ($footerRow + 5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->freezePane("A7");
            },
        ];
    }
}