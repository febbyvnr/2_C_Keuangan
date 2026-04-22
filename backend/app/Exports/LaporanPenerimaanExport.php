<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPenerimaanExport implements WithEvents
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
        return DB::table('tr_penerimaan as p') 
            ->join('ref_penerimaan as rp', 'p.ID_REF_PENERIMAAN', '=', 'rp.ID_REF_PENERIMAAN')
            ->select(
                'p.TANGGAL_TR_PENERIMAAN as tanggal',
                'rp.DESKRIPSI_REF_PENERIMAAN as jenis',
                'p.DESKRIPSI_TR_PENERIMAAN as uraian',
                'p.JUMLAH_TR_PENERIMAAN as jumlah'
            )
            ->when($this->start && $this->end, function ($q) {
                $q->whereBetween('p.TANGGAL_TR_PENERIMAAN', [$this->start, $this->end]);
            })
            ->when($this->sumberDana, function ($q) {
                $q->where('p.ID_REF_DANA', $this->sumberDana);
            })
            ->orderBy('p.TANGGAL_TR_PENERIMAAN')
            ->get();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet;

                // =====================
                // WIDTH
                // =====================
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(22);

                // =====================
                // TITLE
                // =====================
                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN PENERIMAAN (KM)');
                $sheet->setCellValue('A4', 'Periode: ' . ($this->start ?? 'AWAL') . ' s/d ' . ($this->end ?? 'AKHIR'));

                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                $sheet->mergeCells('A4:E4');

                $sheet->getStyle('A2:E4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);

                // =====================
                // HEADER
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

                $sheet->getStyle("A$headerRow:E$headerRow")->getFont()
                    ->getColor()->setARGB('FFFFFFFF');

                $sheet->setAutoFilter("A$headerRow:E$headerRow");

                // =====================
                // DATA
                // =====================
                $row = $headerRow + 1;
                $no = 1;
                $data = $this->collection();

                foreach ($data as $item) {
                    $sheet->setCellValue("A$row", $no++);
                    $sheet->setCellValue("B$row", $item->tanggal);
                    $sheet->setCellValue("C$row", $item->jenis);
                    $sheet->setCellValue("D$row", $item->uraian);
                    $sheet->setCellValue("E$row", $item->jumlah);
                    $row++;
                }

                $endData = $row - 1;

                // ALIGN + FORMAT
                $sheet->getStyle("A$headerRow:E$endData")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("C" . ($headerRow+1) . ":D$endData")
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle("E" . ($headerRow+1) . ":E$endData")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');

                // BORDER
                $sheet->getStyle("A$headerRow:E$endData")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // =====================
                // TOTAL
                // =====================
                $total = $data->sum('jumlah');
                $totalRow = $endData + 2;

                $sheet->setCellValue("D$totalRow", 'TOTAL PENERIMAAN');
                $sheet->setCellValue("E$totalRow", $total);

                $sheet->getStyle("D$totalRow:E$totalRow")->getFont()->setBold(true);

                $sheet->getStyle("E$totalRow")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');

                $sheet->getStyle("D$totalRow:E$totalRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFE699');

                // =====================
                // FOOTER
                // =====================
                $footerRow = $totalRow + 4;


            // =====================
            // TANGGAL
            // =====================
            $sheet->setCellValue("E" . ($footerRow+2), 'Yogyakarta, ' . date('d F Y'));

            $sheet->getStyle("E" . ($footerRow+2))
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);


            // =====================
            // BY ROLE
            // =====================
            $sheet->setCellValue("E" . ($footerRow+3), 'By: ' . ($this->role = Auth::user()->role ?? 'Bendahara'));    

            $sheet->getStyle("E" . ($footerRow+3))
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->freezePane("A7");
            },
        ];
    }
}