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
    protected $start, $end, $role, $nip, $nama_ttd, $nip_ttd;

    public function __construct($start, $end, $dummy = null, $role = null, $nip = null, $nama_ttd = null, $nip_ttd = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->role = $role ?: 'Bendahara';
        $this->nip = $nip;
        $this->nama_ttd = $nama_ttd; // Nama bendahara asli dari database
        $this->nip_ttd = $nip_ttd;   // NIP bendahara asli dari database
    }

    public function collection()
    {
        return DB::table('tr_pm as p')
            ->join('mst_program_kerja as mst', 'p.ID_PROGRAM_KERJA', '=', 'mst.ID_PROGRAM_KERJA')
            ->join('dtl_program_kerja as dtl', 'p.ID_PROGRAM_KERJA', '=', 'dtl.ID_PROGRAM_KERJA')
            ->select(
                'p.TGL_PM as tanggal',
                'mst.PROGRAM_KERJA as program',
                'mst.INDIKATOR as indikator',
                'p.DESKRIPSI_TR_PM as uraian',
                'dtl.NOMINAL as nominal'
            )
            ->where('mst.IS_DELETE', 0)
            ->when($this->start && $this->end, function ($q) {
                $q->whereBetween('p.TGL_PM', [$this->start, $this->end]);
            })
            ->orderBy('p.TGL_PM')
            ->get();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;

                // 1. WIDTH & COLUMN DIMENSIONS
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(35);
                $sheet->getColumnDimension('F')->setWidth(22);

                // 2. TITLE SECTION
                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN PENGELUARAN (KK)');
                $sheet->setCellValue('A4', 'Periode: ' . ($this->start ?? 'AWAL') . ' s/d ' . ($this->end ?? 'AKHIR'));

                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');
                
                $sheet->getStyle('A2:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);

                // 3. HEADER TABLE
                $headerRow = 6;
                $sheet->setCellValue("A$headerRow", 'NO');
                $sheet->setCellValue("B$headerRow", 'TANGGAL');
                $sheet->setCellValue("C$headerRow", 'PROGRAM KERJA');
                $sheet->setCellValue("D$headerRow", 'INDIKATOR');
                $sheet->setCellValue("E$headerRow", 'URAIAN');
                $sheet->setCellValue("F$headerRow", 'NOMINAL');

                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->setBold(true);
                $sheet->getStyle("A$headerRow:F$headerRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A$headerRow:F$headerRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E75B6');
                $sheet->getStyle("A$headerRow:F$headerRow")->getFont()->getColor()->setARGB('FFFFFFFF');

                // 4. DATA LOOPING
                $row = $headerRow + 1;
                $no = 1;
                $data = $this->collection();

                foreach ($data as $item) {
                    $sheet->setCellValue("A$row", $no++);
                    $sheet->setCellValue("B$row", $item->tanggal);
                    $sheet->setCellValue("C$row", $item->program);
                    $sheet->setCellValue("D$row", $item->indikator);
                    $sheet->setCellValue("E$row", $item->uraian);
                    $sheet->setCellValue("F$row", $item->nominal);
                    $row++;
                }

                $endData = $row - 1;

                // 5. STYLING DATA (Alignment, Format, Borders)
                $sheet->getStyle("A$headerRow:F$endData")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C" . ($headerRow+1) . ":E$endData")->getAlignment()->setWrapText(true);
                $sheet->getStyle("F" . ($headerRow+1) . ":F$endData")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("A$headerRow:F$endData")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // 6. TOTAL SECTION
                $totalRow = $endData + 2;
                $sheet->setCellValue("E$totalRow", 'TOTAL PENGELUARAN');
                $sheet->setCellValue("F$totalRow", $data->sum('nominal'));
                
                $sheet->getStyle("E$totalRow:F$totalRow")->getFont()->setBold(true);
                $sheet->getStyle("F$totalRow")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("E$totalRow:F$totalRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE699');

                $footerRow = $totalRow + 4;

                $label_ttd = 'Bendahara,';
                $nama_ttd = $this->nama_ttd ?: '-'; 
                $nip_ttd = $this->nip_ttd ?: '-'; 

                $sheet->mergeCells("B" . ($footerRow+1) . ":D" . ($footerRow+1));
                $sheet->mergeCells("B" . ($footerRow+3) . ":D" . ($footerRow+3));
                $sheet->mergeCells("B" . ($footerRow+7) . ":D" . ($footerRow+7));
                $sheet->mergeCells("B" . ($footerRow+8) . ":D" . ($footerRow+8));

                $sheet->setCellValue("B" . ($footerRow+1), $label_ttd);
                $sheet->setCellValue("B" . ($footerRow+3), $nama_ttd); 
                $sheet->setCellValue("B" . ($footerRow+7), '-------------------------');
                $sheet->setCellValue("B" . ($footerRow+8), 'NIP: ' . $nip_ttd); 

                $sheet->getStyle("B" . ($footerRow+1) . ":D" . ($footerRow+8))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 8. DATE & FINALIZATION
                $sheet->setCellValue("F" . ($footerRow+10), 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("F" . ($footerRow+10))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                $sheet->freezePane("A7");
            },
        ];
    }
}