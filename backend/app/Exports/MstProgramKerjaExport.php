<?php

namespace App\Exports;

use App\Models\MstProgramKerja;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MstProgramKerjaExport implements FromCollection, WithHeadings
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $search = trim((string) ($this->filters['search'] ?? ''));
        $idTan = $this->filters['ID_TAN'] ?? null;
        $idTaAnggaran = $this->filters['ID_TA_ANGGARAN'] ?? null;

        $query = MstProgramKerja::query()
            ->with([
                'tahunAnggaran',
                'unit',
                'tan',
                'coa',
                'kegiatan',
            ])
            ->active()
            ->orderBy('ID_PROGRAM_KERJA', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('PROGRAM_KERJA', 'like', "%{$search}%")
                    ->orWhere('INDIKATOR', 'like', "%{$search}%")
                    ->orWhere('SASARAN', 'like', "%{$search}%")
                    ->orWhere('KELUARAN_PROGKER', 'like', "%{$search}%")
                    ->orWhere('NIP_PENANGGUNG_JAWAB', 'like', "%{$search}%");
            });
        }

        if (!is_null($idTan) && $idTan !== '') {
            $query->where('ID_TAN', $idTan);
        }

        if (!is_null($idTaAnggaran) && $idTaAnggaran !== '') {
            $query->where('ID_TA_ANGGARAN', $idTaAnggaran);
        }

        return $query->get()->map(function ($item) {
            return collect([
                'ID_PROGRAM_KERJA' => $item->ID_PROGRAM_KERJA,
                'TAHUN_ANGGARAN' => optional($item->tahunAnggaran)->TAHUN_ANGGARAN ?? optional($item->tahunAnggaran)->DESKRIPSI_TAHUN_ANGGARAN,
                'UNIT' => optional($item->unit)->NAMA_UNIT ?? optional($item->unit)->DESKRIPSI_UNIT,
                'TAN' => optional($item->tan)->DESKRIPSI_TAN ?? optional($item->tan)->NAMA_TAN,
                'COA' => optional($item->coa)->KODE_COA,
                'DESKRIPSI_COA' => optional($item->coa)->DESKRIPSI_COA,
                'KEGIATAN' => optional($item->kegiatan)->DESKRIPSI_KEGIATAN,
                'PROGRAM_KERJA' => $item->PROGRAM_KERJA,
                'INDIKATOR' => $item->INDIKATOR,
                'SASARAN' => $item->SASARAN,
                'WAKTU_AWAL' => $item->WAKTU_AWAL,
                'WAKTU_AKHIR' => $item->WAKTU_AKHIR,
                'KELUARAN_PROGKER' => $item->KELUARAN_PROGKER,
                'NOMINAL' => $item->NOMINAL,
                'NIP_PENANGGUNG_JAWAB' => $item->NIP_PENANGGUNG_JAWAB,
            ]);
        });
    }

    public function headings(): array
    {
        return [
            'ID_PROGRAM_KERJA',
            'TAHUN_ANGGARAN',
            'UNIT',
            'TAN',
            'KODE_COA',
            'DESKRIPSI_COA',
            'KEGIATAN',
            'PROGRAM_KERJA',
            'INDIKATOR',
            'SASARAN',
            'WAKTU_AWAL',
            'WAKTU_AKHIR',
            'KELUARAN_PROGKER',
            'NOMINAL',
            'NIP_PENANGGUNG_JAWAB',
        ];
    }
}