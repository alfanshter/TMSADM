<?php

namespace App\Http\Controllers;

use App\Models\StockSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockSparepartController extends Controller
{
    public function index()
    {
        $data = StockSparepart::latest()->get();

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
            'stok' => 'required|integer|min:0',
            'remark' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $validator->errors()
            ], 422);
        }

        $sparepart = StockSparepart::create($validator->validated());

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
            'stok' => 'required|integer|min:0',
            'remark' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $validator->errors()
            ], 422);
        }

        $sparepart->update($validator->validated());

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
