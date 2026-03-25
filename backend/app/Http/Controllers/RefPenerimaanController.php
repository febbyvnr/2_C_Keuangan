<?php

namespace App\Http\Controllers;

use App\Models\RefPenerimaan;
use Illuminate\Http\Request;

class RefPenerimaanController extends Controller
{
    public function index()
    {
        return response()->json(RefPenerimaan::all());
    }

    public function show($id)
    {
        $data = RefPenerimaan::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        return response()->json($data);
    }

    //searc by desc dan id ref, klo id dari show(id)atas
    public function search(Request $request)
    {
        $query = RefPenerimaan::query();
        if ($request->DESKRIPSI_REF_PENERIMAAN) {
            $query->where('DESKRIPSI_REF_PENERIMAAN','like','%'.$request->DESKRIPSI_REF_PENERIMAAN.'%');
        }
        if ($request->REF_ID_REF_PENERIMAAN !== null) {
            $query->where('REF_ID_REF_PENERIMAAN',$request->REF_ID_REF_PENERIMAAN);
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'REF_ID_REF_PENERIMAAN' => 'nullable|integer',
            'DESKRIPSI_REF_PENERIMAAN' => 'required'
        ]);
        $lastId = RefPenerimaan::max('ID_REF_PENERIMAAN');
        $newId = $lastId ? $lastId + 1 : 1;
        $data = RefPenerimaan::create([
            'ID_REF_PENERIMAAN' => $newId,
            'REF_ID_REF_PENERIMAAN' => $request->REF_ID_REF_PENERIMAAN,
            'DESKRIPSI_REF_PENERIMAAN' => $request->DESKRIPSI_REF_PENERIMAAN
        ]);
        return response()->json($data,201);
    }

    public function update(Request $request,$id)
    {
        $data = RefPenerimaan::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        $data->update([
            'REF_ID_REF_PENERIMAAN' => $request->REF_ID_REF_PENERIMAAN,
            'DESKRIPSI_REF_PENERIMAAN' => $request->DESKRIPSI_REF_PENERIMAAN
        ]);
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = RefPenerimaan::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        $data->delete();
        return response()->json(['message'=>'Data berhasil dihapus']);
    }
}