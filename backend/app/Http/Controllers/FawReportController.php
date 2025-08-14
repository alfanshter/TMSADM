<?php

namespace App\Http\Controllers;

use App\Models\FawReport;
use App\Models\FawReportPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FawReportController extends Controller
{
    public function index()
    {
        $reports = FawReport::with('photos')->latest()->get();
        return response()->json([
            'status' => 1,
            'message' => 'Data fetched successfully',
            'data' => $reports
        ]);
    }

 public function store(Request $request)
{
    $validated = $request->validate([
        'description' => 'required|string',
        'result' => 'required|string', // tambahkan validasi
        'date' => 'required|date',
        'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $report = FawReport::create($validated);

    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('faw_reports', 'public');
            FawReportPhoto::create([
                'faw_report_id' => $report->id,
                'photo_path' => $path
            ]);
        }
    }

    return response()->json([
        'status' => 1,
        'message' => 'FAW Report created successfully',
        'data' => $report->load('photos')
    ], 201);
}

    public function show($id)
    {
        $report = FawReport::with('photos')->find($id);

        if (!$report) {
            return response()->json([
                'status' => 0,
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Data fetched successfully',
            'data' => $report
        ]);
    }

    public function update(Request $request, $id)
{
    $report = FawReport::find($id);

    if (!$report) {
        return response()->json([
            'status' => 0,
            'message' => 'Data not found',
            'data' => null
        ], 404);
    }

    $validated = $request->validate([
        'description' => 'sometimes|required|string',
        'result' => 'sometimes|required|string', // tambahkan validasi
        'date' => 'sometimes|required|date',
        'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $report->update($validated);

    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('faw_reports', 'public');
            FawReportPhoto::create([
                'faw_report_id' => $report->id,
                'photo_path' => $path
            ]);
        }
    }

    return response()->json([
        'status' => 1,
        'message' => 'FAW Report updated successfully',
        'data' => $report->load('photos')
    ]);
}

    public function destroy($id)
    {
        $report = FawReport::find($id);

        if (!$report) {
            return response()->json([
                'status' => 0,
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        foreach ($report->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $report->delete();

        return response()->json([
            'status' => 1,
            'message' => 'FAW Report deleted successfully',
            'data' => null
        ]);
    }
}
