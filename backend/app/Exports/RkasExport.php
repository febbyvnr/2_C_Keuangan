<?php

namespace App\Exports;

use App\Models\MstProgramKerja;
use App\Models\FpdAnggaran;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Auth;

class RkasExport implements WithEvents
{
    protected $role;
    protected $filters;

    public function __construct($filters = [], $role = null)
    {
        $this->filters = $filters;
        $this->role = $role ?? (Auth::user()->role ?? 'Bendahara');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;

                // =====================
                // TITLE
                // =====================
                $sheet->setCellValue('A1', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A2', 'LAPORAN RKAS (RENCANA KERJA DAN ANGGARAN SEKOLAH)');
                $sheet->setCellValue('A3', 'Berdasarkan RKA yang Disetujui');

                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('A3:H3');

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(17);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('A3')->getFont()->setSize(11)->setItalic(true);

                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(26);
                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(22);

                // =====================
                // FILTER INFO
                // =====================
                $filterRow = 5;
                $filterText = '';
                if (!empty($this->filters['ID_TA_ANGGARAN'])) {
                    $filterText .= 'Tahun: ' . $this->filters['ID_TA_ANGGARAN'];
                }
                
                if (!empty($this->filters['ID_REF_DANA'])) {
                    if (!empty($this->filters['ID_TA_ANGGARAN'])) {
                        $filterText .= ' | ';
                    }
                    $filterText .= 'Sumber Dana: ' . $this->filters['ID_REF_DANA'];
                }

                if (!empty($filterText)) {
                    $sheet->setCellValue("A$filterRow", $filterText);
                    $sheet->getStyle("A$filterRow")->getFont()->setItalic(true)->setSize(10);
                    $sheet->mergeCells("A$filterRow:H$filterRow");
                }

                // =====================
                // HEADER TABLE
                // =====================
                $headerRow = 7;
                $headers = [
                    'A' => 'NO', 'B' => 'TAHUN ANGGARAN', 'C' => 'UNIT', 'D' => 'PROGRAM KERJA',
                    'E' => 'KEGIATAN', 'F' => 'SUMBER DANA', 'G' => 'ANGGARAN (Rp)', 'H' => 'KET',
                ];

                foreach ($headers as $col => $header) {
                    $sheet->setCellValue("$col$headerRow", $header);
                }

                $headerStyle = $sheet->getStyle("A$headerRow:H$headerRow");
                $headerStyle->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $headerStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E75B6');

                $sheet->setAutoFilter("A$headerRow:H$headerRow");

                // Column width
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(22);
                $sheet->getColumnDimension('F')->setWidth(22);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(12);

                // =====================
                // DATA
                // =====================
                $data = $this->getData();
                $startData = $headerRow + 1;
                $row = $startData;
                $no = 1;
                $totalAnggaran = 0;

                foreach ($data as $item) {
                    $sheet->setCellValue("A$row", $no);
                    $sheet->setCellValue("B$row", $item['tahun_anggaran']);
                    $sheet->setCellValue("C$row", $item['unit']);
                    $sheet->setCellValue("D$row", $item['program_kerja']);
                    $sheet->setCellValue("E$row", $item['kegiatan']);
                    $sheet->setCellValue("F$row", $item['sumber_dana']);
                    $sheet->setCellValue("G$row", $item['anggaran_disetujui']);
                    $sheet->setCellValue("H$row", $item['keterangan'] ?? '');

                    $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("G$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("G$row")->getNumberFormat()->setFormatCode('#,##0');

                    $totalAnggaran += $item['anggaran_disetujui'];
                    $row++; $no++;
                }

                $endData = $row - 1;
                if ($endData >= $startData) {
                    $sheet->getStyle("A$headerRow:H$endData")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }

                // =====================
                // TOTAL ROW
                // =====================
                $totalRow = $endData + 2;
                $sheet->setCellValue("F$totalRow", 'TOTAL ANGGARAN');
                $sheet->setCellValue("G$totalRow", $totalAnggaran);
                $sheet->getStyle("F$totalRow:G$totalRow")->getFont()->setBold(true);
                $sheet->getStyle("G$totalRow")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("F$totalRow:G$totalRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE699');

                // =====================
                // FOOTER
                // =====================
                $footerRow = $totalRow + 3;
                $sheet->setCellValue("H$footerRow", 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("H$footerRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->setCellValue("H" . ($footerRow + 1), 'By: ' . $this->role);
                $sheet->getStyle("H" . ($footerRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // =====================
                // PDF SETUP (Agar tidak terpotong)
                // =====================
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->freezePane("A8");
            },
        ];
    }

    private function getData(): array
    {
        $query = MstProgramKerja::query()
            ->with(['tahunAnggaran', 'unit', 'kegiatan', 'detailProgramKerja.sumberDana'])
            ->where('IS_DELETE', 0);

        if (!empty($this->filters['ID_TA_ANGGARAN'])) {
            $query->where('ID_TA_ANGGARAN', $this->filters['ID_TA_ANGGARAN']);
        }

        $programKerjaList = $query->get();
        $data = [];

        foreach ($programKerjaList as $programKerja) {
            $fpd = FpdAnggaran::where('ID_PROGRAM_KERJA', $programKerja->ID_PROGRAM_KERJA)->first();

            if ($fpd) {
                $details = $programKerja->detailProgramKerja;

                if ($details->isEmpty()) {
                    $data[] = [
                        'tahun_anggaran' => optional($programKerja->tahunAnggaran)->DESKRIPSI_TAHUN_ANGGARAN ?? '-',
                        'unit' => optional($programKerja->unit)->NAMA_UNIT ?? optional($programKerja->unit)->DESKRIPSI_UNIT ?? '-',
                        'program_kerja' => $programKerja->PROGRAM_KERJA,
                        'kegiatan' => optional($programKerja->kegiatan)->DESKRIPSI_KEGIATAN ?? '-',
                        'sumber_dana' => '-',
                        'anggaran_disetujui' => $fpd->NOMINAL_FPD ?? 0,
                        'keterangan' => 'FPD',
                    ];
                } else {
                    foreach ($details as $detail) {
                        if (!empty($this->filters['ID_REF_DANA']) && $detail->ID_REF_DANA != $this->filters['ID_REF_DANA']) {
                            continue;
                        }

                        $sumberDanaName = '-';
                        if ($detail->sumberDana) {
                            $sumberDanaName = $detail->sumberDana->NAMA_SUMBER_DANA ?? 
                                             $detail->sumberDana->DESKRIPSI_SUMBER_DANA ?? 
                                             'Dana ID: ' . $detail->sumberDana->ID_REF_DANA;
                        }

                        $data[] = [
                            'tahun_anggaran' => optional($programKerja->tahunAnggaran)->DESKRIPSI_TAHUN_ANGGARAN ?? '-',
                            'unit' => optional($programKerja->unit)->NAMA_UNIT ?? optional($programKerja->unit)->DESKRIPSI_UNIT ?? '-',
                            'program_kerja' => $programKerja->PROGRAM_KERJA,
                            'kegiatan' => optional($programKerja->kegiatan)->DESKRIPSI_KEGIATAN ?? '-',
                            'sumber_dana' => $sumberDanaName,
                            'anggaran_disetujui' => $detail->NOMINAL ?? 0,
                            'keterangan' => '',
                        ];
                    }
                }
            }
        }
        return $data;
    }
}