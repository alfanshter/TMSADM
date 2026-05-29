<?php

namespace App\Http\Controllers;

use App\Models\FawReport;
use App\Models\FawReportPhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FawReportController extends Controller
{
    public function index(Request $request)
    {

        // ambil bulan dari request, kalau kosong pakai bulan sekarang
        $yearOrMonth = $request->get('month'); // biarkan null kalau tidak dikirim


        $reports = FawReport::with('photos')
            ->when($yearOrMonth, function ($query, $yearOrMonth) {
                // Jika month dikirim, pecah jadi tahun & bulan
                [$year, $month] = explode('-', $yearOrMonth);
                $query->whereYear('date', $year)
                    ->whereMonth('date', $month);
            })
            ->latest()->get();
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
            'result' => 'required|string',
            'date' => 'required|date',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);



        $report = FawReport::create($validated);

        // Upload multiple photos
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

        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|required|string',
            'result'      => 'nullable|string',
            'date'        => 'sometimes|required|date',

            // foto lama → ID array
            'photos_old'  => 'array',
            'photos_old.*' => 'integer|exists:faw_report_photos,id',

            // foto baru
            'photos_new.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $data = $validator->validated();

        // Update field dasar
        if (isset($data['description'])) {
            $report->description = $data['description'];
        }
        if (isset($data['result'])) {
            $report->result = $data['result'];
        }
        if (isset($data['date'])) {
            $report->date = $data['date'];
        }
        $report->save();

        // -----------------------------
        // Handle Photos
        // -----------------------------
        $photosOld = $request->input('photos_old', []);

        // Hapus foto lama yang tidak ada di "photos_old"
        $report->photos()
            ->whereNotIn('id', $photosOld)
            ->get()
            ->each(function ($photo) {
                Storage::disk('public')->delete($photo->photo_path);
                $photo->delete();
            });

        // Simpan foto baru
        if ($request->hasFile('photos_new')) {
            foreach ($request->file('photos_new') as $file) {
                $path = $file->store('faw_reports', 'public');
                $report->photos()->create([
                    'photo_path' => $path,
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

    public function export(Request $request)
    {


        // Ambil bulan dari request (format: YYYY-MM)
        $month = $request->get('month') ?? Carbon::now()->format('Y-m');

        // ✅ Pecah jadi tahun dan bulan
        [$year, $monthNumber] = explode('-', $month);

        // Nama file unik berdasarkan tanggal
        $fileName = 'faw_reports_' . \Carbon\Carbon::now()->format('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\FawReportExport, $fileName);

        // Ambil data laporan + foto, dan mapping URL foto
        $reports = \App\Models\FawReport::with('photos')
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNumber)

            ->get()->map(function ($report) {
                $report->photos->transform(function ($photo) {
                    $photo->photo_url = url('storage/' . $photo->photo_path);
                    return $photo;
                });
                return $report;
            });

        // Simpan ke storage/app/public/exports
        \Maatwebsite\Excel\Facades\Excel::store(
            new \App\Exports\FawReportExport,
            'exports/' . $fileName,
            'public'
        );

        // Buat URL public untuk file Excel
        $fileUrl = \Illuminate\Support\Facades\Storage::url('exports/' . $fileName);

        return response()->json([
            'status'  => 1,
            'message' => 'Export generated successfully',
            'url'     => asset($fileUrl),
            'data'    => $reports
        ]);
    }
}
