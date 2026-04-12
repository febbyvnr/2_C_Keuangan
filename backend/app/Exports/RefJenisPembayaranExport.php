<?php

namespace App\Exports;

use App\Models\RefJenisPembayaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithEvents
};

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RefJenisPembayaranExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    protected Collection $data;

    public function collection(): Collection
    {
        $this->data = RefJenisPembayaran::query()
            ->orderBy('ID_JENIS_PEMBAYARAN', 'asc')
            ->get();

        return $this->data;
    }

    public function map($item): array
    {
        return [
            $item->ID_JENIS_PEMBAYARAN,
            $item->deskripsi_jenis_pembayaran 
                ?? $item->DESKRIPSI_JENIS_PEMBAYARAN 
                ?? '-',
        ];
    }

    public function startCell(): string
    {
        return 'A5'; // data mulai dari baris 5
    }
    public function headings(): array
    {
        return [
            ['SMK BOPKRI 2 YOGYAKARTA'], // baris 1
            ['Referensi Jenis Pembayaran'], // baris 2
            [], // spasi
            ['Kode', 'Keterangan'], // header tabel
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 60,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // HEADER SESUAI DESIGN SYSTEM (brand-primary #265F9C)
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '265F9C'], // dari design system kamu
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        // BORDER
        $sheet->getStyle('A1:B' . $sheet->getHighestRow())
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle('thin');

        // WRAP TEXT DESKRIPSI
        $sheet->getStyle('B')->getAlignment()->setWrapText(true);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // === MERGE TITLE ===
                $sheet->mergeCells('A1:B1');
                $sheet->mergeCells('A2:B2');

                // === STYLE TITLE ===
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                // === HEADER TABLE (BARIS 4) ===
                $sheet->getStyle('A4:B4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '265F9C'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                // === BORDER ===
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle("A4:B{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');

                // === ZEBRA ===
                for ($i = 5; $i <= $highestRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle("A{$i}:B{$i}")
                            ->getFill()
                            ->setFillType('solid')
                            ->getStartColor()
                            ->setRGB('F6F7F9');
                    }
                }

                // === FREEZE ===
                $sheet->freezePane('A5');

                // === WRAP TEXT ===
                $sheet->getStyle('B')->getAlignment()->setWrapText(true);
            },
        ];
    }
}