<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanKeuanganYayasanExport implements WithEvents
{
    protected $filters;
    protected $role;
    protected $nip;
    protected $nama;

    public function __construct($filters)
    {
        $this->filters = $filters;
        $this->role = $filters['role'] ?? (Auth::user()->role ?? 'Bendahara');
        $this->nip = $filters['nip'] ?? (Auth::user()->nip ?? null);
        $this->nama = $filters['nama'] ?? (Auth::user()->name ?? '-');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet;
                $isPdf = ($this->filters['format'] ?? '') === 'pdf';

                // JUDUL LAPORAN
                $sheet->setCellValue('A2', 'SMK BOPKRI 2 YOGYAKARTA');
                $sheet->setCellValue('A3', 'LAPORAN KEUANGAN YAYASAN');
                $sheet->setCellValue('A4', 'Periode Tahun Anggaran: ' . ($this->filters['tahun'] ?? '-'));
                
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                $sheet->mergeCells('A4:E4');

                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(11);

                $sheet->getStyle('A2:A4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(22);
                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getDefaultRowDimension()->setRowHeight(-1);

                // HEADER TABEL
                $headerRow = 6;
                $sheet->setCellValue('A' . $headerRow, 'KODE AKUN');
                $sheet->setCellValue('B' . $headerRow, 'NAMA AKUN');
                $sheet->setCellValue('C' . $headerRow, 'PENERIMAAN');
                $sheet->setCellValue('D' . $headerRow, 'PENGELUARAN');
                $sheet->setCellValue('E' . $headerRow, 'SALDO');

                $sheet->getStyle("A$headerRow:E$headerRow")->getFont()->setBold(true)->getColor()->setARGB('FF000000');
                $sheet->getStyle("A$headerRow:E$headerRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A$headerRow:E$headerRow")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A$headerRow:E$headerRow")->getAlignment()->setWrapText(true);
                $sheet->getStyle("A$headerRow:E$headerRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
                $sheet->getRowDimension($headerRow)->setRowHeight(-1);

                $sheet->setAutoFilter("A$headerRow:E$headerRow");

                // DATA DARI DATABASE
                $data = $this->getDataKeuangan();
                $row = $headerRow + 1;
                $totalMasuk = 0;
                $totalKeluar = 0;

                foreach ($data as $item) {
                    $sheet->setCellValue("A$row", $item->KODE_COA);
                    $sheet->setCellValue("B$row", $item->NAMA_COA);
                    $sheet->setCellValue("C$row", $item->TOTAL_MASUK);
                    $sheet->setCellValue("D$row", $item->TOTAL_KELUAR);
                    $sheet->setCellValue("E$row", $item->TOTAL_MASUK - $item->TOTAL_KELUAR);

                    // Format Angka (0 => '-')
                    $sheet->getStyle("C$row:E$row")->getNumberFormat()->setFormatCode('"Rp" #,##0;[Red]"Rp" -#,##0;"-"');
                    $sheet->getStyle("A$row:E$row")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                    $sheet->getStyle("A$row:E$row")->getAlignment()->setWrapText(true);
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                    
                    $totalMasuk += $item->TOTAL_MASUK;
                    $totalKeluar += $item->TOTAL_KELUAR;
                    $row++;
                }

                // TOTAL AKHIR
                $totalRow = $row + 1;
                $sheet->setCellValue("B$totalRow", 'TOTAL KESELURUHAN');
                $sheet->setCellValue("C$totalRow", $totalMasuk);
                $sheet->setCellValue("D$totalRow", $totalKeluar);
                $sheet->setCellValue("E$totalRow", $totalMasuk - $totalKeluar);
                
                $sheet->getStyle("A$totalRow:E$totalRow")->getFont()->setBold(true)->getColor()->setARGB('FF000000');
                $sheet->getStyle("A$totalRow:E$totalRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE699');
                $sheet->getStyle("C$totalRow:E$totalRow")->getNumberFormat()->setFormatCode('"Rp" #,##0;[Red]"Rp" -#,##0;"-"');

                // BORDER
                $sheet->getStyle("A$headerRow:E" . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A$headerRow:E" . ($row - 1))->getFont()->getColor()->setARGB('FF000000');

                // Wrap nama akun agar tidak melebar di PDF.
                if ($row > ($headerRow + 1)) {
                    $sheet->getStyle('B' . ($headerRow + 1) . ':B' . ($row - 1))
                        ->getAlignment()->setWrapText(true);
                }

                // FOOTER
                $footerRow = $totalRow + 3;
                $role = $this->role ?: 'Bendahara';
                $nama = $this->nama ?: '-';
                $nip = $this->nip ?: '-';

                $sheet->setCellValue("E" . ($footerRow), 'Yogyakarta, ' . date('d F Y'));
                $sheet->getStyle("E" . ($footerRow))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->setCellValue("E" . ($footerRow + 1), $role . ',');
                $sheet->getStyle("E" . ($footerRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->setCellValue("E" . ($footerRow + 7), $nama);
                $sheet->getStyle("E" . ($footerRow + 7))->getFont()->setBold(true);
                $sheet->getStyle("E" . ($footerRow + 7))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->setCellValue("E" . ($footerRow + 8), '-------------------------');
                $sheet->getStyle("E" . ($footerRow + 8))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->setCellValue("E" . ($footerRow + 9), 'NIP: ' . $nip);
                $sheet->getStyle("E" . ($footerRow + 9))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // PDF SETUP
                $sheet->getPageSetup()->setOrientation(
                    $isPdf
                        ? \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                        : \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT
                );
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setHorizontalCentered(true);

                $sheet->getPageMargins()->setTop(0.35);
                $sheet->getPageMargins()->setBottom(0.35);
                $sheet->getPageMargins()->setLeft(0.3);
                $sheet->getPageMargins()->setRight(0.3);
                
                // Column Width
                if ($isPdf) {
                    $sheet->getStyle('A:E')->getFont()->setSize(9);
                }

                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(36);
                $sheet->getColumnDimension('C')->setWidth(22);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(22);

                $sheet->freezePane('A7');
            },
        ];
    }

    public function getData()
    {
        return $this->getDataKeuangan();
    }
    
    private function getDataKeuangan()
    {
        try {
            $totalMasukSql = $this->buildTotalMasukSql();
            $totalKeluarSql = $this->buildTotalKeluarSql();

            return DB::table('mst_coa as c')
                ->select(
                    'c.KODE_COA',
                    'c.DESKRIPSI_COA as NAMA_COA'
                )
                ->selectRaw("{$totalMasukSql} as TOTAL_MASUK")
                ->selectRaw("{$totalKeluarSql} as TOTAL_KELUAR")
                ->where('c.IS_DELETE', 0)
                ->orderBy('c.KODE_COA', 'ASC')
                ->get();
        } catch (\Throwable $e) {
            report($e);

            return collect();
        }
    }

    private function buildTotalMasukSql(): string
    {
        $idTaAnggaran = $this->getIdTaAnggaran();
        $tahunAngka = $this->getTahunAngka();

        // Jalur utama: tr_pembayaran -> tagihan_siswa -> ref_tarif -> mst_coa(KODE_COA)
        if (
            Schema::hasTable('tr_pembayaran')
            && Schema::hasTable('tagihan_siswa')
            && Schema::hasTable('ref_tarif')
            && Schema::hasColumn('tr_pembayaran', 'ID_TAGIHAN_SISWA')
            && Schema::hasColumn('tr_pembayaran', 'JUMLAH_BAYAR')
            && Schema::hasColumn('tagihan_siswa', 'ID_TAGIHAN_SISWA')
            && Schema::hasColumn('tagihan_siswa', 'ID_TARIF')
            && Schema::hasColumn('ref_tarif', 'ID_TARIF')
            && Schema::hasColumn('ref_tarif', 'KODE_COA')
        ) {
            $whereTahun = '';

            if ($idTaAnggaran && Schema::hasColumn('ref_tarif', 'ID_TA_ANGGARAN')) {
                $whereTahun = ' AND rt.ID_TA_ANGGARAN = ' . $idTaAnggaran;
            } elseif ($idTaAnggaran && Schema::hasColumn('tr_pembayaran', 'KODE_TA')) {
                $whereTahun = ' AND p.KODE_TA = ' . $idTaAnggaran;
            } elseif ($tahunAngka && Schema::hasColumn('tagihan_siswa', 'TAHUN_TAGIHAN_SISWA')) {
                $whereTahun = ' AND ts.TAHUN_TAGIHAN_SISWA = ' . $tahunAngka;
            } elseif ($tahunAngka && Schema::hasColumn('tr_pembayaran', 'TGL_BAYAR')) {
                $whereTahun = ' AND YEAR(p.TGL_BAYAR) = ' . $tahunAngka;
            }

            return "IFNULL((
                SELECT SUM(p.JUMLAH_BAYAR)
                FROM tr_pembayaran p
                JOIN tagihan_siswa ts ON p.ID_TAGIHAN_SISWA = ts.ID_TAGIHAN_SISWA
                JOIN ref_tarif rt ON ts.ID_TARIF = rt.ID_TARIF
                WHERE rt.KODE_COA = c.KODE_COA{$whereTahun}
            ), 0)";
        }

        // Fallback: tr_pembayaran -> ref_jenis_pembayaran -> mst_coa(KODE_COA)
        if (
            Schema::hasTable('tr_pembayaran')
            && Schema::hasTable('ref_jenis_pembayaran')
            && Schema::hasColumn('tr_pembayaran', 'ID_JENIS_PEMBAYARAN')
            && Schema::hasColumn('tr_pembayaran', 'JUMLAH_BAYAR')
            && Schema::hasColumn('ref_jenis_pembayaran', 'ID_JENIS_PEMBAYARAN')
            && Schema::hasColumn('ref_jenis_pembayaran', 'KODE_COA')
        ) {
            $whereTahun = '';

            if ($idTaAnggaran && Schema::hasColumn('tr_pembayaran', 'KODE_TA')) {
                $whereTahun = ' AND p.KODE_TA = ' . $idTaAnggaran;
            } elseif ($tahunAngka && Schema::hasColumn('tr_pembayaran', 'TGL_BAYAR')) {
                $whereTahun = ' AND YEAR(p.TGL_BAYAR) = ' . $tahunAngka;
            }

            return "IFNULL((
                SELECT SUM(p.JUMLAH_BAYAR)
                FROM tr_pembayaran p
                JOIN ref_jenis_pembayaran jp ON p.ID_JENIS_PEMBAYARAN = jp.ID_JENIS_PEMBAYARAN
                WHERE jp.KODE_COA = c.KODE_COA{$whereTahun}
            ), 0)";
        }

        // Fallback: tr_pembayaran -> ref_jenis_pembayaran -> mst_coa(ID_MASTER_COA)
        if (
            Schema::hasTable('tr_pembayaran')
            && Schema::hasTable('ref_jenis_pembayaran')
            && Schema::hasColumn('tr_pembayaran', 'ID_JENIS_PEMBAYARAN')
            && Schema::hasColumn('tr_pembayaran', 'JUMLAH_BAYAR')
            && Schema::hasColumn('ref_jenis_pembayaran', 'ID_JENIS_PEMBAYARAN')
            && Schema::hasColumn('ref_jenis_pembayaran', 'ID_MASTER_COA')
            && Schema::hasColumn('mst_coa', 'ID_MASTER_COA')
        ) {
            $whereTahun = '';

            if ($idTaAnggaran && Schema::hasColumn('tr_pembayaran', 'KODE_TA')) {
                $whereTahun = ' AND p.KODE_TA = ' . $idTaAnggaran;
            } elseif ($tahunAngka && Schema::hasColumn('tr_pembayaran', 'TGL_BAYAR')) {
                $whereTahun = ' AND YEAR(p.TGL_BAYAR) = ' . $tahunAngka;
            }

            return "IFNULL((
                SELECT SUM(p.JUMLAH_BAYAR)
                FROM tr_pembayaran p
                JOIN ref_jenis_pembayaran jp ON p.ID_JENIS_PEMBAYARAN = jp.ID_JENIS_PEMBAYARAN
                WHERE jp.ID_MASTER_COA = c.ID_MASTER_COA{$whereTahun}
            ), 0)";
        }

        // Fallback: jika tr_penerimaan langsung menyimpan KODE_COA
        if (
            Schema::hasTable('tr_penerimaan')
            && Schema::hasColumn('tr_penerimaan', 'KODE_COA')
            && Schema::hasColumn('tr_penerimaan', 'JUMLAH_TR_PENERIMAAN')
        ) {
            $whereTahun = '';

            if ($idTaAnggaran && Schema::hasColumn('tr_penerimaan', 'ID_TA_ANGGARAN')) {
                $whereTahun = ' AND p.ID_TA_ANGGARAN = ' . $idTaAnggaran;
            } elseif ($tahunAngka && Schema::hasColumn('tr_penerimaan', 'TANGGAL_TR_PENERIMAAN')) {
                $whereTahun = ' AND YEAR(p.TANGGAL_TR_PENERIMAAN) = ' . $tahunAngka;
            }

            return "IFNULL((
                SELECT SUM(p.JUMLAH_TR_PENERIMAAN)
                FROM tr_penerimaan p
                WHERE p.KODE_COA = c.KODE_COA{$whereTahun}
            ), 0)";
        }

        // Tidak ada jalur data yang valid di skema saat ini.
        return '0';
    }

    private function buildTotalKeluarSql(): string
    {
        $idTaAnggaran = $this->getIdTaAnggaran();
        $tahunAngka = $this->getTahunAngka();

        // Jalur utama: fpd_anggaran -> mst_program_kerja -> mst_coa(ID_MASTER_COA)
        if (
            Schema::hasTable('fpd_anggaran')
            && Schema::hasTable('mst_program_kerja')
            && Schema::hasColumn('fpd_anggaran', 'ID_PROGRAM_KERJA')
            && Schema::hasColumn('fpd_anggaran', 'NOMINAL_FPD')
            && Schema::hasColumn('mst_program_kerja', 'ID_PROGRAM_KERJA')
            && Schema::hasColumn('mst_program_kerja', 'ID_MASTER_COA')
            && Schema::hasColumn('mst_coa', 'ID_MASTER_COA')
        ) {
            $whereTahun = '';

            if ($idTaAnggaran && Schema::hasColumn('mst_program_kerja', 'ID_TA_ANGGARAN')) {
                $whereTahun = ' AND pk.ID_TA_ANGGARAN = ' . $idTaAnggaran;
            } elseif ($tahunAngka && Schema::hasColumn('fpd_anggaran', 'TGL_FPD')) {
                $whereTahun = ' AND YEAR(f.TGL_FPD) = ' . $tahunAngka;
            }

            return "IFNULL((
                SELECT SUM(f.NOMINAL_FPD)
                FROM fpd_anggaran f
                JOIN mst_program_kerja pk ON f.ID_PROGRAM_KERJA = pk.ID_PROGRAM_KERJA
                WHERE pk.ID_MASTER_COA = c.ID_MASTER_COA{$whereTahun}
            ), 0)";
        }

        return '0';
    }

    private function getIdTaAnggaran(): ?int
    {
        if (!isset($this->filters['id_ta_anggaran'])) {
            return null;
        }

        $value = $this->filters['id_ta_anggaran'];

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function getTahunAngka(): ?int
    {
        if (!isset($this->filters['tahun_angka'])) {
            return null;
        }

        $value = $this->filters['tahun_angka'];

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }




     
}