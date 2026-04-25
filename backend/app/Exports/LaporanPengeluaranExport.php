<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection; 
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPengeluaranExport implements FromCollection, WithEvents 
{
    protected $start, $end, $sumberDana, $role, $nip;
    protected $total = 0;

    public function __construct($start, $end, $sumberDana, $role = null, $nip = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->sumberDana = $sumberDana;
        $this->role = $role ?: 'Bendahara';
        $this->nip = $nip;
    }

    public function collection()
    {
        $query = DB::table('tr_pm as tp')
            ->join('fpd_anggaran as fa', 'tp.ID_PROGRAM_KERJA', '=', 'fa.ID_PROGRAM_KERJA')
            ->join('dtl_fpd as df', 'fa.ID_FPD', '=', 'df.ID_FPD')
            ->join('dtl_program_kerja as dpk', 'fa.ID_PROGRAM_KERJA', '=', 'dpk.ID_PROGRAM_KERJA')
            ->join('mst_program_kerja as mpk', 'dpk.ID_PROGRAM_KERJA', '=', 'mpk.ID_PROGRAM_KERJA')
            ->join('ref_sumber_dana as rsd', 'dpk.ID_REF_DANA', '=', 'rsd.ID_REF_DANA')
            ->select(
                'tp.TGL_PM as tanggal',
                'mpk.PROGRAM_KERJA as program',
                'rsd.DESKRIPSI_SUMBER_DANA as sumber_dana',
                'tp.DESKRIPSI_TR_PM as uraian',
                DB::raw('(df.QTY * df.HARGA_SATUAN) as nominal')
            );

        if ($this->start && $this->end) {
            $query->whereBetween('tp.TGL_PM', [$this->start, $this->end]);
        }

        if ($this->sumberDana) {
            $query->where('dpk.ID_REF_DANA', $this->sumberDana);
        }

        return $query->orderBy('tp.TGL_PM', 'asc')->get();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $data = $this->collection(); 

                // Width
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(35);
                $sheet->getColumnDimension('F')->setWidth(22);

                // Title
                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN PENGELUARAN (KK)');
                $sheet->setCellValue('A4', 'Periode: ' . ($this->start ?? 'AWAL') . ' s/d ' . ($this->end ?? 'AKHIR'));
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');
                $sheet->getStyle('A2:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);

                // Header
                $headerRow = 6;
                $headers = ['NO', 'TANGGAL', 'PROGRAM KERJA', 'SUMBER DANA', 'URAIAN', 'NOMINAL'];
                $sheet->fromArray($headers, NULL, "A$headerRow");
                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle("A$headerRow:F$headerRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E75B6');

                // Data
                $row = $headerRow + 1; $no = 1;
                foreach ($data as $item) {
                    $sheet->setCellValue("A$row", $no++);
                    $sheet->setCellValue("B$row", $item->tanggal);
                    $sheet->setCellValue("C$row", $item->program);
                    $sheet->setCellValue("D$row", $item->sumber_dana);
                    $sheet->setCellValue("E$row", $item->uraian);
                    $sheet->setCellValue("F$row", $item->nominal);
                    $row++;
                }

                $endData = $row - 1;
                $sheet->getStyle("F" . ($headerRow + 1) . ":F$endData")->getNumberFormat()->setFormatCode('"Rp" #,##0');

                // Total
                $total = $data->sum('nominal');
                $totalRow = $endData + 2;
                $sheet->setCellValue("E$totalRow", 'TOTAL PENGELUARAN');
                $sheet->setCellValue("F$totalRow", $total);
                $sheet->getStyle("F$totalRow")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                
                
                $footerRow = $totalRow + 4;
                $role = $this->role;
                $nama = ($role === 'Kepala Sekolah') ? 'Drs. Budi Santoso' : 'Rina Putri, S.E.';
                
                $sheet->mergeCells("C" . ($footerRow+1) . ":E" . ($footerRow+1));
                $sheet->setCellValue("C" . ($footerRow+1), ($role === 'Kepala Sekolah' ? 'Kepala Sekolah,' : 'Bendahara,'));
                $sheet->setCellValue("C" . ($footerRow+3), $nama);
                $sheet->setCellValue("C" . ($footerRow+8), 'NIP: ' . ($this->nip ?: '-'));
                $sheet->getStyle("C" . ($footerRow+1) . ":E" . ($footerRow+8))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->freezePane("A7");
            },
        ];
    }
}