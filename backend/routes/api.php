<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MstCoaController;
use App\Http\Controllers\MstKegiatanController;

use App\Http\Controllers\RefTahunAnggaranController;
use App\Http\Controllers\DtlFpdController;
use App\Http\Controllers\FpdAnggaranController;
use App\Http\Controllers\RefSumberDanaController;
use App\Http\Controllers\TrPmController;
use App\Http\Controllers\RefPmController;

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