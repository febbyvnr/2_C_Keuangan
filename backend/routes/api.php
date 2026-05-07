<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AccessLogController;
use App\Http\Controllers\MstCoaController;
use App\Http\Controllers\MstKegiatanController;
use App\Http\Controllers\MstProgramKerjaController;
use App\Http\Controllers\RefTahunAnggaranController;
use App\Http\Controllers\RefTanController;
use App\Http\Controllers\RefJenisTarifController;
use App\Http\Controllers\RefTarifController;
use App\Http\Controllers\DtlFpdController;
use App\Http\Controllers\FpdAnggaranController;
use App\Http\Controllers\RefSumberDanaController;
use App\Http\Controllers\TrPmController;
use App\Http\Controllers\RefPmController;
use App\Http\Controllers\RefVisiMisiController;
use App\Http\Controllers\RefPenerimaanController;
use App\Http\Controllers\TrCicilanController;
use App\Http\Controllers\TrPembayaranController;
use App\Http\Controllers\EvaluasiRktController;
use App\Http\Controllers\TagihanSiswaController;
use App\Http\Controllers\LaporanPenerimaanController;
use App\Http\Controllers\TrPenerimaanController;
use App\Http\Controllers\RefJenisPembayaranController;
use App\Http\Controllers\RefJenisPembayaranExportController;
use App\Http\Controllers\JenisTarifExportController;
use App\Http\Controllers\LaporanRkasController;
use App\Http\Controllers\LaporanKeuanganYayasanController;
use App\Http\Controllers\MstUnitController;
use App\Http\Controllers\MstKaryawanController;

use Termwind\Components\Raw;
use App\Http\Controllers\RkaController;
use App\Http\Controllers\LaporanBukuKhasUmumController;
use App\Http\Controllers\LaporanPengeluaranController;

use App\Http\Controllers\DashboardBendaharaController;
use App\Http\Controllers\DashboardKepsekKeuanganController;
use App\Http\Controllers\RefJenisTagihanController;
use App\Http\Controllers\RefMetodePembayaranController;
use App\Http\Controllers\DashboardTimPenjaminanMutuController;
use App\Http\Controllers\DashboardKepsekRKTController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

// ==========================
// ROUTE PENERIMAAN
// ==========================
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/activity-logs', [ActivityLogController::class, 'index']);
Route::get('/access-logs', [AccessLogController::class, 'index']);

// F82, F83, F84 -> Create, Update, Delete
Route::middleware(['role:Bendahara'])->group(function () {
    Route::post('/keuangan/penerimaan', [TrPenerimaanController::class, 'store']); // F82
    Route::put('/keuangan/penerimaan/{id}', [TrPenerimaanController::class, 'update']); // F83
    Route::delete('/keuangan/penerimaan/{id}', [TrPenerimaanController::class, 'destroy']); // F84
});

// F85, F86, F87 -> Read, Search, Export
// 3. F85, F86, F87 (Read, Search, Export) & Route /me - BENDAHARA ATAU KEPALA SEKOLAH
Route::middleware(['role:Bendahara,Kepala Sekolah'])->group(function () {
    Route::get('/me', function (Request $request) {
        return response()->json([
            'message' => 'Token berhasil ditembus!',
            'user' => $request->user(),
            'roles' => $request->user()->jabatans->pluck('DESKRIPSI_JABATAN')
        ]);
    });

    Route::get('/keuangan/penerimaan', [TrPenerimaanController::class, 'index']); // F85 & F86
    Route::get('/keuangan/penerimaan/export', [TrPenerimaanController::class, 'export']); // F87
});

Route::prefix('coa')->group(function () {
    Route::get('/', [MstCoaController::class, 'index']);
    Route::get('/parents', [MstCoaController::class, 'parents']);

    Route::get('/export/excel', [MstCoaController::class, 'exportExcel']);
    Route::get('/export/pdf', [MstCoaController::class, 'exportPdf']);

    Route::get('/{id}', [MstCoaController::class, 'show'])->whereNumber('id');
    Route::post('/store', [MstCoaController::class, 'store']);
    Route::put('/update/{id}', [MstCoaController::class, 'update']);
    Route::delete('/delete/{id}', [MstCoaController::class, 'destroy']);
});

