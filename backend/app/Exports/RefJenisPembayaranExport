<?php

namespace App\Exports;

use App\Models\RefJenisPembayaran;
use Illuminate\Support\Collection;
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
    WithEvents
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
            $item->deskripsi_jenis_pembayaran,
        ];
    }

    public function headings(): array
    {
        return [
            'ID Jenis Pembayaran',
            'Deskripsi Jenis Pembayaran',
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

                // FREEZE HEADER
                $sheet->freezePane('A2');

                // ZEBRA STRIPING (soft background sesuai design system)
                $highestRow = $sheet->getHighestRow();

                for ($i = 2; $i <= $highestRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle("A{$i}:B{$i}")
                            ->getFill()
                            ->setFillType('solid')
                            ->getStartColor()
                            ->setRGB('F6F7F9'); // background-default dari design system
                    }
                }
            },
        ];
    }
}
