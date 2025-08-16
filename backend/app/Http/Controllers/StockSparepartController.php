<?php

namespace App\Http\Controllers;

use App\Models\StockSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockSparepartController extends Controller
{
    public function index(Request $request)
    {
        $query = StockSparepart::query();

        // Filter kategori
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Filter lokasi
        if ($request->has('loc') && !empty($request->loc)) {
            $query->where('loc', 'LIKE', "%{$request->loc}%");
        }

        // Filter stok rendah (end_month_stock <= 5)
        if ($request->has('low_stock') && $request->low_stock == 1) {
            $query->where(DB::raw('(stok_awal + incoming - usage)'), '<=', 5);
        }

        $data = $query->orderBy('nama_sparepart', 'ASC')
                      ->get()
                      ->map(function($item) {
                          $item->end_month_stock = $item->stok_awal + $item->incoming - $item->usage;
                          return $item;
                      });

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'List spare parts retrieved successfully'
        ]);
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nama_sparepart' => 'required|string|max:255',
        'spec' => 'nullable|string|max:255',
        'loc' => 'required|string|max:255',
        'type' => 'nullable|string|max:255',
        'category' => 'required|in:Belting & House,Safety,Tools,Spare part & Cons',
        'remark' => 'required|string|max:50',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'data' => null,
            'message' => $validator->errors()
        ], 422);
    }

    $validated = $validator->validated();

    // set stok awal & incoming default
    $validated['incoming'] = 0;

    $sparepart = StockSparepart::create($validated);

    return response()->json([
        'status' => true,
        'data' => $sparepart,
        'message' => 'Spare part created successfully'
    ], 201);
}

    public function show($id)
    {
        $sparepart = StockSparepart::find($id);

        if (!$sparepart) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Spare part not found'
            ], 404);
        }

        $sparepart->end_month_stock = $sparepart->stok_awal + $sparepart->incoming - $sparepart->usage;

        return response()->json([
            'status' => true,
            'data' => $sparepart,
            'message' => 'Spare part retrieved successfully'
        ]);
    }


    public function update(Request $request, $id)
    {
        $sparepart = StockSparepart::find($id);

        if (!$sparepart) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Spare part not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_sparepart' => 'required|string|max:255',
            'spec' => 'nullable|string|max:255',
            'loc' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'category' => 'required|in:Belting & House,Safety,Tools,Spare part & Cons',
            'stok_awal' => 'required|integer|min:0',
            'incoming' => 'required|integer|min:0',
            'usage' => 'required|integer|min:0',
            'remark' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $validated['end_month_stock'] = $validated['stok_awal'] + $validated['incoming'] - $validated['usage'];

        $sparepart->update($validated);

        return response()->json([
            'status' => true,
            'data' => $sparepart,
            'message' => 'Spare part updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $sparepart = StockSparepart::find($id);

        if (!$sparepart) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Spare part not found'
            ], 404);
        }

        $sparepart->delete();

        return response()->json([
            'status' => true,
            'data' => null,
            'message' => 'Spare part deleted successfully'
        ]);
    }
}
