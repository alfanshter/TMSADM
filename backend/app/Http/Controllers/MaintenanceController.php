<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityTms;
use App\Models\MaintenanceType;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    // 1. Get activity TMS by id_itemmachine and date
    public function getActivityTms(Request $request)
    {
        $request->validate([
            'item_machine_id' => 'required|integer',
            'date' => 'required|date'
        ]);

        $activity = ActivityTms::where('item_machine_id', $request->item_machine_id)
                    ->whereDate('date', $request->date)
                    ->with('itemMachine') // kalau ada relasi
                    ->get();

        return response()->json($activity);
    }


public function storeActivityTms(Request $request)
{
    $request->validate([
        'item_machine_id' => 'required|exists:item_machines,id',
        'date' => 'required|date',
    ]);

    $activity = ActivityTms::create([
        'item_machine_id' => $request->item_machine_id,
        'date' => $request->date,
    ]);

    return response()->json([
        'message' => 'Activity TMS berhasil ditambahkan',
        'data' => $activity,
    ], 201);
}

    // 2. Get available maintenance types
    public function getMaintenanceTypes()
    {
        $types = MaintenanceType::all(['id', 'maintenance_type']);
        return response()->json($types);
    }

    // 3. Store foto before, after, and JSA file
    public function storeMaintenance(Request $request)
    {
        $request->validate([
            'tms_id' => 'required|integer',
            'maintenance_type' => 'required|in:Cleaning Critical,Just Cleaning,Replacement Part,Preventive (PM)',
            'foto_before' => 'nullable|image|mimes:jpg,jpeg,png',
            'foto_after' => 'nullable|image|mimes:jpg,jpeg,png',
            'jsa_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls'
        ]);

        $data = [
            'tms_id' => $request->tms_id,
            'maintenance_type' => $request->maintenance_type
        ];

        if ($request->hasFile('foto_before')) {
            $data['foto_before_' . strtolower(str_replace(' ', '_', $request->maintenance_type))] = $request->file('foto_before')->store('uploads/before', 'public');
        }

        if ($request->hasFile('foto_after')) {
            $data['foto_after_' . strtolower(str_replace(' ', '_', $request->maintenance_type))] = $request->file('foto_after')->store('uploads/after', 'public');
        }

        if ($request->hasFile('jsa_file')) {
            $data['jsa_file_' . strtolower(str_replace(' ', '_', $request->maintenance_type))] = $request->file('jsa_file')->store('uploads/jsa', 'public');
        }

        $maintenance = MaintenanceType::create($data);

        return response()->json([
            'message' => 'Maintenance berhasil disimpan',
            'data' => $maintenance
        ]);
    }
}
