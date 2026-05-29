<?php

namespace App\Http\Controllers;

use App\Models\TmsSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'activity_tms_id'    => 'required|integer|exists:activity_tms,id',
                'stock_sparepart_id' => 'required|integer|exists:stock_spareparts,id',
                'qty'                => 'required|integer|min:1',
            ]);

            // Jika validasi gagal
            if ($validator->fails()) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Validasi gagal.',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Cek apakah item sudah ada
            $sparepart = TmsSparepart::where('activity_tms_id', $validated['activity_tms_id'])
                ->where('stock_sparepart_id', $validated['stock_sparepart_id'])
                ->first();

            if ($sparepart) {
                // Update stok (tambah qty)
                $sparepart->qty += $validated['qty'];
                $sparepart->save();

                $message = 'Stok sparepart berhasil diperbarui.';
            } else {
                // Insert baru
                $sparepart = TmsSparepart::create($validated);
                $message = 'Sparepart berhasil ditambahkan.';
            }

            return response()->json([
                'status'  => 1,
                'message' => 'Sparepart berhasil ditambahkan.',
                'data'    => $sparepart
            ], 201);
        } catch (\Exception $e) {
            // Jika ada error tak terduga
            return response()->json([
                'status'  => 0,
                'message' => 'Terjadi kesalahan pada server.',
                'error'   => $e->getMessage()
            ], 500);
        }
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
