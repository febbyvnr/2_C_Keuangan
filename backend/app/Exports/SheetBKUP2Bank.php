<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetBKUP2Bank extends SheetBKU implements WithTitle
{   

    public function __construct($data, $role)
    {
        parent::__construct($data, $role);
    }

    public function title(): string
    {
        return 'BKU (BANK)';
    }
    public function registerEvents(): array
    {
        $events = parent::registerEvents();

        $events[AfterSheet::class] = function ($event) {

            // Jalankan logic dari parent (SheetBKU)
            parent::registerEvents()[AfterSheet::class]($event);

            $sheet = $event->sheet;

            // =====================
            // UBAH JUDUL
            // =====================
            $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM - BANK (P2)');
        };

        return $events;
    }
}