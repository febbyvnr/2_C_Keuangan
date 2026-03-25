<?php

namespace App\Http\Controllers;

use App\Models\RefTan;
use Illuminate\Http\Request;

class RefTanController extends Controller
{
    public function index()
    {
        return response()->json(RefTan::all());
    }

    public function show($id)
    {
        $data = RefTan::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        return response()->json($data);
    }

    public function search(Request $request)
    {
        $query = RefTan::query();
        if ($request->TAHUN) {
            $query->where('TAHUN', $request->TAHUN);
        }
        if ($request->IS_CURRENT !== null) {
            $query->where('IS_CURRENT', $request->IS_CURRENT);
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'TAHUN' => 'required|integer|unique:REF_TAN,TAHUN',
            'IS_CURRENT' => 'required|boolean',
            'DESKRIPSI_TAN' => 'required'
        ],[
            'TAHUN.unique' => 'Tahun akademik sudah ada.',
            'TAHUN.required' => 'Tahun wajib diisi.'
        ]);
        $lastId = RefTan::max('ID_TAN');
        $newId = $lastId ? $lastId + 1 : 1;
        $data = RefTan::create([
            'ID_TAN' => $newId,
            'TAHUN' => $request->TAHUN,
            'IS_CURRENT' => $request->IS_CURRENT,
            'DESKRIPSI_TAN' => $request->DESKRIPSI_TAN
        ]);

        return response()->json($data,201);
    }

    public function update(Request $request, $id)
    {
        $data = RefTan::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        if ($data->IS_CURRENT == 1) {
            return response()->json(['message' => 'Tahun aktif tidak boleh diubah'], 400);
        }
        $request->validate([
            'TAHUN' => 'required|integer|unique:REF_TAN,TAHUN,' . $id . ',ID_TAN',
            'IS_CURRENT' => 'required|boolean',
            'DESKRIPSI_TAN' => 'required'
        ], [
            'TAHUN.unique' => 'Tahun akademik sudah ada.',
            'TAHUN.required' => 'Tahun wajib diisi.'
        ]);
        $data->update($request->only([
            'TAHUN',
            'IS_CURRENT',
            'DESKRIPSI_TAN'
        ]));
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = RefTan::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        // if ($data->tagihan()->exists()) {
        //     return response()->json([
        //         'message' => 'Data tidak dapat dihapus karena sudah dipakai di tagihan/pembayaran siswa'
        //     ], 400);
        // }
        $data->delete();
        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }
}