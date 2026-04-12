<?php

namespace App\Exports;

use App\Models\RefJenisPembayaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithEvents,
    WithColumnWidths
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RefJenisPembayaranExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithEvents,
    WithColumnWidths
{
    protected int $no = 0;

    public function collection(): Collection
    {
        return RefJenisPembayaran::orderBy('ID_JENIS_PEMBAYARAN')->get();
    }

    public function map($item): array
    {
        return [
            ++$this->no,
            $item->ID_JENIS_PEMBAYARAN,
            $item->deskripsi_jenis_pembayaran 
                ?? $item->DESKRIPSI_JENIS_PEMBAYARAN 
                ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            ['No', 'ID', 'Deskripsi'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No (kecil)
            'B' => 10,  // ID (compact)
            'C' => 30,  // Deskripsi (cukup panjang)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // HEADER
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '2F5597'],
            ],
            'alignment' => [
                'horizontal' => 'center',
            ],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();

                // BORDER
                $sheet->getStyle("A1:C{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');

                // ZEBRA
                for ($i = 2; $i <= $highestRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle("A{$i}:C{$i}")
                            ->getFill()
                            ->setFillType('solid')
                            ->getStartColor()
                            ->setRGB('E7E6E6');
                    }
                }

                // ALIGNMENT
                $sheet->getStyle('A')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B')->getAlignment()->setHorizontal('center');

                // WRAP TEXT (biar deskripsi panjang gak keluar)
                $sheet->getStyle('C')->getAlignment()->setWrapText(true);

                // FREEZE HEADER
                $sheet->freezePane('A2');
            },
        ];
    }
}