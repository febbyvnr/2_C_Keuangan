<?php

namespace App\Exports;

use App\Models\Rka;

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

    public function __construct($role = null, $nip = null, $nama = null)
    {
        $this->role = $role ?? (Auth::user()->role ?? 'Bendahara');
        $this->nip = $nip ?? (Auth::user()->nip ?? '-');
        $this->nama = $nama ?? (Auth::user()->name ?? '-');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'LAPORAN RENCANA KEGIATAN DAN ANGGARAN (RKA)');
                $sheet->getStyle('A1')->getFont()
                    ->setBold(true)
                    ->setSize(16);
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $headerRow = 3;
                $headers = [
                    'A' => 'NO',
                    'B' => 'PROGRAM KERJA',
                    'C' => 'INDIKATOR',
                    'D' => 'SUMBER DANA',
                    'E' => 'QTY',
                    'F' => 'VOLUME',
                    'G' => 'SATUAN',
                    'H' => 'HARGA SATUAN',
                    'I' => 'NOMINAL',
                ];
                foreach ($headers as $col => $text) {
                    $sheet->setCellValue($col . $headerRow, $text);
                }
                $sheet->getStyle("A{$headerRow}:I{$headerRow}")
                    ->getFont()
                    ->setBold(true);
                $sheet->getStyle("A{$headerRow}:I{$headerRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$headerRow}:I{$headerRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$headerRow}:I{$headerRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFD9D9D9');
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(10);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('H')->setWidth(18);
                $sheet->getColumnDimension('I')->setWidth(18);
                $data = $this->getData();
                $row = 4;
                $no = 1;
                $grandTotal = 0;
                foreach ($data as $item) {
                    $sheet->setCellValue("A{$row}", $no);
                    $sheet->setCellValue("B{$row}", $item['program_kerja']);
                    $sheet->setCellValue("C{$row}", $item['indikator']);
                    $sheet->setCellValue("D{$row}", $item['sumber_dana']);
                    $sheet->setCellValue("E{$row}", $item['qty']);
                    $sheet->setCellValue("F{$row}", $item['volume']);
                    $sheet->setCellValue("G{$row}", $item['satuan']);
                    $sheet->setCellValue("H{$row}", $item['harga_satuan']);
                    $sheet->setCellValue("I{$row}", $item['nominal']);
                    $sheet->getStyle("A{$row}:I{$row}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_TOP);
                    $sheet->getStyle("A{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$row}:G{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H{$row}:I{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("H{$row}:I{$row}")
                        ->getNumberFormat()
                        ->setFormatCode('"Rp" #,##0');
                    $grandTotal += $item['nominal'];
                    $row++;
                    $no++;
                }
                $sheet->mergeCells("A{$row}:H{$row}");
                $sheet->setCellValue("A{$row}", 'TOTAL');
                $sheet->setCellValue("I{$row}", $grandTotal);
                $sheet->getStyle("A{$row}:I{$row}")
                    ->getFont()
                    ->setBold(true);
                $sheet->getStyle("I{$row}")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("A{$row}:I{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFFE699');
                $sheet->getStyle("A3:I{$row}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $footerRow = $row + 3;
                $sheet->mergeCells("G{$footerRow}:I{$footerRow}");
                $sheet->setCellValue(
                    "G{$footerRow}",
                    'Yogyakarta, ' . date('d F Y')
                );
                $sheet->getStyle("G{$footerRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $footerRow++;
                $sheet->setCellValue(
                    "I{$footerRow}",
                    ucwords(strtolower($this->role))
                );
                $sheet->getStyle("I{$footerRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $footerRow += 4;
                $sheet->setCellValue("I{$footerRow}", $this->nama);
                $sheet->getStyle("I{$footerRow}")
                    ->getFont()
                    ->setBold(true);
                $sheet->getStyle("I{$footerRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $footerRow++;
                $sheet->setCellValue(
                    "I{$footerRow}",
                    'NIP: ' . $this->nip
                );
                $sheet->getStyle("I{$footerRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->freezePane('A4');
                $sheet->getPageSetup()
                    ->setOrientation(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                    );
                $sheet->getPageSetup()
                    ->setPaperSize(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                    );
            },
        ];
    }

    public function getData(): array
    {
        $rkaList = Rka::with(['rkt', 'refDana'])
            ->whereHas('rkt', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('IS_DELETE', '!=', 1)
                        ->orWhereNull('IS_DELETE');
                });
                $q->whereNotNull('NIP_VALIDATOR_PROGKER');
            })
            ->get();
        $data = [];
        foreach ($rkaList as $item) {
            $data[] = [
                'program_kerja' => $item->rkt->PROGRAM_KERJA ?? '-',
                'indikator' => $item->rkt->INDIKATOR ?? '-',
                'sumber_dana' => $item->refDana->DESKRIPSI_SUMBER_DANA ?? '-',
                'qty' => $item->QTY ?? 0,
                'volume' => $item->VOLUME ?? 0,
                'satuan' => $item->SATUAN ?? '-',
                'harga_satuan' => $item->HARGA_SATUAN ?? 0,
                'nominal' => $item->NOMINAL ?? 0,
            ];
        }
        return $data;
    }
}