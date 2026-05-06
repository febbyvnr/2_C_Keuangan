<?php

namespace App\Exports;

use App\Models\TagihanSiswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class TagihanSiswaExport implements FromCollection, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents, WithTitle
{
    protected array $filters;
    protected Collection $rows;

    protected float $totalTagihan = 0;
    protected float $totalBayar = 0;
    protected float $totalSisa = 0;

    protected string $namaTtd = '-';
    protected string $nipTtd = '-';
    protected string $roleTtd = 'Bendahara';

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->rows = collect();

        $penandatangan = \Illuminate\Support\Facades\DB::table('mst_karyawan')
            ->select(
                'NAMA_LENGKAP_GELAR as nama',
                'NIP_KARYAWAN as nip',
                'JABATAN_FUNGSIONAL as jabatan'
            )
            ->where(function ($query) {
                $query->whereRaw('LOWER(JABATAN_FUNGSIONAL) LIKE ?', ['%bendahara%'])
                    ->orWhereRaw('LOWER(JABATAN_FUNGSIONAL) LIKE ?', ['%keuangan%']);
            })
            ->orderByDesc('TANGGAL_MASUK')
            ->first();

        $this->namaTtd = $penandatangan->nama ?? '-';
        $this->nipTtd = $penandatangan->nip ?? '-';
        $this->roleTtd = 'Bendahara';
    }

    public function title(): string
    {
        return 'Tagihan Siswa';
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function headings(): array
    {
        return [
            'No',
            'ID',
            'Nama Siswa',
            'Jenis',
            'Periode',
            'Jatuh Tempo',
            'Total',
            'Bayar',
            'Sisa',
            'Status',
        ];
    }

    public function collection(): Collection
    {
        $query = TagihanSiswa::with([
            'siswa',
            'jenisTagihan',
            'pembayaran.metodePembayaran',
        ]);

        if (!empty($this->filters['ID_SISWA_TETAP'])) {
            $query->where('ID_SISWA_TETAP', $this->filters['ID_SISWA_TETAP']);
        }

        if (!empty($this->filters['ID_JENIS_TAGIHAN'])) {
            $query->where('ID_JENIS_TAGIHAN', $this->filters['ID_JENIS_TAGIHAN']);
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

        $rows = $data->map(function ($tagihan, $index) {
            $jumlahTagihan = (float) ($tagihan->JUMLAH_TAGIHAN_SISWA ?? 0);
            $totalPembayaran = (float) ($tagihan->pembayaran->sum('JUMLAH_BAYAR') ?? 0);
            $sisaTagihan = max(0, $jumlahTagihan - $totalPembayaran);

            return [
                'NO' => $index + 1,
                'ID_TAGIHAN_SISWA' => $tagihan->ID_TAGIHAN_SISWA,
                'NAMA_SISWA_TETAP' => optional($tagihan->siswa)->NAMA_SISWA_TETAP ?? '-',
                'JENIS_TAGIHAN' => optional($tagihan->jenisTagihan)->DESKRIPSI_JENIS_TAGIHAN ?? '-',
                'PERIODE' => trim(($tagihan->BULAN_TAGIHAN_SISWA ?? '-') . ' ' . ($tagihan->TAHUN_TAGIHAN_SISWA ?? '')),
                'JATUH_TEMPO' => $tagihan->DUEDATETIME_TAGIHAN_SISWA
                    ? Carbon::parse($tagihan->DUEDATETIME_TAGIHAN_SISWA)
                        ->locale('id')
                        ->translatedFormat('d F Y')
                    : '-',
                'JUMLAH_TAGIHAN_SISWA' => $jumlahTagihan,
                'TOTAL_PEMBAYARAN' => $totalPembayaran,
                'SISA_TAGIHAN' => $sisaTagihan, // ini akan tetap 0 kalau lunas
                'STATUS_TAGIHAN_SISWA' => $tagihan->STATUS_TAGIHAN_SISWA,
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

        $this->rows = $rows->values();

        $this->totalTagihan = $this->rows->sum('JUMLAH_TAGIHAN_SISWA');
        $this->totalBayar = $this->rows->sum('TOTAL_PEMBAYARAN');
        $this->totalSisa = $this->rows->sum('SISA_TAGIHAN');

        return $this->rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $startRow = 5;
                $lastDataRow = $startRow + $this->rows->count();
                $totalRow = $lastDataRow + 2;
                $signatureStartRow = $totalRow + 4;
                $tanggalRow = $signatureStartRow + 8;

                // Judul
                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');
                $sheet->mergeCells('A3:J3');

                $sheet->setCellValue('A1', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A2', 'LAPORAN TAGIHAN SISWA');
                $sheet->setCellValue('A3', 'Periode: ' . $this->getPeriodeText());

                $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

                // Garis bawah header
                $sheet->getStyle('A4:J4')->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);

                // Header tabel
                $sheet->getStyle("A{$startRow}:J{$startRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'D9EAF7',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Border tabel
                $sheet->getStyle("A{$startRow}:J{$lastDataRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->setAutoFilter("A{$startRow}:J{$lastDataRow}");

                // Alignment isi
                if ($this->rows->count() > 0) {
                    $sheet->getStyle("A6:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C6:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("E6:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("G6:I{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("J6:J{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                for ($row = 6; $row <= $lastDataRow; $row++) {
                    foreach (['G', 'H', 'I'] as $column) {
                        $value = $sheet->getCell("{$column}{$row}")->getValue();

                        if ($value === null || $value === '') {
                            $value = 0;
                        }

                        $sheet->setCellValueExplicit(
                            "{$column}{$row}",
                            (float) $value,
                            DataType::TYPE_NUMERIC
                        );
                    }
                }

                // Format angka nominal
                $sheet->getStyle("G6:I{$totalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Total row
                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->setCellValue("G{$totalRow}", $this->totalTagihan);
                $sheet->setCellValue("H{$totalRow}", $this->totalBayar);
                $sheet->setCellValue("I{$totalRow}", $this->totalSisa);

                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'F7F7F7',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("G{$totalRow}:I{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Tanda tangan
                $sheet->mergeCells("G{$signatureStartRow}:J{$signatureStartRow}");
                $sheet->mergeCells("G" . ($signatureStartRow + 1) . ":J" . ($signatureStartRow + 1));
                $sheet->mergeCells("G" . ($signatureStartRow + 4) . ":J" . ($signatureStartRow + 4));
                $sheet->mergeCells("G" . ($signatureStartRow + 5) . ":J" . ($signatureStartRow + 5));

                $sheet->setCellValue("G{$signatureStartRow}", $this->roleTtd . ',');
                $sheet->setCellValue("G" . ($signatureStartRow + 1), $this->namaTtd);
                $sheet->setCellValue("G" . ($signatureStartRow + 4), '-------------------------');
                $sheet->setCellValue("G" . ($signatureStartRow + 5), 'NIP: ' . $this->nipTtd);

                $sheet->getStyle("G{$signatureStartRow}:J" . ($signatureStartRow + 5))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("G" . ($signatureStartRow + 1))
                    ->getFont()
                    ->setBold(true);

                // Tanggal export
                $sheet->mergeCells("G{$tanggalRow}:J{$tanggalRow}");
                Carbon::setLocale('id');
                $sheet->setCellValue(
                    "G{$tanggalRow}",
                    'Yogyakarta, ' . Carbon::now()->translatedFormat('d F Y')
                );

                $sheet->getStyle("G{$tanggalRow}:J{$tanggalRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Ukuran kolom
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(10);
                $sheet->getColumnDimension('C')->setWidth(24);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(16);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(16);

                // Wrap text
                $sheet->getStyle("A1:J{$tanggalRow}")
                    ->getAlignment()
                    ->setWrapText(true);

                // Freeze header tabel
                $sheet->freezePane('A6');
            },
        ];
    }

    private function getPeriodeText(): string
    {
        if (!empty($this->filters['BULAN_TAGIHAN_SISWA']) && !empty($this->filters['TAHUN_TAGIHAN_SISWA'])) {
            return $this->filters['BULAN_TAGIHAN_SISWA'] . ' ' . $this->filters['TAHUN_TAGIHAN_SISWA'];
        }

        if (!empty($this->filters['TAHUN_TAGIHAN_SISWA'])) {
            return $this->filters['TAHUN_TAGIHAN_SISWA'];
        }

        return '-';
    }
}