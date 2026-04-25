<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\RefPenerimaanController;
use App\Http\Controllers\TrCicilanController;
use App\Http\Controllers\TrPembayaranController;
use App\Http\Controllers\EvaluasiRktController;
use App\Http\Controllers\TagihanSiswaController;
use App\Http\Controllers\LaporanPenerimaanController;
use App\Http\Controllers\RefJenisPembayaranController;
use App\Http\Controllers\JenisTarifExportController;
use App\Http\Controllers\LaporanRkasController;
use App\Http\Controllers\LaporanKeuanganYayasanController;

use Termwind\Components\Raw;
use App\Http\Controllers\RkaController;
use App\Http\Controllers\LaporanBukuKhasUmumController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('coa')->group(function () {
    Route::get('/', [MstCoaController::class, 'index']);
    Route::get('/parents', [MstCoaController::class, 'parents']);

    Route::get('/export', [MstCoaController::class, 'export']);
    Route::get('/export/excel', [MstCoaController::class, 'exportExcel']);
    Route::get('/export/csv', [MstCoaController::class, 'exportCsv']);
    Route::get('/export/pdf', [MstCoaController::class, 'exportPdf']);

    Route::get('/{id}', [MstCoaController::class, 'show'])->whereNumber('id');
    Route::post('/store', [MstCoaController::class, 'store']);
    Route::put('/update/{id}', [MstCoaController::class, 'update']);
    Route::delete('/delete/{id}', [MstCoaController::class, 'destroy']);
});


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

    Route::get('/{id}', [MstProgramKerjaController::class, 'show'])->whereNumber('id');
    Route::post('/store', [MstProgramKerjaController::class, 'store']);
    Route::put('/update/{id}', [MstProgramKerjaController::class, 'update']);
    Route::delete('/delete/{id}', [MstProgramKerjaController::class, 'destroy']);
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
    Route::get('/export/{id}', [FpdAnggaranController::class, 'export']);
    Route::get('/{id}', [FpdAnggaranController::class, 'show']);
    Route::post('/store', [FpdAnggaranController::class, 'store']);
    Route::put('/update/{id}', [FpdAnggaranController::class, 'update']);
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

Route::prefix('ref-pm')->group(function () {
    Route::get('/', [RefPmController::class, 'index']);
    Route::get('/search', [RefPmController::class, 'search']);
    Route::get('/{id}', [RefPmController::class, 'show']);
    Route::post('/store', [RefPmController::class, 'store']);
    Route::put('/update/{id}', [RefPmController::class, 'update']);
    Route::delete('/delete/{id}', [RefPmController::class, 'destroy']);
});

Route::prefix('jenis-tarif')->group(function () {
    Route::get('/', [RefJenisTarifController::class, 'index']);
    Route::get('/search', [RefJenisTarifController::class, 'search']);
    Route::get('/{id}', [RefJenisTarifController::class, 'show']);
    Route::post('/store', [RefJenisTarifController::class, 'store']);
    Route::put('/update/{id}', [RefJenisTarifController::class, 'update']);
    Route::delete('/delete/{id}', [RefJenisTarifController::class, 'destroy']);
});

Route::prefix('tarif')->group(function () {
       Route::get('/', [RefTarifController::class, 'index']);
       Route::get('/search', [RefTarifController::class, 'search']);
       Route::get('/by-jenis/{idJenis}', [RefTarifController::class, 'byJenis']);
       Route::get('/by-tahun/{idTahun}', [RefTarifController::class, 'byTahun']);
       Route::get('/detail/{id}', [RefTarifController::class, 'showById']);
       Route::get('/{idJenis}/{idTahun}', [RefTarifController::class, 'show']);
       Route::post('/store', [RefTarifController::class, 'store']);
       Route::put('/update/{idJenis}/{idTahun}', [RefTarifController::class, 'update']);
       Route::delete('/delete/{idJenis}/{idTahun}', [RefTarifController::class, 'destroy']);
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
});

Route::prefix('tagihan-siswa')->group(function () {
    Route::get('/', [TagihanSiswaController::class, 'index']);
    Route::get('/search', [TagihanSiswaController::class, 'search']);

    Route::get('/export', [TagihanSiswaController::class, 'export']);
    Route::get('/export/excel', [TagihanSiswaController::class, 'exportExcel']);
    Route::get('/export/csv', [TagihanSiswaController::class, 'exportCsv']);
    Route::get('/export/pdf', [TagihanSiswaController::class, 'exportPdf']);

    Route::get('/{id}', [TagihanSiswaController::class, 'show']);

    Route::post('/store', [TagihanSiswaController::class, 'store']);
    Route::put('/update/{id}', [TagihanSiswaController::class, 'update']);
    Route::delete('/delete/{id}', [TagihanSiswaController::class, 'destroy']);
});

Route::prefix('laporan')->group(function () {
    Route::get('/penerimaan', [LaporanPenerimaanController::class, 'penerimaan']);
    Route::post('/rkas/export', [LaporanRkasController::class, 'export']);
    Route::post('/rkas/export-pdf', [LaporanRkasController::class, 'exportPdf']);
    Route::get('/yayasan/export-excel', [LaporanKeuanganYayasanController::class, 'exportExcel']);
    Route::get('/yayasan/export-pdf', [LaporanKeuanganYayasanController::class, 'exportPdf']);
});

Route::prefix('laporan')->group(function () {
    Route::get('/bku', [LaporanBukuKhasUmumController::class, 'bku']);

});

Route::prefix('rka')->group(function () {
    Route::get('/', [RkaController::class, 'index']);
    Route::get('/search', [RkaController::class, 'search']);
    Route::get('/{id}', [RkaController::class, 'show']);
    Route::post('/store', [RkaController::class, 'store']);
    Route::put('/update/{id}', [RkaController::class, 'update']);
    Route::delete('/delete/{id}', [RkaController::class, 'destroy']);
});

Route::prefix('jenis-pembayaran')->group(function () {
    Route::get('/', [RefJenisPembayaranController::class, 'index']);
    Route::post('/', [RefJenisPembayaranController::class, 'store']);
    Route::put('/{id}', [RefJenisPembayaranController::class, 'update']);
    Route::delete('/{id}', [RefJenisPembayaranController::class, 'destroy']);
    Route::get('/search', [RefJenisPembayaranController::class, 'search']);
    
    Route::get('/export', [RefJenisPembayaranController::class, 'export']);
});

Route::prefix('export')->group(function () {
    Route::get('/jenis-tarif', [JenisTarifExportController::class, 'export']);
    Route::get('/jenis-tarif/export-pdf', [JenisTarifExportController::class, 'exportPdf']);
});
