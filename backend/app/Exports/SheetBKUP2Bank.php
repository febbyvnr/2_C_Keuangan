<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetBKUP2Bank extends SheetBKU implements WithTitle
{
    public function __construct($data, $role, $nip = null)
    {
        parent::__construct($data, $role, $nip);
    }

    public function title(): string
    {
        return 'BKU (BANK)';
    }

    public function registerEvents(): array
    {
        $events = parent::registerEvents();

        $events[AfterSheet::class] = function ($event) {

            parent::registerEvents()[AfterSheet::class]($event);

            $sheet = $event->sheet;

            $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM - BANK (P2)');
        };

        return $events;
    }
}