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
            $item->DESKRIPSI_JENIS_PEMBAYARAN
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
            'A' => 8,   
            'B' => 10,  
            'C' => 30,  
        ];
    }

    public function styles(Worksheet $sheet)
    {
        
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

                
                $sheet->getStyle("A1:C{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');

                
                for ($i = 2; $i <= $highestRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle("A{$i}:C{$i}")
                            ->getFill()
                            ->setFillType('solid')
                            ->getStartColor()
                            ->setRGB('E7E6E6');
                    }
                }

                
                $sheet->getStyle('A')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B')->getAlignment()->setHorizontal('center');

                
                $sheet->getStyle('C')->getAlignment()->setWrapText(true);

                
                $sheet->freezePane('A2');
            },
        ];
    }
}