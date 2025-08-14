<?php

namespace App\Http\Controllers;

use App\Models\LeakageReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LeakageReportController extends Controller
{
     // List all reports
     public function index()
     {
         $data = LeakageReport::latest()->get();
         return response()->json([
             'status' => true,
             'data' => $data,
             'message' => 'List leakage reports retrieved successfully'
         ]);
     }

      // Create new report
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_scan' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'lokasi' => 'required|string|max:255',
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('file_scan')) {
            $path = $request->file('file_scan')->store('leakage_scans', 'public');
        }

        $report = LeakageReport::create([
            'file_scan' => $path,
            'lokasi' => $request->lokasi,
            'date' => $request->date
        ]);

        return response()->json([
            'status' => true,
            'data' => $report,
            'message' => 'Leakage report created successfully'
        ], 201);
    }

    // Show specific report
    public function show($id)
    {
        $report = LeakageReport::find($id);

        if (!$report) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Leakage report not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $report,
            'message' => 'Leakage report retrieved successfully'
        ]);
    }

    // Update report
    public function update(Request $request, $id)
    {
        $report = LeakageReport::find($id);

        if (!$report) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Leakage report not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'file_scan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'lokasi' => 'required|string|max:255',
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('file_scan')) {
            if ($report->file_scan && Storage::disk('public')->exists($report->file_scan)) {
                Storage::disk('public')->delete($report->file_scan);
            }
            $path = $request->file('file_scan')->store('leakage_scans', 'public');
            $report->file_scan = $path;
        }

        $report->lokasi = $request->lokasi;
        $report->date = $request->date;
        $report->save();

        return response()->json([
            'status' => true,
            'data' => $report,
            'message' => 'Leakage report updated successfully'
        ]);
    }

    // Delete report
    public function destroy($id)
    {
        $report = LeakageReport::find($id);

        if (!$report) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Leakage report not found'
            ], 404);
        }

        if ($report->file_scan && Storage::disk('public')->exists($report->file_scan)) {
            Storage::disk('public')->delete($report->file_scan);
        }

        $report->delete();

        return response()->json([
            'status' => true,
            'data' => null,
            'message' => 'Leakage report deleted successfully'
        ]);
    }
}
