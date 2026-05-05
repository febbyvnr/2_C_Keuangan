<?php

namespace App\Exports;

use App\Exports\SheetBKU; // ✅ WAJIB
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetBKUP2Bank extends SheetBKU implements WithTitle
{
    // 🔥 TAMBAHAN PARAMETER (SAMA SEPERTI P1)
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
        $events = parent::registerEvents();

        $events[AfterSheet::class] = function ($event) {

            // tetap jalankan parent
            parent::registerEvents()[AfterSheet::class]($event);

            $sheet = $event->sheet;

            // hanya ubah judul
            $sheet->setCellValue('A3', 'LAPORAN BUKU KAS UMUM - BANK (P2)');
        };

        return $events;
    }
}