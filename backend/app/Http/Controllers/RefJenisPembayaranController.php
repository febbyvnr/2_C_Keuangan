<?php

namespace App\Http\Controllers;

use App\Models\RefJenisPembayaran;
use Illuminate\Http\Request;
use Exception;

use App\Exports\RefJenisPembayaranExport;
use Maatwebsite\Excel\Facades\Excel;


class RefJenisPembayaranController extends Controller
{
    
    public function index()
    {
        try {
            $data = RefJenisPembayaran::all();
            return response()->json([
                'success' => true,
                'message' => 'Data ditemukan',
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage()
            ], 500);
        }
    }

    
    public function store(Request $request)
    {
        try {
            $request->validate([
                'DESKRIPSI_JENIS_PEMBAYARAN' => 'required|string|unique:ref_jenis_pembayaran,DESKRIPSI_JENIS_PEMBAYARAN'
            ]);

            $jenis = RefJenisPembayaran::create([
                'DESKRIPSI_JENIS_PEMBAYARAN' => $request->DESKRIPSI_JENIS_PEMBAYARAN
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditambahkan',
                'data' => $jenis
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data: '.$e->getMessage()
            ], 500);
        }
    }

    
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'DESKRIPSI_JENIS_PEMBAYARAN' => 
                    'required|string|unique:ref_jenis_pembayaran,DESKRIPSI_JENIS_PEMBAYARAN,' 
                    . $id . ',ID_JENIS_PEMBAYARAN'
            ]);

            $jenis = RefJenisPembayaran::findOrFail($id);
            $jenis->DESKRIPSI_JENIS_PEMBAYARAN = $request->DESKRIPSI_JENIS_PEMBAYARAN;
            $jenis->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $jenis
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan atau gagal update: '.$e->getMessage()
            ], 404);
        }
    }


    
    public function destroy($id)
    {
        try {
            $jenis = RefJenisPembayaran::findOrFail($id);
            $jenis->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan atau gagal hapus: '.$e->getMessage()
            ], 404);
        }
    }

    
    public function search(Request $request)
    {
        try {
            $keyword = $request->input('q');

            $data = RefJenisPembayaran::where('DESKRIPSI_JENIS_PEMBAYARAN', 'like', '%'.$keyword.'%')->get();

            return response()->json([
                'success' => true,
                'message' => 'Hasil pencarian',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        return Excel::download(
            new RefJenisPembayaranExport(),
            'ref_jenis_pembayaran.xlsx'
        );
    }
}
