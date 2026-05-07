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
    protected $role = 'Bendahara';
    protected $nip;

    protected $nama;
    protected $nip_ttd;

   public function __construct($start, $end, $sumberDana, $role = null, $nip = null, $nama = null, $nip_ttd = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->sumberDana = $sumberDana;
        $this->role = $role ?: 'Bendahara';
        $this->nip = $nip;
        $this->nama = $nama;
        $this->nip_ttd = $nip_ttd;
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
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(22);

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

                $headerRow = 6;

                $sheet->setCellValue("A$headerRow", 'NO');
                $sheet->setCellValue("B$headerRow", 'TANGGAL');
                $sheet->setCellValue("C$headerRow", 'KATEGORI');
                $sheet->setCellValue("D$headerRow", 'KETERANGAN');
                $sheet->setCellValue("E$headerRow", 'NOMINAL');

                $sheet->getStyle("A$headerRow:E$headerRow")->getFont()->setBold(true);
                $sheet->getStyle("A$headerRow:E$headerRow")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A$headerRow:E$headerRow")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF2E75B6');

                $sheet->getStyle("A$headerRow:E$headerRow")->getFont()
                    ->getColor()->setARGB('FFFFFFFF');

                $sheet->setAutoFilter("A$headerRow:E$headerRow");

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

                $sheet->getStyle("A$headerRow:E$endData")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("D" . ($headerRow+1) . ":D$endData")
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle("E" . ($headerRow+1) . ":E$endData")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');

                $total = $data->sum('jumlah');
                $totalRow = $endData + 2;

                $sheet->setCellValue("D$totalRow", 'TOTAL');
                $sheet->setCellValue("E$totalRow", $total);

                $sheet->getStyle("D$totalRow:E$totalRow")->getFont()->setBold(true);

                $sheet->getStyle("E$totalRow")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');

                $nama = $this->nama ?? '-';
                $nip = $this->nip_ttd ?? '-';

                $roleLabel = 'Bendahara';

                // =====================
                // FOOTER
                // =====================
                $footerRow = $totalRow + 4;

                $sheet->mergeCells("B" . ($footerRow+1) . ":D" . ($footerRow+1));
                $sheet->mergeCells("B" . ($footerRow+3) . ":D" . ($footerRow+3));
                $sheet->mergeCells("B" . ($footerRow+7) . ":D" . ($footerRow+7));
                $sheet->mergeCells("B" . ($footerRow+8) . ":D" . ($footerRow+8));

                $sheet->setCellValue("B" . ($footerRow+1), $roleLabel . ',');
                $sheet->setCellValue("B" . ($footerRow+3), $nama);
                $sheet->setCellValue("B" . ($footerRow+7), '-------------------------');
                $sheet->setCellValue("B" . ($footerRow+8), 'NIP: ' . $nip);

                $sheet->getStyle("B" . ($footerRow+1) . ":D" . ($footerRow+8))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue("E" . ($footerRow+10), 'Yogyakarta, ' . date('d F Y'));

                $sheet->freezePane("A7");
            },
        ];
    }
}