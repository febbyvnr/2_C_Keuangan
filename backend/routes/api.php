<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MstCoaController;
use App\Http\Controllers\MstKegiatanController;

use App\Http\Controllers\RefTahunAnggaranController;
use App\Http\Controllers\RefTanController;

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

Route::prefix('ref-tan')->group(function () {
    Route::get('/', [RefTanController::class, 'index']);
    Route::get('/current', [RefTanController::class, 'current']);
    Route::get('/{id}', [RefTanController::class, 'show']);
    Route::post('/store', [RefTanController::class, 'store']);
    Route::put('/update/{id}', [RefTanController::class, 'update']);
    Route::delete('/delete/{id}', [RefTanController::class, 'destroy']);
});