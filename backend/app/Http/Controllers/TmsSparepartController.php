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

    function delete(Request $request)
    {

        // Validasi input
        $request->validate([
            'id' => 'required|integer|exists:tms_spareparts,id',
        ]);

        // Hapus data
        $deleted = TmsSparepart::where('id', $request->id)->delete();

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
            'message' => 'Gagal menghapus sparepart.'
        ], 500);
    }
}
