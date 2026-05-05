<?php

namespace App\Exports;

use App\Exports\SheetBKU;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetBKUP1Tunai extends SheetBKU implements WithTitle
{
    // 🔥 TAMBAHAN PARAMETER
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
        $events = parent::registerEvents();

        $events[AfterSheet::class] = function ($event) {

            // tetap panggil parent
            parent::registerEvents()[AfterSheet::class]($event);

            $sheet = $event->sheet;

            // hanya ubah judul
            $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM - TUNAI (P1)');
        };

        return $events;
    }
}