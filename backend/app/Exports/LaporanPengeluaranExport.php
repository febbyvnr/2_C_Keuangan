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
    protected $user;

    public function __construct($start, $end, $sumberDana)
    {
        $this->start = $start;
        $this->end = $end;
        $this->sumberDana = $sumberDana;
        
        $this->user = DB::table('users as u')
            ->leftJoin('tr_jabatan as tj', 'u.nip', '=', 'tj.NIP_KARYAWAN') 
            ->leftJoin('ref_jabatan_str as rjs', 'tj.id_jabatan', '=', 'rjs.id_jabatan')
            ->where('u.id', Auth::id())
            ->select('u.name', 'tj.NIP_KARYAWAN as nip', 'rjs.DESKRIPSI_JABATAN as nama_jabatan') 
            ->first();
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

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;
                $dataCollection = $this->collection();

                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN PENGELUARAN (KK)');
                $sheet->setCellValue('A4', 'Periode: ' . ($this->start ?? 'AWAL') . ' s/d ' . ($this->end ?? 'AKHIR'));
                $sheet->mergeCells('A2:F2'); $sheet->mergeCells('A3:F3'); $sheet->mergeCells('A4:F4');
                $sheet->getStyle('A2:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $headerRow = 6;
                $headers = ['NO', 'TANGGAL', 'PROGRAM KERJA', 'SUMBER DANA', 'URAIAN', 'NOMINAL'];
                $sheet->fromArray($headers, NULL, "A$headerRow");
                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle("A$headerRow:F$headerRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC00000');
                $sheet->getStyle("A$headerRow:F$headerRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row = 7; $no = 1;
                foreach ($dataCollection as $item) {
                    $sheet->setCellValue("A$row", $no++);
                    $sheet->setCellValue("B$row", $item->tanggal);
                    $sheet->setCellValue("C$row", $item->program);
                    $sheet->setCellValue("D$row", $item->sumber_dana);
                    $sheet->setCellValue("E$row", $item->uraian);
                    $sheet->setCellValue("F$row", $item->nominal);
                    $row++;
                }

                $endData = $row - 1;
                $sheet->getStyle("F7:F$endData")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("A$headerRow:F$endData")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $totalRow = $endData + 2;
                $sheet->setCellValue("E$totalRow", 'TOTAL PENGELUARAN');
                $sheet->setCellValue("F$totalRow", $this->total);
                $sheet->getStyle("F$totalRow")->getNumberFormat()->setFormatCode('"Rp" #,##0');

                $footerRow = $totalRow + 4;
                $sheet->setCellValue("F" . $footerRow, 'Yogyakarta, ' . date('d F Y'));
                $sheet->setCellValue("F" . ($footerRow + 1), ($this->user->nama_jabatan ?? 'Bendahara'));
                $sheet->setCellValue("F" . ($footerRow + 4), ($this->user->name ?? '-'));
                $sheet->setCellValue("F" . ($footerRow + 5), 'NIP. ' . ($this->user->nip ?? '-'));
                
                $sheet->getStyle("F" . $footerRow . ":F" . ($footerRow + 5))
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->freezePane("A7");
            },
        ];
    }
}