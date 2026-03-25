<?php

namespace App\Http\Controllers;

use App\Models\RefTan;
use Illuminate\Http\Request;

class RefTanController extends Controller
{
    // fetch all data - get
    public function index()
    {
        return response()->json(RefTan::all());
    }

    // show by id - get
    public function show($id)
    {
        $data = RefTan::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        return response()->json($data);
    }

    //search by TAHUN - get
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

    // add - post
    public function store(Request $request)
    {
        $request->validate([
            'TAHUN'=>'required|integer',
            'IS_CURRENT'=>'required|boolean',
            'DESKRIPSI_TAN'=>'required'
        ]);
        $data = RefTan::create($request->all());
        return response()->json($data,201);
    }

    // update - put
    public function update(Request $request,$id)
    {
        $data = RefTan::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        $data->update($request->all());
        return response()->json($data);
    }

    // del - destroy
    public function destroy($id)
    {
        $data = RefTan::find($id);
        if(!$data){
            return response()->json(['message'=>'Data tidak ditemukan'],404);
        }
        $data->delete();
        return response()->json(['message'=>'Data berhasil dihapus']);
    }
}