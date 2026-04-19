<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetBKUP1Tunai extends SheetBKU implements WithTitle
{

   public function __construct($data, $role, $nip) 
    {
        parent::__construct($data, $role, $nip);
    }

    public function title(): string
    {
        return 'BKU (TUNAI)';
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