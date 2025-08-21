<?php

namespace App\Http\Controllers;

use App\Models\TmsSparepart;
use Illuminate\Http\Request;

class TmsSparepartController extends Controller
{
    // List all reports
    public function index()
    {
        $data = TmsSparepart::latest()->get();
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'List tms sparepart retrieved successfully'
        ]);
    }

    function destroy($id)
    {

     
        // Hapus data
        $deleted = TmsSparepart::where('id', $id)->delete();

        
        // Cek apakah berhasil dihapus
        if ($deleted) {
            return response()->json([
                'status' => true,
                'data' => null,
                'message' => 'Sparepart berhasil dihapus.'
            ]);
        }

        return response()->json([
            'status' => false,
            'data' => null,
            'message' => 'Gagal menghapus spareparts.'
        ], 500);
    }
}