Route::prefix('unit')->group(function () {
    Route::get('/', [MstUnitController::class, 'index']);
});

Route::get('/karyawan/{nip}', [MstKaryawanController::class, 'show']);

Route::prefix('kegiatan')->group(function () {
    Route::get('/', [MstKegiatanController::class, 'index']);
    Route::get('/parents', [MstKegiatanController::class, 'parents']);

    Route::get('/export', [MstKegiatanController::class, 'export']);
    Route::get('/export/excel', [MstKegiatanController::class, 'exportExcel']);
    Route::get('/export/csv', [MstKegiatanController::class, 'exportCsv']);
    Route::get('/export/pdf', [MstKegiatanController::class, 'exportPdf']);

    Route::get('/{id}', [MstKegiatanController::class, 'show'])->whereNumber('id');
    Route::post('/store', [MstKegiatanController::class, 'store']);
    Route::put('/update/{id}', [MstKegiatanController::class, 'update']);
    Route::delete('/delete/{id}', [MstKegiatanController::class, 'destroy']);
});

Route::prefix('tahun-anggaran')->group(function () {
    Route::get('/', [RefTahunAnggaranController::class, 'index']);
    Route::get('/search', [RefTahunAnggaranController::class, 'search']);
    Route::get('/{id}', [RefTahunAnggaranController::class, 'show']);
    Route::post('/store', [RefTahunAnggaranController::class, 'store']);
    Route::put('/update/{id}', [RefTahunAnggaranController::class, 'update']);
    Route::delete('/delete/{id}', [RefTahunAnggaranController::class, 'destroy']);
});

Route::prefix('ref-penerimaan')->group(function () {
    Route::get('/', [RefPenerimaanController::class, 'index']);
    Route::get('/search', [RefPenerimaanController::class, 'search']);
    Route::get('/{id}', [RefPenerimaanController::class, 'show']);
    Route::post('/store', [RefPenerimaanController::class, 'store']);
    Route::put('/update/{id}', [RefPenerimaanController::class, 'update']);
    Route::delete('/delete/{id}', [RefPenerimaanController::class, 'destroy']);
});

Route::prefix('tr-cicilan')->group(function () {
    Route::get('/', [TrCicilanController::class, 'index']);
    Route::get('/search', [TrCicilanController::class, 'search']);
    Route::get('/{id}', [TrCicilanController::class, 'show']);
    Route::post('/store', [TrCicilanController::class, 'store']);
    Route::put('/update/{id}', [TrCicilanController::class, 'update']);
    Route::delete('/delete/{id}', [TrCicilanController::class, 'destroy']);
});

Route::prefix('tr-pembayaran')->group(function () {
    Route::get('/', [TrPembayaranController::class, 'index']);
    Route::get('/search', [TrPembayaranController::class, 'search']);
    Route::get('/{id}', [TrPembayaranController::class, 'show']);
    Route::post('/store', [TrPembayaranController::class, 'store']);
    Route::put('/update/{id}', [TrPembayaranController::class, 'update']);
    Route::delete('/delete/{id}', [TrPembayaranController::class, 'destroy']);
});

Route::prefix('ref-tan')->group(function () {
    Route::get('/', [RefTanController::class, 'index']);
    Route::get('/current', [RefTanController::class, 'current']);
    Route::get('/{id}', [RefTanController::class, 'show']);
    Route::post('/store', [RefTanController::class, 'store']);
    Route::put('/update/{id}', [RefTanController::class, 'update']);
    Route::delete('/delete/{id}', [RefTanController::class, 'destroy']);
});

