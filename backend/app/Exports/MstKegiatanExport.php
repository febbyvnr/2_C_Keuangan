<?php

namespace App\Exports;

use App\Models\MstKegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MstKegiatanExport implements FromCollection, WithHeadings
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $search = trim((string) ($this->filters['search'] ?? ''));

        $query = MstKegiatan::query()
            ->with(['parent'])
            ->where('IS_DELETE', 0)
            ->orderBy('DESKRIPSI_KEGIATAN', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('DESKRIPSI_KEGIATAN', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function ($item) {
            return collect([
                'ID_KEGIATAN' => $item->ID_KEGIATAN,
                'MST_ID_KEGIATAN' => $item->MST_ID_KEGIATAN,
                'DESKRIPSI_KEGIATAN' => $item->DESKRIPSI_KEGIATAN,
                'PARENT_DESKRIPSI_KEGIATAN' => optional($item->parent)->DESKRIPSI_KEGIATAN,
                'IS_DELETE' => $item->IS_DELETE,
            ]);
        });
    }

    public function headings(): array
    {
        return [
            'ID_KEGIATAN',
            'MST_ID_KEGIATAN',
            'DESKRIPSI_KEGIATAN',
            'PARENT_DESKRIPSI_KEGIATAN',
            'IS_DELETE',
        ];
    }
}