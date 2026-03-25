<?php

namespace App\Http\Controllers;

use App\Models\RefSumberDana;
use Illuminate\Http\Request;

class RefSumberDanaController extends Controller
{
    public function index()
    {
        return response()->json(RefSumberDana::all());
    }

    public function show($id)
    {
        $data = RefSumberDana::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        return response()->json($data);
    }

    public function search(Request $request)
    {
        $query = RefSumberDana::query();
        if ($request->REF_ID_REF_DANA !== null) {
            $query->where('REF_ID_REF_DANA', $request->REF_ID_REF_DANA);
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'REF_ID_REF_DANA' => 'nullable|integer'
        ]);
        $lastId = RefSumberDana::max('ID_REF_DANA');
        $newId = $lastId ? $lastId + 1 : 1;
        $data = RefSumberDana::create([
            'ID_REF_DANA' => $newId,
            'REF_ID_REF_DANA' => $request->REF_ID_REF_DANA
        ]);
        return response()->json($data,201);
    }

    public function update(Request $request, $id)
    {
        $data = RefSumberDana::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        $data->update([
            'REF_ID_REF_DANA' => $request->REF_ID_REF_DANA
        ]);
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = RefSumberDana::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        $data->delete();
        return response()->json(['message'=>'Data berhasil dihapus']);
    }
}