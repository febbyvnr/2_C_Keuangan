<?php

namespace App\Exports;

use App\Models\MstCoa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MstCoaExport implements FromCollection, WithHeadings
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $search = trim((string) ($this->filters['search'] ?? ''));

        $query = MstCoa::query()
            ->with(['parent'])
            ->where('IS_DELETE', 0)
            ->orderBy('KODE_COA', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('KODE_COA', $search)
                  ->orWhere('DESKRIPSI_COA', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function ($item) {
            return collect([
                'ID_MASTER_COA' => $item->ID_MASTER_COA,
                'MST_ID_MASTER_COA' => $item->MST_ID_MASTER_COA,
                'KODE_COA' => $item->KODE_COA,
                'DESKRIPSI_COA' => $item->DESKRIPSI_COA,
                'PARENT_KODE_COA' => optional($item->parent)->KODE_COA,
                'PARENT_DESKRIPSI_COA' => optional($item->parent)->DESKRIPSI_COA,
                'IS_DELETE' => $item->IS_DELETE,
            ]);
        });
    }

    public function headings(): array
    {
        return [
            'ID_MASTER_COA',
            'MST_ID_MASTER_COA',
            'KODE_COA',
            'DESKRIPSI_COA',
            'PARENT_KODE_COA',
            'PARENT_DESKRIPSI_COA',
            'IS_DELETE',
        ];
    }
}