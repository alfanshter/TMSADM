<?php

namespace App\Http\Controllers;

use App\Models\ItemMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemMachineController extends Controller
{
    public function index()
    {
        $data =  ItemMachine::all();
        return response()->json([
            'status' => 1,
            'message' => 'Machine created successfully.',
            'data' => $data
        ], 201);
    }

    public function store(Request $request)
    {
         // Validasi input
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'code' => 'required|string|unique:item_machines,code',
        'location' => 'required|string',
        'scope_of_work' => 'required|in:safety,production',
    ]);

    if ($validator->fails()) {
        // Ambil pesan error pertama
        $firstError = collect($validator->errors()->all())->first();

        return response()->json([
            'status' => 0,
            'message' => $firstError
        ], 422);
    }

    // Ambil data validasi yang lolos
    $validated = $validator->validated();

    $machine = ItemMachine::create($validated);

    return response()->json([
        'status' => 1,
        'message' => 'Machine created successfully.',
        'data' => $machine
    ], 201);
    }

    public function show($id)
    {
        $machine = ItemMachine::find($id);

        if (!$machine) {
            return response()->json([
                'status' => 0,
                'message' => 'Data mesin tidak ditemukan.',
                'data' => null
            ], 404);
        }
    
        return response()->json([
            'status' => 1,
            'message' => 'Data mesin berhasil ditemukan.',
            'data' => $machine
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $machine = ItemMachine::find($id);

        if (!$machine) {
            return response()->json([
                'status' => 0,
                'message' => 'Data mesin tidak ditemukan.',
                'data' => null
            ], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'code' => 'sometimes|string|unique:item_machines,code,' . $id,
            'location' => 'sometimes|string',
            'scope_of_work' => 'sometimes|in:safety,production',
        ]);
    
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
    
            return response()->json([
                'status' => 0,
                'message' => $firstError,
            ], 422);
        }
    
        $validated = $validator->validated();
        $machine->update($validated);
    
        return response()->json([
            'status' => 1,
            'message' => 'Data mesin berhasil diperbarui.',
            'data' => $machine
        ], 200);
    }

    public function destroy($id)
    {
        $machine = ItemMachine::findOrFail($id);
        $machine->delete();

        return response()->json(['message' => 'Machine deleted successfully.']);
    }
}
