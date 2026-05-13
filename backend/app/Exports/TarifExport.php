<?php

namespace App\Exports;

use App\Models\RefTarif;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\Auth;

class TarifExport implements WithEvents
{
    protected $role;
    protected $idJenisTarif;
    protected $idTaAnggaran;

    public function __construct($role = null, $idJenisTarif = null, $idTaAnggaran = null)
    {
        $this->role         = $role ?? (Auth::user()->role ?? 'Bendahara');
        $this->idJenisTarif = $idJenisTarif;
        $this->idTaAnggaran = $idTaAnggaran;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;

                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'DATA TARIF');

                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(17);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('A4')->getFont()->setSize(11);

                foreach (['A2', 'A3', 'A4'] as $cell) {
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $sheet->getRowDimension(2)->setRowHeight(26);
                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension(4)->setRowHeight(22);

                $headerRow = 6;
                $headers = [
                    'A' => 'NO',
                    'B' => 'JENIS TARIF',
                    'C' => 'TAHUN ANGGARAN',
                    'D' => 'DESKRIPSI TARIF',
                    'E' => 'NOMINAL',
                    'F' => 'TANGGAL PENETAPAN',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}{$headerRow}", $label);
                }

                $sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF2E75B6'],
                    ],
                ]);

                $sheet->setAutoFilter("A{$headerRow}:F{$headerRow}");

                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(22);

                $query = RefTarif::with(['jenisTarif', 'tahunAnggaran'])
                    ->orderBy('TGL_PENETAPAN', 'desc');

                // Filter opsional jika parameter dikirim
                if ($this->idJenisTarif) {
                    $query->where('ID_JENIS_TARIF', $this->idJenisTarif);
                }
                if ($this->idTaAnggaran) {
                    $query->where('ID_TA_ANGGARAN', $this->idTaAnggaran);
                }

                $data      = $query->get();
                $startData = $headerRow + 1;
                $row       = $startData;
                $no        = 1;

                foreach ($data as $item) {
        
                    $jenisTarif    = $item->jenisTarif?->DESKRIPSI_JENIS_TARIF
                        ?? ($item->ID_JENIS_TARIF ? "ID: {$item->ID_JENIS_TARIF}" : '-');

                    $tahunAnggaran = $item->tahunAnggaran?->DESKRIPSI_TAHUN_ANGGARAN
                        ?? ($item->ID_TA_ANGGARAN ? "ID: {$item->ID_TA_ANGGARAN}" : '-');

                    $deskripsi     = $item->DESKRIPSI_TARIF ?? '-';
                    $nominal       = $item->NOMINAL ?? 0;

                    $tglPenetapan  = '-';
                    if (!empty($item->TGL_PENETAPAN)) {
                        try {
                            $tglPenetapan = \Carbon\Carbon::parse($item->TGL_PENETAPAN)
                                ->translatedFormat('d F Y');
                        } catch (\Exception $e) {
                            $tglPenetapan = $item->TGL_PENETAPAN;
                        }
                    }

                    $sheet->setCellValue("A{$row}", $no);
                    $sheet->setCellValue("B{$row}", $jenisTarif);
                    $sheet->setCellValue("C{$row}", $tahunAnggaran);
                    $sheet->setCellValue("D{$row}", $deskripsi);
                    $sheet->setCellValue("E{$row}", $nominal);
                    $sheet->setCellValue("F{$row}", $tglPenetapan);

                    // Nomor urut di tengah
                    $sheet->getStyle("A{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Format nominal sebagai currency Rupiah
                    $sheet->getStyle("E{$row}")->getNumberFormat()
                        ->setFormatCode('"Rp "#,##0');

                    // Nominal rata kanan
                    $sheet->getStyle("E{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Tahun Anggaran & Tanggal rata tengah
                    $sheet->getStyle("C{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $row++;
                    $no++;
                }

                $endData = $row - 1;

                foreach (['B', 'D'] as $col) {
                    $sheet->getStyle("{$col}{$startData}:{$col}{$endData}")
                        ->getAlignment()->setWrapText(true);
                }

                $sheet->getStyle("A{$headerRow}:F{$endData}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                for ($i = $startData; $i <= $endData; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(-1);
                    if ($i % 2 === 0) {
                        $sheet->getStyle("A{$i}:F{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF2F2F2');
                    }
                }

                $footerRow = $endData + 3;

                $sheet->setCellValue("F{$footerRow}", 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("F{$footerRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->setCellValue("F" . ($footerRow + 1), 'By: ' . $this->role);
                $sheet->getStyle("F" . ($footerRow + 1))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

               
                $sheet->freezePane("A7");
            },
        ];
    }
}