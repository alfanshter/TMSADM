<?php

namespace App\Http\Controllers;

use App\Models\SparepartLog;
use App\Models\TmsSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Validasi gagal.',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Cek apakah item sudah ada
            $existing = TmsSparepart::where('activity_tms_id', $validated['activity_tms_id'])
                ->where('stock_sparepart_id', $validated['stock_sparepart_id'])
                ->first();

            if ($existing) {
                // Update qty (tambah)
                $addedQty = $validated['qty'];
                $existing->qty += $addedQty;
                $existing->save();
                $sparepart = $existing;
                $message = 'Stok sparepart berhasil diperbarui.';
            } else {
                // Insert baru
                $addedQty = $validated['qty'];
                $sparepart = TmsSparepart::create($validated);
                $message = 'Sparepart berhasil ditambahkan.';
            }

            // ✅ Catat log pemakaian (usage)
            SparepartLog::create([
                'stock_sparepart_id' => $validated['stock_sparepart_id'],
                'user_id'            => Auth::id(),
                'action'             => 'usage',
                'qty'                => $addedQty,
                'keterangan'         => 'Digunakan pada Activity TMS #' . $validated['activity_tms_id'],
            ]);

            return response()->json([
                'status'  => 1,
                'message' => $message,
                'data'    => $sparepart
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Terjadi kesalahan pada server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $tmsSparepart = TmsSparepart::find($id);

        if (!$tmsSparepart) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Sparepart tidak ditemukan.'
            ], 404);
        }

        // ✅ Catat log pembatalan pemakaian
        SparepartLog::create([
            'stock_sparepart_id' => $tmsSparepart->stock_sparepart_id,
            'user_id'            => Auth::id(),
            'action'             => 'usage_cancelled',
            'qty'                => $tmsSparepart->qty,
            'keterangan'         => 'Pemakaian dibatalkan dari Activity TMS #' . $tmsSparepart->activity_tms_id,
        ]);

        $tmsSparepart->delete();

        return response()->json([
            'status' => true,
            'data' => null,
            'message' => 'Sparepart berhasil dihapus.'
        ]);
    }
}
