<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MstCoaController;
use App\Http\Controllers\MstKegiatanController;
use App\Http\Controllers\RefJenisTarifController;
use App\Http\Controllers\RefTarifController;
use App\Http\Controllers\RefTahunAnggaranController;

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

