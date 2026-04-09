<?php

namespace App\Http\Controllers;

use App\Models\RefJenisPembayaran;
use Illuminate\Http\Request;
use Exception;

class RefJenisPembayaranController extends Controller
{
    // READ
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

    // CREATE
    public function store(Request $request)
{
    try {
        // Validasi input
        $request->validate([
            'deskripsi_jenis_pembayaran' => 'required|string|min:3|unique:ref_jenis_pembayaran,deskripsi_jenis_pembayaran'
        ]);

        // Simpan data
        $jenis = RefJenisPembayaran::create([
            'deskripsi_jenis_pembayaran' => $request->deskripsi_jenis_pembayaran
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


    // UPDATE
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'deskripsi_jenis_pembayaran' => 'required|string|unique:ref_jenis_pembayaran,deskripsi_jenis_pembayaran,'.$id.',id_jenis_pembayaran'
            ]);

            $jenis = RefJenisPembayaran::findOrFail($id);
            $jenis->update([
                'deskripsi_jenis_pembayaran' => $request->deskripsi_jenis_pembayaran
            ]);
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

    // DELETE
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
    
    //Search
    public function search(Request $request)
    {
        try {
            $keyword = $request->input('q'); // parameter pencarian, misalnya ?q=BCA

            $data = RefJenisPembayaran::where('deskripsi_jenis_pembayaran', 'like', '%'.$keyword.'%')->get();

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
}
    
