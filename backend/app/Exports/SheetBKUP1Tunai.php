<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;

class SheetBKUP1Tunai extends SheetBKU
{

    public function __construct($data, $role)
    {
        parent::__construct($data, $role);
    }
    
    public function registerEvents(): array
    {
        $events = parent::registerEvents();

        $events[AfterSheet::class] = function ($event) {

            // Panggil logic dari parent (SheetBKU)
            parent::registerEvents()[AfterSheet::class]($event);

            $sheet = $event->sheet;

            // =====================
            // UBAH JUDUL
            // =====================
            $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM - TUNAI (P1)');
        };

        return $events;
    }
}