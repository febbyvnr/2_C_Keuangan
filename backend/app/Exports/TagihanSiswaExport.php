<?php

namespace App\Exports;

use App\Models\TagihanSiswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TagihanSiswaExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = TagihanSiswa::with([
            'siswa',
            'jenisPembayaran',
            'pembayaran',
        ]);

        if (!empty($this->filters['ID_SISWA_TETAP'])) {
            $query->where('ID_SISWA_TETAP', $this->filters['ID_SISWA_TETAP']);
        }

        if (!empty($this->filters['ID_JENIS_PEMBAYARAN'])) {
            $query->where('ID_JENIS_PEMBAYARAN', $this->filters['ID_JENIS_PEMBAYARAN']);
        }

        if (!empty($this->filters['BULAN_TAGIHAN_SISWA'])) {
            $query->where('BULAN_TAGIHAN_SISWA', $this->filters['BULAN_TAGIHAN_SISWA']);
        }

        if (!empty($this->filters['TAHUN_TAGIHAN_SISWA'])) {
            $query->where('TAHUN_TAGIHAN_SISWA', $this->filters['TAHUN_TAGIHAN_SISWA']);
        }

        if (!empty($this->filters['STATUS_TAGIHAN_SISWA'])) {
            $query->where('STATUS_TAGIHAN_SISWA', $this->filters['STATUS_TAGIHAN_SISWA']);
        }

        if (!empty($this->filters['search'])) {
            $search = trim($this->filters['search']);

            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('NAMA_SISWA_TETAP', 'like', '%' . $search . '%')
                  ->orWhere('NISN_SISWA', 'like', '%' . $search . '%');
            });
        }

        $data = $query
            ->orderByDesc('TAHUN_TAGIHAN_SISWA')
            ->orderByDesc('ID_TAGIHAN_SISWA')
            ->get();

        $rows = $data->map(function ($tagihan) {
            $totalPembayaran = (float) $tagihan->pembayaran->sum('JUMLAH_BAYAR');
            $sisaTagihan = max(0, (float) $tagihan->JUMLAH_TAGIHAN_SISWA - $totalPembayaran);

            return [
                'ID_TAGIHAN_SISWA' => $tagihan->ID_TAGIHAN_SISWA,
                'ID_SISWA_TETAP' => $tagihan->ID_SISWA_TETAP,
                'NAMA_SISWA_TETAP' => optional($tagihan->siswa)->NAMA_SISWA_TETAP,
                'NISN_SISWA' => optional($tagihan->siswa)->NISN_SISWA,
                'JENIS_PEMBAYARAN' => optional($tagihan->jenisPembayaran)->DESKRIPSI_JENIS_PEMBAYARAN,
                'BULAN_TAGIHAN_SISWA' => $tagihan->BULAN_TAGIHAN_SISWA,
                'TAHUN_TAGIHAN_SISWA' => $tagihan->TAHUN_TAGIHAN_SISWA,
                'JUMLAH_TAGIHAN_SISWA' => (float) $tagihan->JUMLAH_TAGIHAN_SISWA,
                'TOTAL_PEMBAYARAN' => $totalPembayaran,
                'SISA_TAGIHAN' => $sisaTagihan,
                'STATUS_TAGIHAN_SISWA' => $tagihan->STATUS_TAGIHAN_SISWA,
                'ADA_TUNGGAKAN' => $sisaTagihan > 0 ? 'Ya' : 'Tidak',
                'DUEDATE_TAGIHAN_SISWA' => $tagihan->DUEDATE_TAGIHAN_SISWA,
            ];
        });

        if (!empty($this->filters['tunggakan'])) {
            $tunggakan = strtolower((string) $this->filters['tunggakan']);

            if ($tunggakan === 'ada') {
                $rows = $rows->filter(fn ($item) => $item['SISA_TAGIHAN'] > 0)->values();
            }

            if ($tunggakan === 'tidak') {
                $rows = $rows->filter(fn ($item) => $item['SISA_TAGIHAN'] <= 0)->values();
            }
        }

        return $rows->values();
    }

    public function headings(): array
    {
        return [
            'ID_TAGIHAN_SISWA',
            'ID_SISWA_TETAP',
            'NAMA_SISWA_TETAP',
            'NISN_SISWA',
            'JENIS_PEMBAYARAN',
            'BULAN_TAGIHAN_SISWA',
            'TAHUN_TAGIHAN_SISWA',
            'JUMLAH_TAGIHAN_SISWA',
            'TOTAL_PEMBAYARAN',
            'SISA_TAGIHAN',
            'STATUS_TAGIHAN_SISWA',
            'ADA_TUNGGAKAN',
            'DUEDATE_TAGIHAN_SISWA',
        ];
    }
}