<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RbacController;
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
use Termwind\Components\Raw;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('rbac')
    ->middleware(['jabatan:Admin Sistem'])
    ->group(function () {
        // daftar master role/jabatan
        Route::get('/jabatan', [RbacController::class, 'listJabatan']);

        // daftar master menu/permission
        Route::get('/menu', [RbacController::class, 'listMenu']);

        // lihat mapping jabatan -> menu
        Route::get('/jabatan-menu', [RbacController::class, 'listJabatanMenu']);

        // tambah 1 hak akses menu ke jabatan
        Route::post('/jabatan-menu', [RbacController::class, 'assignMenuToJabatan']);

        // cabut 1 hak akses menu dari jabatan
        Route::delete('/jabatan-menu/{idHakAkses}', [RbacController::class, 'revokeMenuFromJabatan']);

        // sinkronisasi full menu untuk 1 jabatan
        Route::post('/jabatan-menu/sync', [RbacController::class, 'syncJabatanMenu']);
    });

Route::prefix('coa')->group(function () {
    Route::get('/', [MstCoaController::class, 'index']);
    Route::get('/parents', [MstCoaController::class, 'parents']);
    Route::get('/{id}', [MstCoaController::class, 'show']);
    Route::post('/store', [MstCoaController::class, 'store']);
    Route::put('/update/{id}', [MstCoaController::class, 'update']);
    Route::delete('/delete/{id}', [MstCoaController::class, 'destroy']);
});


Route::prefix('kegiatan')->group(function () {
    Route::get('/', [MstKegiatanController::class, 'index']);
    Route::get('/parents', [MstKegiatanController::class, 'parents']);
    Route::get('/{id}', [MstKegiatanController::class, 'show']);
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
    Route::get('/{id}', [MstProgramKerjaController::class, 'show']);
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
    Route::get('/by-jenis/{idJenis}', [RefTarifController::class, 'byJenis']);
    Route::get('/by-tahun/{idTahun}', [RefTarifController::class, 'byTahun']);
    Route::get('/{idJenis}/{idTahun}', [RefTarifController::class, 'show']);
    Route::post('/store', [RefTarifController::class, 'store']);
    Route::put('/update', [RefTarifController::class, 'update']);
    Route::delete('/delete', [RefTarifController::class, 'destroy']);
});