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
    protected $nip; 

    protected $nama;
    protected $nip_ttd;

    public function __construct($bku, $p1, $p2, $role = 'Bendahara', $nip = null, $nama = null, $nip_ttd = null) 
    {
        $this->bku = $bku;
        $this->p1 = $p1;
        $this->p2 = $p2;
        $this->role = $role;
        $this->nip = $nip;
        $this->nama = $nama;
        $this->nip_ttd = $nip_ttd;
    }

     public function sheets(): array
    {
        return [
            new SheetBKU($this->bku, $this->role, $this->nip, $this->nama, $this->nip_ttd), 

            new SheetBKUP1Tunai($this->p1, $this->role, $this->nip, $this->nama, $this->nip_ttd),

            new SheetBKUP2Bank($this->p2, $this->role, $this->nip, $this->nama, $this->nip_ttd),
        ];
    }
}
