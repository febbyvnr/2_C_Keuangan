<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanPenerimaanExport implements FromArray, WithEvents
{
    protected $data;
    protected $periode;
    protected $saldoAkhir;
    protected $tanggalCetak;

    public function __construct(Collection $data, $periode, $saldoAkhir, $tanggalCetak)
    {
        $this->data = $data;
        $this->periode = $periode;
        $this->saldoAkhir = $saldoAkhir;
        $this->tanggalCetak = $tanggalCetak;
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['', 'SMK BOPKRI 2 YOGYAKARTA'];
        $rows[] = ['', 'LAPORAN PENERIMAAN'];
        $rows[] = ['', 'Periode ' . $this->periode];
        $rows[] = ['', '', '', '', '', '', ''];

        $rows[] = ['', 'NO', 'TANGGAL', 'URAIAN', 'DEBIT', 'KREDIT', 'SALDO'];

        foreach ($this->data as $item) {
            $rows[] = [
                '',
                $item->no,
                $item->tanggal ? date('Y-m-d H:i:s', strtotime($item->tanggal)) : '-',
                $item->uraian,
                $item->debit,
                $item->kredit,
                $item->saldo,
            ];
        }

        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', 'SALDO AKHIR', '', $this->saldoAkhir];
        $rows[] = ['', '', '', '', '', '', ''];

        // tanda tangan
        $rows[] = ['', '', '', 'Bendahara,', '', '', ''];
        $rows[] = ['', '', '', 'Rina Putri, S.E.', '', '', ''];

        // ruang kosong besar untuk tanda tangan (3 row kosong)
        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];

        $rows[] = ['', '', '', '-------------------------', '', '', ''];
        $rows[] = ['', '', '', 'NIP: 19800101', '', '', ''];

        // tanggal cetak
        $rows[] = ['', '', '', '', '', 'Yogyakarta, ' . $this->tanggalCetak, ''];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $jumlahData = count($this->data);
                $headerRow = 6;
                $startDataRow = 7;
                $endDataRow = $startDataRow + $jumlahData - 1;
                $saldoRow = $endDataRow + 2;

                $ttdJabatanRow = $saldoRow + 2;   // Bendahara,
                $ttdNamaRow = $saldoRow + 3;      // Nama
                $spasi1Row = $saldoRow + 4;       // kosong
                $spasi2Row = $saldoRow + 5;       // kosong
                $spasi3Row = $saldoRow + 6;       // kosong
                $ttdGarisRow = $saldoRow + 7;     // garis
                $ttdNipRow = $saldoRow + 8;       // NIP
                $tanggalRow = $saldoRow + 9;      // tanggal

                $sheet->mergeCells('B2:G2');
                $sheet->mergeCells('B3:G3');
                $sheet->mergeCells('B4:G4');

                $sheet->getStyle('B2:G4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B2:G4')->getFont()->setBold(true);
                $sheet->getStyle('B2')->getFont()->setSize(16);
                $sheet->getStyle('B3')->getFont()->setSize(14);
                $sheet->getStyle('B4')->getFont()->setSize(11);

                $sheet->getStyle("B{$headerRow}:G{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2F75B5'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->freezePane('B7');

                if ($jumlahData > 0) {
                    $sheet->getStyle("B{$startDataRow}:G{$endDataRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                    $sheet->getStyle("B{$startDataRow}:C{$endDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("E{$startDataRow}:G{$endDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle("E{$startDataRow}:G{$endDataRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                $sheet->getStyle("E{$saldoRow}:G{$saldoRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFE699'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle("G{$saldoRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->getStyle("E{$saldoRow}:G{$saldoRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("E{$saldoRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getColumnDimension('A')->setWidth(3);
                $sheet->getColumnDimension('B')->setWidth(6);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(40);
                $sheet->getColumnDimension('E')->setWidth(16);
                $sheet->getColumnDimension('F')->setWidth(16);
                $sheet->getColumnDimension('G')->setWidth(16);

                // posisi tanda tangan di tengah
                $sheet->mergeCells("D{$ttdJabatanRow}:E{$ttdJabatanRow}");
                $sheet->mergeCells("D{$ttdNamaRow}:E{$ttdNamaRow}");
                $sheet->mergeCells("D{$ttdGarisRow}:E{$ttdGarisRow}");
                $sheet->mergeCells("D{$ttdNipRow}:E{$ttdNipRow}");

                $sheet->getStyle("D{$ttdJabatanRow}:E{$ttdNipRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("D{$ttdNamaRow}")
                    ->getFont()
                    ->setBold(true);

                // bikin ruang tanda tangan beneran luas
                $sheet->getRowDimension($spasi1Row)->setRowHeight(18);
                $sheet->getRowDimension($spasi2Row)->setRowHeight(18);
                $sheet->getRowDimension($spasi3Row)->setRowHeight(18);

                // tanggal cetak jangan terlalu kanan
                $sheet->mergeCells("F{$tanggalRow}:G{$tanggalRow}");
                $sheet->getStyle("F{$tanggalRow}:G{$tanggalRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}