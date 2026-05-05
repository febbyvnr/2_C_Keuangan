<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetBKUP2Bank extends SheetBKU implements WithTitle
{
    public function __construct($data, $role, $nip = null, $nama = null, $nip_ttd = null)
    {
        parent::__construct($data, $role, $nip, $nama, $nip_ttd);
    }

    public function title(): string
    {
        return 'BKU (BANK)';
    }

    public function registerEvents(): array
    {
        $parentEvents = parent::registerEvents();
        $parentAfterSheet = $parentEvents[AfterSheet::class];

        return [
            AfterSheet::class => function ($event) use ($parentAfterSheet) {

                // jalankan semua dari parent dulu
                $parentAfterSheet($event);

                $sheet = $event->sheet;

                // override judul
                $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM - BANK (P2)');
            },
        ];
    }
}