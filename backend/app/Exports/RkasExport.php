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
    protected $nip;
    protected $nama;
    protected $filters;
    protected $filterText;

    public function __construct($filters = [], $role = null, $nip = null, $nama = null, $filterText = null)
    {
        $this->filters = $filters;
        $this->role = $role ?? (Auth::user()->role ?? 'Bendahara');
        $this->nip = $nip ?? (Auth::user()->nip ?? null);
        $this->nama = $nama ?? '-';
        $this->filterText = $filterText ?? 'Semua Data';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;

                // Set column widths to match PDF proportions
                $sheet->getColumnDimension('A')->setWidth(6);        // Wider for NO
                $sheet->getColumnDimension('B')->setWidth(22);       // Wider for Tahun Anggaran
                $sheet->getColumnDimension('C')->setWidth(10.8);     // 12%
                $sheet->getColumnDimension('D')->setWidth(20.7);     // 23%
                $sheet->getColumnDimension('E')->setWidth(18);       // 20%
                $sheet->getColumnDimension('F')->setWidth(18);       // Wider for TOTAL ANGGARAN
                $sheet->getColumnDimension('G')->setWidth(16);       // Increased for Rp format
                $sheet->getColumnDimension('H')->setWidth(9);        // 10%

                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN RKAS (RENCANA KERJA DAN ANGGARAN SEKOLAH)');
                $sheet->setCellValue('A4', 'Berdasarkan RKA yang Disetujui');

                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('A3:H3');
                $sheet->mergeCells('A4:H4');

                $sheet->getStyle('A2:H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(11);

                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(22);
                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getDefaultRowDimension()->setRowHeight(-1);

                $filterRow = 5;
                $filterText = $this->filterText ?? 'Semua Data';

                if (!empty($filterText)) {
                    $sheet->setCellValue("A$filterRow", $filterText);
                    $sheet->mergeCells("A$filterRow:H$filterRow");
                    $sheet->getStyle("A$filterRow")->getFont()->setItalic(true)->setSize(10);
                }

                $headerRow = 7;
                $headers = [
                    'A' => 'NO',
                    'B' => 'TAHUN ANGGARAN',
                    'C' => 'UNIT',
                    'D' => 'PROGRAM KERJA',
                    'E' => 'KEGIATAN',
                    'F' => 'SUMBER DANA',
                    'G' => 'ANGGARAN',
                    'H' => 'KET',
                ];

                foreach ($headers as $col => $header) {
                    $sheet->setCellValue("$col$headerRow", $header);
                }

                $headerStyle = $sheet->getStyle("A$headerRow:H$headerRow");
                $headerStyle->getFont()->setBold(true)->getColor()->setARGB('FF000000');
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $headerStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $headerStyle->getAlignment()->setWrapText(true);
                $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');

                $sheet->getRowDimension($headerRow)->setRowHeight(-1);

                $sheet->setAutoFilter("A$headerRow:H$headerRow");

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
                    $sheet->getStyle("A$row")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                    $sheet->getStyle("B$row:H$row")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                    $sheet->getStyle("B$row:H$row")->getAlignment()->setWrapText(true);
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                    $sheet->getStyle("G$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("G$row")->getNumberFormat()->setFormatCode('"Rp" #,##0');

                    $totalAnggaran += $item['anggaran_disetujui'];
                    $row++; $no++;
                }

                $endData = $row - 1;
                if ($endData >= $startData) {
                    $sheet->getStyle("A$headerRow:H$endData")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle("A$headerRow:H$endData")->getFont()->getColor()->setARGB('FF000000');
                }

                $totalRow = $endData + 2;
                $sheet->setCellValue("F$totalRow", 'TOTAL ANGGARAN');
                $sheet->setCellValue("G$totalRow", $totalAnggaran);
                $sheet->getStyle("F$totalRow:G$totalRow")->getFont()->setBold(true);
                $sheet->getStyle("F$totalRow:G$totalRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE699');
                $sheet->getStyle("G$totalRow")->getNumberFormat()->setFormatCode('"Rp" #,##0');

                $footerRow = $totalRow + 3;
                $role = trim((string) ($this->role ?: 'Bendahara'));
                $nama = $this->nama ?: '-';
                $nip = $this->nip ?: '-';
                $roleLabel = ucwords(strtolower($role));

                // TTD section - right aligned at column H (Keterangan column)
                $ttdRow = $footerRow;
                
                // Date
                $sheet->mergeCells("F" . $ttdRow . ":H" . $ttdRow);
                $sheet->setCellValue("F" . $ttdRow, 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("F" . $ttdRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F" . $ttdRow)->getFont()->setSize(11);
                
                // Role/Jabatan (with spacing for vertical gap)
                $ttdRow += 1;  // Tight spacing from date (keeps date visible)
                $sheet->setCellValue("H" . $ttdRow, $roleLabel . ',');
                $sheet->getStyle("H" . $ttdRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("H" . $ttdRow)->getFont()->setSize(11);
                
                // Name (bold) - add wider spacing before name for signature space
                $ttdRow += 6;  // Wider space between jabatan and nama
                $sheet->setCellValue("H" . $ttdRow, $nama);
                $sheet->getStyle("H" . $ttdRow)->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle("H" . $ttdRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                // Signature line (dashed)
                $ttdRow += 1;
                $sheet->setCellValue("H" . $ttdRow, str_repeat('-', 20));
                $sheet->getStyle("H" . $ttdRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("H" . $ttdRow)->getFont()->setSize(10);
                
                // NIP
                $ttdRow += 1;
                $sheet->setCellValue("H" . $ttdRow, 'NIP: ' . $nip);
                $sheet->getStyle("H" . $ttdRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("H" . $ttdRow)->getFont()->setSize(11);

                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->freezePane("A8");
            },
        ];
    }

    public function getData(): array
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