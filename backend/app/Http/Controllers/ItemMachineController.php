<?php
namespace App\Http\Controllers;

use App\Models\ItemMachine;
use Illuminate\Http\Request;

class ItemMachineController extends Controller
{
    public function index()
    {
        return ItemMachine::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:item_machines,code',
            'location' => 'required|string',
            'scope_of_work' => 'required|in:safety,production',
        ]);

        $machine = ItemMachine::create($validated);

        return response()->json([
            'message' => 'Machine created successfully.',
            'data' => $machine
        ], 201);
    }

    public function show($id)
    {
        $machine = ItemMachine::findOrFail($id);
        return $machine;
    }

    public function update(Request $request, $id)
    {
        $machine = ItemMachine::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'code' => 'sometimes|string|unique:item_machines,code,' . $id,
            'location' => 'sometimes|string',
            'scope_of_work' => 'sometimes|in:safety,production',
        ]);

        $machine->update($validated);

        return response()->json([
            'message' => 'Machine updated successfully.',
            'data' => $machine
        ]);
    }

    public function destroy($id)
    {
        $machine = ItemMachine::findOrFail($id);
        $machine->delete();

        return response()->json(['message' => 'Machine deleted successfully.']);
    }
}
