<?php

namespace App\Exports;

use App\Models\MstProgramKerja;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MstProgramKerjaExport implements FromArray, WithEvents
{
    protected array $filters;
    protected Collection $data;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->data = $this->getData();
    }

    public function array(): array
    {
        return [];
    }

    private function getData(): Collection
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
            ->whereNotNull('NIP_VALIDATOR_PROGKER')
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

        return $query->get();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $data = $this->data;

                // TITLE
                $sheet->setCellValue('A2', 'RENCANA KERJA TAHUNAN');
                $sheet->setCellValue('A3', 'UNIT SEKOLAH SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A4', 'TAHUN 2026');

                $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3');
                $sheet->mergeCells('A4:L4');

                $sheet->getStyle('A2:L4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(18);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);

                // TABLE HEADER
                $headerRow = 6;

                $sheet->setCellValue("A{$headerRow}", 'NO');
                $sheet->setCellValue("B{$headerRow}", 'PROGRAM');
                $sheet->setCellValue("C{$headerRow}", '');
                $sheet->setCellValue("D{$headerRow}", '');
                $sheet->setCellValue("E{$headerRow}", 'KEGIATAN');
                $sheet->setCellValue("F{$headerRow}", 'INDIKATOR');
                $sheet->setCellValue("G{$headerRow}", 'SASARAN');
                $sheet->setCellValue("H{$headerRow}", 'PENANGGUNG JAWAB');
                $sheet->setCellValue("I{$headerRow}", 'ANGGARAN');
                $sheet->setCellValue("J{$headerRow}", 'WAKTU');
                $sheet->setCellValue("K{$headerRow}", 'KELUARAN');
                $sheet->setCellValue("L{$headerRow}", 'KETERANGAN');

                $sheet->getStyle("A{$headerRow}:L{$headerRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$headerRow}:L{$headerRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A{$headerRow}:L{$headerRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF00FF00');

                // WIDTH
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(24);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(36);
                $sheet->getColumnDimension('F')->setWidth(34);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('H')->setWidth(22);
                $sheet->getColumnDimension('I')->setWidth(16);
                $sheet->getColumnDimension('J')->setWidth(22);
                $sheet->getColumnDimension('K')->setWidth(34);
                $sheet->getColumnDimension('L')->setWidth(24);

                // GROUPING / NUMBERING
                $grouped = $this->groupData($data);
                $row = $headerRow + 1;

                foreach ($grouped as $romanIndex => $romanData) {
                    $roman = $this->toRoman((int) $romanIndex);

                    foreach ($romanData as $subCode => $subData) {
                        $bidangCode = $subData['bidang_code'];
                        $bidangLabel = $subData['bidang_label'];
                        $items = $subData['items'];

                        $urut = 1;

                        foreach ($items as $item) {
                            $kodeRoman = $roman . '.';
                            $kodeSub = $roman . '.' . $subCode . '.';
                            $kodeKegiatan = $roman . '.' . $subCode . '.' . $bidangCode . '.' . $urut . '.';

                            $namaKegiatan = (string) ($item->PROGRAM_KERJA ?? '-');
                            $waktu = $this->formatWaktu($item->WAKTU_AWAL, $item->WAKTU_AKHIR);

                            $sheet->setCellValueExplicit("A{$row}", $kodeRoman, DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit("B{$row}", $bidangLabel, DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit("C{$row}", $kodeSub, DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit("D{$row}", $kodeKegiatan, DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit("E{$row}", $kodeKegiatan . ' ' . $namaKegiatan, DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit("F{$row}", (string) ($item->INDIKATOR ?? '-'), DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit("G{$row}", (string) ($item->SASARAN ?? '-'), DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit("H{$row}", (string) ($item->NIP_PENANGGUNG_JAWAB ?? '-'), DataType::TYPE_STRING);
                            $sheet->setCellValue("I{$row}", (float) ($item->TOTAL_PROGKER ?? 0));
                            $sheet->setCellValueExplicit("J{$row}", $waktu, DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit("K{$row}", (string) ($item->KELUARAN_PROGKER ?? '-'), DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit("L{$row}", (string) ($item->KETERANGAN ?? '-'), DataType::TYPE_STRING);

                            $row++;
                            $urut++;
                        }
                    }
                }

                $endRow = $row - 1;

                // STYLE DATA
                if ($endRow >= $headerRow + 1) {
                    $sheet->getStyle("A{$headerRow}:L{$endRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);

                    $sheet->getStyle("A{$headerRow}:L{$endRow}")
                        ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                    $sheet->getStyle("A" . ($headerRow + 1) . ":D{$endRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("I" . ($headerRow + 1) . ":I{$endRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle("B" . ($headerRow + 1) . ":L{$endRow}")
                        ->getAlignment()->setWrapText(true);

                    $sheet->getStyle("I" . ($headerRow + 1) . ":I{$endRow}")
                        ->getNumberFormat()->setFormatCode('"Rp" #,##0');
                }

                $sheet->freezePane('A7');
            },
        ];
    }

    private function toRoman(int $number): string
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
        ];

        return $map[$number] ?? (string) $number;
    }

    private function groupData(Collection $data): array
    {
        $grouped = [];

        foreach ($data as $item) {
            $mapping = $this->mapBidang($item);

            $romanIndex = $mapping['roman_index'];
            $subCode = $mapping['sub_code'];

            if (!isset($grouped[$romanIndex][$subCode])) {
                $grouped[$romanIndex][$subCode] = [
                    'bidang_code' => $mapping['bidang_code'],
                    'bidang_label' => $mapping['bidang_label'],
                    'items' => [],
                ];
            }

            $grouped[$romanIndex][$subCode]['items'][] = $item;
        }

        ksort($grouped);
        foreach ($grouped as &$romanData) {
            ksort($romanData);
        }

        return $grouped;
    }

    private function mapBidang($item): array
    {
        $program = strtolower((string) ($item->PROGRAM_KERJA ?? ''));
        $kegiatan = strtolower((string) (optional($item->kegiatan)->DESKRIPSI_KEGIATAN ?? ''));
        $coa = strtolower((string) (optional($item->coa)->DESKRIPSI_COA ?? ''));

        $text = $program . ' ' . $kegiatan . ' ' . $coa;

        if (str_contains($text, 'kelulusan') || str_contains($text, 'wisuda') || str_contains($text, 'ujian')) {
            return [
                'roman_index' => 1,
                'sub_code' => 1,
                'bidang_code' => 1,
                'bidang_label' => '1.BIDANG KELULUSAN',
            ];
        }

        if (str_contains($text, 'kurikulum') || str_contains($text, 'sinkronisasi')) {
            return [
                'roman_index' => 1,
                'sub_code' => 2,
                'bidang_code' => 2,
                'bidang_label' => '2.BIDANG KEKURIKULUMAN',
            ];
        }

        if (
            str_contains($text, 'pembelajaran') ||
            str_contains($text, 'lomba') ||
            str_contains($text, 'pkl') ||
            str_contains($text, 'ekstra') ||
            str_contains($text, 'mpls')
        ) {
            return [
                'roman_index' => 1,
                'sub_code' => 2,
                'bidang_code' => 3,
                'bidang_label' => '3.BIDANG PROSES PEMBELAJARAN',
            ];
        }

        if (str_contains($text, 'anbk') || str_contains($text, 'ukk') || str_contains($text, 'asesmen')) {
            return [
                'roman_index' => 1,
                'sub_code' => 3,
                'bidang_code' => 8,
                'bidang_label' => '8.BIDANG PENILAIAN',
            ];
        }

        if (
            str_contains($text, 'guru') ||
            str_contains($text, 'mgmp') ||
            str_contains($text, 'workshop') ||
            str_contains($text, 'kompetensi') ||
            str_contains($text, 'diklat')
        ) {
            return [
                'roman_index' => 2,
                'sub_code' => 1,
                'bidang_code' => 5,
                'bidang_label' => '5.BIDANG SDM',
            ];
        }

        if (
            str_contains($text, 'ppdb') ||
            str_contains($text, 'monitoring') ||
            str_contains($text, 'rapat') ||
            str_contains($text, 'branding') ||
            str_contains($text, 'manajemen')
        ) {
            return [
                'roman_index' => 3,
                'sub_code' => 1,
                'bidang_code' => 4,
                'bidang_label' => '4.BIDANG PENGELOLAAN',
            ];
        }

        if (
            str_contains($text, 'biaya') ||
            str_contains($text, 'iuran') ||
            str_contains($text, 'air') ||
            str_contains($text, 'listrik') ||
            str_contains($text, 'atk')
        ) {
            return [
                'roman_index' => 3,
                'sub_code' => 1,
                'bidang_code' => 7,
                'bidang_label' => '7.BIDANG PEMBIAYAAN',
            ];
        }

        if (
            str_contains($text, 'sarpras') ||
            str_contains($text, 'pengadaan') ||
            str_contains($text, 'renovasi') ||
            str_contains($text, 'perawatan') ||
            str_contains($text, 'inventaris') ||
            str_contains($text, 'lampu') ||
            str_contains($text, 'ac')
        ) {
            return [
                'roman_index' => 3,
                'sub_code' => 2,
                'bidang_code' => 6,
                'bidang_label' => '6.BIDANG SARPRAS',
            ];
        }

        return [
            'roman_index' => 1,
            'sub_code' => 1,
            'bidang_code' => 1,
            'bidang_label' => '1.BIDANG KELULUSAN',
        ];
    }

    private function formatWaktu($awal, $akhir): string
    {
        $awalText = $this->cleanDateString($awal);
        $akhirText = $this->cleanDateString($akhir);

        if ($awalText === '-' && $akhirText === '-') {
            return '-';
        }

        return $awalText . "\n" . $akhirText;
    }

    private function cleanDateString($value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }
}