Route::prefix('rkt')->group(function () {
    Route::get('/', [MstProgramKerjaController::class, 'index']);
    Route::get('/export/excel', [MstProgramKerjaController::class, 'exportExcel']);
    Route::post('/store', [MstProgramKerjaController::class, 'store']);
    Route::put('/update/{id}', [MstProgramKerjaController::class, 'update'])->whereNumber('id');
    Route::put('/approve/{id}', [MstProgramKerjaController::class, 'approve'])->whereNumber('id');
    Route::put('/reject/{id}', [MstProgramKerjaController::class, 'reject'])->whereNumber('id');
    Route::put('/revisi/{id}', [MstProgramKerjaController::class, 'revisi'])->whereNumber('id');
    Route::delete('/delete/{id}', [MstProgramKerjaController::class, 'destroy'])->whereNumber('id');
    Route::post('/ajukan/{id}', [MstProgramKerjaController::class, 'ajukan'])->whereNumber('id');
    Route::get('/{id}', [MstProgramKerjaController::class, 'show'])->whereNumber('id');
});

Route::prefix('dtl-fpd')->group(function () {
    Route::get('/', [DtlFpdController::class, 'index']);
    Route::get('/search', [DtlFpdController::class, 'search']);
    Route::get('/{id}', [DtlFpdController::class, 'show']);
    Route::post('/store', [DtlFpdController::class, 'store']);
    Route::put('/update/{id}', [DtlFpdController::class, 'update']);
    Route::delete('/delete/{id}', [DtlFpdController::class, 'destroy']);
});

Route::prefix('fpd-anggaran')->group(function () {
    Route::get('/', [FpdAnggaranController::class, 'index']);
    Route::get('/search', [FpdAnggaranController::class, 'search']);
    Route::get('/export/excel', [FpdAnggaranController::class, 'exportListExcel']);
    Route::get('/export/pdf', [FpdAnggaranController::class, 'exportListPdf']);
    Route::get('/export/{id}', [FpdAnggaranController::class, 'export']);
    Route::get('/{id}', [FpdAnggaranController::class, 'show']);
    Route::post('/store', [FpdAnggaranController::class, 'store']);
    Route::put('/update/{id}', [FpdAnggaranController::class, 'update']);
    Route::put('/reject/{id}', [FpdAnggaranController::class, 'reject']);
    Route::delete('/delete/{id}', [FpdAnggaranController::class, 'destroy']);
});

Route::prefix('ref-sumber-dana')->group(function () {
    Route::get('/', [RefSumberDanaController::class, 'index']);
    Route::get('/search', [RefSumberDanaController::class, 'search']);
    Route::get('/{id}', [RefSumberDanaController::class, 'show']);
    Route::post('/store', [RefSumberDanaController::class, 'store']);
    Route::put('/update/{id}', [RefSumberDanaController::class, 'update']);
    Route::delete('/delete/{id}', [RefSumberDanaController::class, 'destroy']);
});

Route::prefix('tr-pm')->group(function () {
    Route::get('/', [TrPmController::class, 'index']);
    Route::get('/search', [TrPmController::class, 'search']);
    Route::get('/{id}', [TrPmController::class, 'show']);
    Route::post('/store', [TrPmController::class, 'store']);
    Route::put('/update/{id}', [TrPmController::class, 'update']);
    Route::delete('/delete/{id}', [TrPmController::class, 'destroy']);
});

Route::prefix('ref-visi-misi')->group(function () {
    Route::get('/', [RefVisiMisiController::class, 'index']);
    Route::get('/search', [RefVisiMisiController::class, 'search']);
    Route::get('/{id}', [RefVisiMisiController::class, 'show']);
    Route::post('/store', [RefVisiMisiController::class, 'store']);
    Route::put('/update/{id}', [RefVisiMisiController::class, 'update']);
    Route::delete('/delete/{id}', [RefVisiMisiController::class, 'destroy']);
});

Route::prefix('ref-pm')->group(function () {
    Route::get('/', [RefPmController::class, 'index']);
    Route::get('/search', [RefPmController::class, 'search']);
    Route::get('/export/excel', [RefPmController::class, 'exportExcel']);
    Route::put('/evaluasi/{id}', [RefPmController::class, 'updateEvaluasi']);

    Route::post('/', [RefPmController::class, 'store']);
    Route::get('/{id}', [RefPmController::class, 'show']);
    Route::put('/{id}', [RefPmController::class, 'update']);
    Route::delete('/{id}', [RefPmController::class, 'destroy']);
});

