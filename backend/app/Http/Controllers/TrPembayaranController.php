<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrPembayaran;
use App\Models\TagihanSiswa;

class TrPembayaranController extends Controller
{
    public function index()
    {
        //tunggu User.php
        // if(auth()->user()->ROLE == 'siswa'){
        //     $pembayaran = TrPembayaran::where('ID_SISWA', auth()->user()->id_siswa)->get();
        // } else {
            $pembayaran = TrPembayaran::all();
        // }
        return response()->json($pembayaran);
    }

    public function show($id)
    {
        $pembayaran = TrPembayaran::find($id);
        if (!$pembayaran) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        return response()->json($pembayaran);
    }

    public function store(Request $request)
    {
        $tagihan = TagihanSiswa::find($request->ID_TAGIHAN_SISWA);
        if($tagihan && $request->JUMLAH_BAYAR > $tagihan->JUMLAH_TAGIHAN_SISWA){
            //masi bingung ini, hrsnya pke SISA_TAGIHAN_SISWA, adanya JUMLAH_TAGIHAN_SISWA
            return response()->json(['message' => 'Jumlah bayar melebihi sisa tagihan'], 400);
        }
        $data = $request->validate([
            'ID_SISWA_TETAP' => 'nullable|integer',
            'KODE_TA' => 'nullable|integer',
            'ID_JENIS_PEMBAYARAN' => 'nullable|integer',
            'ID_TAGIHAN_SISWA' => 'nullable|integer',
            'REF_ID_JENIS_PEMBAYARAN' => 'nullable|integer',
            'TGL_BAYAR' => 'nullable|date',
            'JUMLAH_BAYAR' => 'nullable|numeric',
            'LINK_BUKTI_BAYAR' => 'nullable|string|max:255',
            'NIP_VALIDATOR_PEMBAYARAN' => 'nullable|string|max:20',
        ]);
        $lastId = TrPembayaran::max('ID_PEMBAYARAN');
        $newId = $lastId ? $lastId + 1 : 1;
        $data['ID_PEMBAYARAN'] = $newId;
        $pembayaran = TrPembayaran::create($data);
        return response()->json($pembayaran, 201);
    }

    public function update(Request $request, $id)
    {
        $pembayaran = TrPembayaran::find($id);
        if (!$pembayaran) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        $data = $request->validate([
            'ID_SISWA_TETAP' => 'nullable|integer',
            'KODE_TA' => 'nullable|integer',
            'ID_JENIS_PEMBAYARAN' => 'nullable|integer',
            'ID_TAGIHAN_SISWA' => 'nullable|integer',
            'REF_ID_JENIS_PEMBAYARAN' => 'nullable|integer',
            'TGL_BAYAR' => 'nullable|date',
            'JUMLAH_BAYAR' => 'nullable|numeric',
            'LINK_BUKTI_BAYAR' => 'nullable|string|max:255',
            'NIP_VALIDATOR_PEMBAYARAN' => 'nullable|string|max:20',
        ]);
        $pembayaran->update($data);
        return response()->json($pembayaran);
    }

    public function destroy($id)
    {
        $pembayaran = TrPembayaran::find($id);
        if (!$pembayaran) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        $pembayaran->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function verify(Request $request, $id)
    {
        $pembayaran = TrPembayaran::find($id);
        if(!$pembayaran) return response()->json(['message' => 'Data tidak ditemukan'], 404);
        $pembayaran->NIP_VALIDATOR_PEMBAYARAN = auth()->user()->nip;
        $pembayaran->status = 'terverifikasi';
        $pembayaran->save();
        return response()->json($pembayaran);
    }
}