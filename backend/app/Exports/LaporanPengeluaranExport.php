<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPengeluaranExport implements WithEvents
{
    protected $start, $end, $sumberDana, $role, $nip, $nama, $nip_ttd;

    public function __construct($start, $end, $sumberDana, $role = null, $nip = null, $nama = null, $nip_ttd = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->sumberDana = $sumberDana;
        $this->role = $role ?: 'Bendahara';
        $this->nip = $nip; // NIP user pengakses
        $this->nama = $nama; // Nama pejabat TTD (Dinamis)
        $this->nip_ttd = $nip_ttd; // NIP pejabat TTD (Dinamis)
    }

    /**
     * Mengambil data berdasarkan struktur database asli:
     * tr_pm -> dtl_program_kerja -> mst_program_kerja
     */
    public function collection()
    {
        $query = DB::table('tr_pm as tp')
            
            ->join('dtl_program_kerja as dpk', 'tp.ID_PROGRAM_KERJA', '=', 'dpk.ID_PROGRAM_KERJA')
            ->join('mst_program_kerja as mpk', 'dpk.ID_PROGRAM_KERJA', '=', 'mpk.ID_PROGRAM_KERJA')
            ->join('ref_sumber_dana as rsd', 'dpk.ID_REF_DANA', '=', 'rsd.ID_REF_DANA')
            ->select(
                'tp.TGL_PM as tanggal',
                'mpk.PROGRAM_KERJA as program',      // Nama Program dari Master
                'rsd.DESKRIPSI_SUMBER_DANA as sumber_dana',
                'tp.DESKRIPSI_TR_PM as uraian',
                'dpk.NOMINAL as nominal'             // Nominal dari tabel detail
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
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;
                $data = $this->collection();

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(40); 
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(35);
                $sheet->getColumnDimension('F')->setWidth(22);

                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN PENGELUARAN (KK)');
                $sheet->setCellValue('A4', 'Periode: ' . ($this->start ?? 'AWAL') . ' s/d ' . ($this->end ?? 'AKHIR'));

                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');

                $sheet->getStyle('A2:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);

                $headerRow = 6;
                $headers = ['NO', 'TANGGAL', 'PROGRAM KERJA', 'SUMBER DANA', 'URAIAN', 'NOMINAL'];
                
                foreach ($headers as $index => $header) {
                    $column = chr(65 + $index);
                    $sheet->setCellValue($column . $headerRow, $header);
                }
                
                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle("A$headerRow:F$headerRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E75B6');
                $sheet->getStyle("A$headerRow:F$headerRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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
                $sheet->getStyle("A$headerRow:F$endData")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("F" . ($headerRow + 1) . ":F$endData")->getNumberFormat()->setFormatCode('"Rp" #,##0');

                $total = $data->sum('nominal');
                $totalRow = $endData + 2;
                $sheet->setCellValue("E$totalRow", 'TOTAL PENGELUARAN');
                $sheet->setCellValue("F$totalRow", $total);
                $sheet->getStyle("E$totalRow:F$totalRow")->getFont()->setBold(true);
                $sheet->getStyle("F$totalRow")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("E$totalRow:F$totalRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE699');

                // Bagian Tanda Tangan
                $footerRow = $totalRow + 3;
                $roleLabel = ucfirst(strtolower(trim($this->role)));
                $namaPejabat = $this->nama ?? '-';
                $nipPejabat = $this->nip_ttd ?? '-';
                
                $sheet->setCellValue("F" . ($footerRow), 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("F" . ($footerRow))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue("F" . ($footerRow + 1), $roleLabel . ',');
                $sheet->setCellValue("F" . ($footerRow + 5), $namaPejabat);
                $sheet->setCellValue("F" . ($footerRow + 6), 'NIP: ' . $nipPejabat);
                $sheet->getStyle("F" . ($footerRow + 1) . ":F" . ($footerRow + 6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F" . ($footerRow + 5))->getFont()->setBold(true)->setUnderline(true);

                $sheet->freezePane("A7");
            },
        ];
    }
}