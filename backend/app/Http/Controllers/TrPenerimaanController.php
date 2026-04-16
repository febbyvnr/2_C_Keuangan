<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenerimaanRequest;
use App\Http\Requests\UpdatePenerimaanRequest;
use App\Models\TrPenerimaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrPenerimaanController extends Controller
{
    // F85 & F86: Menampilkan & Mencari
    public function index(Request $request)
    {
        $query = TrPenerimaan::query();

        if ($request->filled('search')) {
            $query->where('DESKRIPSI_TR_PENERIMAAN', 'like', "%{$request->search}%");
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    // F82: Menambah Penerimaan
    public function store(StorePenerimaanRequest $request)
    {
        DB::beginTransaction();
        try {
            // INSERT ke tr_penerimaan (Ini berhasil karena sudah auto-increment di DB kamu)
            $penerimaan = TrPenerimaan::create($request->validated());
            
            // LOGGING (F110) - Menangani ID Manual untuk activity_log
            $this->logActivity(
                'INSERT_PENERIMAAN', 
                'ID: ' . $penerimaan->ID_TR_PENERIMAAN, 
                'Menambah data penerimaan baru'
            );

            DB::commit();
            return response()->json(['success' => true, 'data' => $penerimaan], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // F83: Mengubah Penerimaan
    public function update(UpdatePenerimaanRequest $request, $id)
    {
        $penerimaan = TrPenerimaan::findOrFail($id);
        $penerimaan->update($request->validated());

        $this->logActivity('UPDATE_PENERIMAAN', 'ID: ' . $id, 'Mengubah data penerimaan');

        return response()->json(['success' => true, 'message' => 'Data diperbarui']);
    }

    // F84: Menghapus Penerimaan
    public function destroy($id)
    {
        TrPenerimaan::findOrFail($id)->delete();
        $this->logActivity('DELETE_PENERIMAAN', 'ID: ' . $id, 'Menghapus data penerimaan');

        return response()->json(['success' => true, 'message' => 'Data dihapus']);
    }

    /**
     * Helper untuk mencatat log dengan ID Manual (Handle DB Tanpa Auto Increment)
     */
    private function logActivity($activityName, $relatedData, $description)
    {
        // Step 1: Ambil ID terakhir dari activity_log secara manual
        $lastId = DB::table('activity_log')->max('ID_ACTIVITY_LOG') ?? 0;
        
        // Step 2: Insert dengan menyertakan ID baru (lastId + 1)
        DB::table('activity_log')->insert([
            'ID_ACTIVITY_LOG'      => $lastId + 1,
            'EVENT_TIME'           => now(),
            'ACTOR_USERNAME'       => 'Admin_Keuangan_Tester', // Sementara karena auth off
            'ACTIVITY_NAME'        => $activityName,
            'RELATED_DATA'         => $relatedData,
            'ACTIVITY_DESCRIPTION' => $description
        ]);
    }
}