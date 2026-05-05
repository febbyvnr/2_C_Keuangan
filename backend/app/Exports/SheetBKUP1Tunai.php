<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetBKUP1Tunai extends SheetBKU implements WithTitle
{
    public function __construct($data, $role, $nip = null, $nama = null, $nip_ttd = null)
    {
        parent::__construct($data, $role, $nip, $nama, $nip_ttd);
    }

    public function title(): string
    {
        return 'BKU (TUNAI)';
    }

    public function registerEvents(): array
    {
        $parentEvents = parent::registerEvents();

        $parentAfterSheet = $parentEvents[AfterSheet::class];

        return [
            AfterSheet::class => function ($event) use ($parentAfterSheet) {

                $parentAfterSheet($event);

                $sheet = $event->sheet;

                $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM - TUNAI (P1)');
            },
        ];
    }
}