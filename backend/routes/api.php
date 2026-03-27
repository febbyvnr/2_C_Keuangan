<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MstCoaController;
use App\Http\Controllers\MstKegiatanController;
use App\Http\Controllers\RefTahunAnggaranController;
use App\Http\Controllers\RefPenerimaanController;
use App\Http\Controllers\TrCicilanController;
use App\Http\Controllers\TrPembayaranController;
use Termwind\Components\Raw;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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