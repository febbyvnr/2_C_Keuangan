<?php

namespace App\Exports;

use App\Exports\SheetBKU;
use App\Exports\SheetBKUP1Tunai;
use App\Exports\SheetBKUP2Bank;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanBukuKhasUmumExport implements WithMultipleSheets
{
    protected $bku;
    protected $p1;
    protected $p2;
    protected $role;

    public function __construct($bku, $p1, $p2, $role = 'Bendahara')
    {
        $this->bku = $bku;
        $this->p1 = $p1;
        $this->p2 = $p2;
        $this->role = $role;
    }

    public function sheets(): array
    {
        return [
            new SheetBKU($this->bku, $this->role),

            new SheetBKUP1Tunai($this->p1, $this->role),

            new SheetBKUP2Bank($this->p2, $this->role),
        ];
    }
}