Route::prefix('jenis-tarif')->group(function () {
    Route::get('/', [RefJenisTarifController::class, 'index']);
    Route::get('/search', [RefJenisTarifController::class, 'search']);
    Route::get('/{id}', [RefJenisTarifController::class, 'show']);
    Route::post('/store', [RefJenisTarifController::class, 'store']);
    Route::put('/update/{id}', [RefJenisTarifController::class, 'update']);
    Route::delete('/delete/{id}', [RefJenisTarifController::class, 'destroy']);
});

// Route::prefix('tarif')->group(function () {
//     Route::get('/', [RefTarifController::class, 'index']);
//     Route::get('/search', [RefTarifController::class, 'search']);
//     Route::get('/by-jenis/{idJenis}', [RefTarifController::class, 'byJenis']);
//     Route::get('/by-tahun/{idTahun}', [RefTarifController::class, 'byTahun']);
//     Route::get('/detail/{id}', [RefTarifController::class, 'showById']);
//     Route::get('/{idJenis}/{idTahun}', [RefTarifController::class, 'show']);
//     Route::post('/store', [RefTarifController::class, 'store']);
//     Route::put('/update/{idJenis}/{idTahun}', [RefTarifController::class, 'update']);
//     Route::delete('/delete/{idJenis}/{idTahun}', [RefTarifController::class, 'destroy']);
// });

Route::prefix('tarif')->group(function () {
    Route::get('/', [RefTarifController::class, 'index']);
    Route::post('/store', [RefTarifController::class, 'store']);
    Route::put('/update/{id}', [RefTarifController::class, 'update']);
    Route::delete('/delete/{id}', [RefTarifController::class, 'destroy']);
});

Route::prefix('evaluasi-rkt/export')->group(function () {
    Route::get('/excel', [EvaluasiRktController::class, 'exportExcel']);
    Route::get('/csv', [EvaluasiRktController::class, 'exportCsv']);
    Route::get('/pdf', [EvaluasiRktController::class, 'exportPdf']);
});

Route::prefix('evaluasi-rkt')->group(function () {
    Route::get('/', [EvaluasiRktController::class, 'index']);
    Route::get('/search', [EvaluasiRktController::class, 'search']);
    Route::get('/{id}', [EvaluasiRktController::class, 'show']);
    Route::post('/store', [EvaluasiRktController::class, 'store']);
    Route::put('/update/{id}', [EvaluasiRktController::class, 'update']);
    Route::delete('/delete/{id}', [EvaluasiRktController::class, 'destroy']);
    Route::put('/approve/{id}', [EvaluasiRktController::class, 'approve']);
    Route::put('/reject/{id}', [EvaluasiRktController::class, 'reject']);
});

Route::get('/ref-jenis-tagihan', [RefJenisTagihanController::class, 'index']);
Route::get('/ref-metode-pembayaran', [RefMetodePembayaranController::class, 'index']);

Route::prefix('tagihan-siswa')->group(function () {
    Route::get('/', [TagihanSiswaController::class, 'index']);
    Route::get('/search', [TagihanSiswaController::class, 'search']);
    Route::get('/siswa-options', [TagihanSiswaController::class, 'getSiswaOptions']);
    Route::get('/export', [TagihanSiswaController::class, 'export']);
    Route::get('/export/excel', [TagihanSiswaController::class, 'exportExcel']);
    Route::get('/export/pdf', [TagihanSiswaController::class, 'exportPdf']);
    Route::get('/{id}', [TagihanSiswaController::class, 'show']);
    Route::post('/store', [TagihanSiswaController::class, 'store']);
    Route::put('/update/{id}', [TagihanSiswaController::class, 'update']);
    Route::delete('/delete/{id}', [TagihanSiswaController::class, 'destroy']);
});

Route::prefix('laporan')->group(function () {
    Route::get('/penerimaan', [LaporanPenerimaanController::class, 'penerimaan']);
    Route::get('/rkas', [LaporanRkasController::class, 'rkas']);
    Route::post('/rkas/export', [LaporanRkasController::class, 'export']);
    Route::post('/rkas/export-excel', [LaporanRkasController::class, 'exportExcel']);
    Route::post('/rkas/export-pdf', [LaporanRkasController::class, 'exportPdf']);
    Route::get('/rkas/export', [LaporanRkasController::class, 'export']);
    Route::get('/rkas/export-excel', [LaporanRkasController::class, 'export']);
    Route::get('/rkas/export-pdf', [LaporanRkasController::class, 'exportPdf']);
    Route::get('/yayasan', [LaporanKeuanganYayasanController::class, 'yayasan']);
    Route::get('/yayasan/export-excel', [LaporanKeuanganYayasanController::class, 'exportExcel']);
    Route::get('/yayasan/export-pdf', [LaporanKeuanganYayasanController::class, 'exportPdf']);
    Route::get('/jenis-tarif/export-excel', [JenisTarifExportController::class, 'export']);
    Route::get('/jenis-tarif/export-pdf', [JenisTarifExportController::class, 'exportPdf']);
});

    Route::get('/siswa-ortu/profile/{id}', [TagihanSiswaController::class, 'getProfileSiswa']);
    Route::put('/siswa-ortu/profile/{id}', [TagihanSiswaController::class, 'updateProfileSiswa']);

Route::prefix('laporan')->group(function () {
    Route::get('/penerimaan', [LaporanPenerimaanController::class, 'penerimaan']);
    Route::get('/bku', [LaporanBukuKhasUmumController::class, 'bku']);
    Route::get('/pengeluaran', [LaporanPengeluaranController::class, 'pengeluaran']);
});

Route::prefix('rka')->group(function () {
    Route::get('/', [RkaController::class, 'index']);
    Route::get('/search', [RkaController::class, 'search']);
    Route::get('/export', [RkaController::class, 'export']);
    Route::get('/export/pdf', [RkaController::class, 'exportPdf']);
    Route::get('/{id}', [RkaController::class, 'show'])->whereNumber('id');
    Route::post('/store', [RkaController::class, 'store']);
    Route::put('/update/{id}', [RkaController::class, 'update'])->whereNumber('id');
    Route::delete('/delete/{id}', [RkaController::class, 'destroy'])->whereNumber('id');

    Route::put('/detail/{id}', [RkaController::class, 'updateDetail'])->whereNumber('id');
    Route::delete('/detail/{id}', [RkaController::class, 'destroyDetail'])->whereNumber('id');
});

Route::prefix('jenis-pembayaran')->group(function () {
    Route::get('/', [RefJenisPembayaranController::class, 'index']);
    Route::post('/store', [RefJenisPembayaranController::class, 'store']);
    Route::put('/update/{id}', [RefJenisPembayaranController::class, 'update']);
    Route::delete('/delete/{id}', [RefJenisPembayaranController::class, 'destroy']);
    Route::get('/search', [RefJenisPembayaranController::class, 'search']);
    Route::get('/export', [RefJenisPembayaranExportController::class, 'export']);
});

Route::prefix('export')->group(function () {
    Route::get('/jenis-tarif', [JenisTarifExportController::class, 'export']);
    Route::get('/jenis-tarif/export-pdf', [JenisTarifExportController::class, 'exportPdf']);
});

Route::prefix('laporan')->group(function () {
    Route::get('/pengeluaran', [LaporanPengeluaranController::class, 'pengeluaran']);
});

Route::get('/dashboard-bendahara', [DashboardBendaharaController::class, 'index']);


Route::get('/dashboard-kepsek', [DashboardKepsekKeuanganController::class, 'index']);

Route::get('/dashboardtimpenjaminanmutu', [DashboardTimPenjaminanMutuController::class, 'index']);

Route::get('/dashboard-kepsekrkt', [DashboardKepsekRKTController::class, 'index']);