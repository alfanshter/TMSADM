<?php

namespace App\Http\Controllers;

use App\Models\ActivityTMS;
use App\Models\CleaningCritical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ActivityTmsController extends Controller
{
    public function getAllActivityTms()
    {
        $activities = ActivityTMS::with('itemMachine')
            ->with('cleaningCriticals')
            ->with('justCleaning')
            ->with('preventive')
            ->with('replacementPart')
            ->get();

        if ($activities->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Belum ada data aktivitas TMS.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Berhasil mengambil semua data aktivitas TMS.',
            'data' => $activities
        ], 200);
    }


    public function getActivityTmsById($id)
    {

        $activity = ActivityTMS::with([
            'itemMachine',
            'cleaningCriticals',
            'justCleaning',
            'preventive',
            'replacementPart'
        ])->find($id);

        if (!$activity) {
            return response()->json([
                'status' => 0,
                'message' => 'Data aktivitas TMS tidak ditemukan.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Berhasil mengambil data aktivitas TMS.',
            'data' => $activity
        ], 200);
    }



    public function storeActivityTms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_machine_id' => 'required|exists:item_machines,id',
            'date' => 'required|date',

            // JSA file uploads
            'jsa_file_cleaning_criticals' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'jsa_file_just_cleaning' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'jsa_file_replacement_part' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'jsa_file_preventive' => 'nullable|file|mimes:pdf,jpg,jpeg,png',

            // Foto array
            'cleaning_criticals' => 'nullable|array',
            'cleaning_criticals.*.foto_before' => 'nullable|file|mimes:jpg,jpeg,png',
            'cleaning_criticals.*.foto_after' => 'nullable|file|mimes:jpg,jpeg,png',

            'just_cleaning' => 'nullable|array',
            'just_cleaning.*.foto_before' => 'nullable|file|mimes:jpg,jpeg,png',
            'just_cleaning.*.foto_after' => 'nullable|file|mimes:jpg,jpeg,png',

            'preventive' => 'nullable|array',
            'preventive.*.foto_before' => 'nullable|file|mimes:jpg,jpeg,png',
            'preventive.*.foto_after' => 'nullable|file|mimes:jpg,jpeg,png',

            'replacement_part' => 'nullable|array',
            'replacement_part.*.foto_before' => 'nullable|file|mimes:jpg,jpeg,png',
            'replacement_part.*.foto_after' => 'nullable|file|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();

            return response()->json([
                'status' => 0,
                'message' => $firstError,
            ], 422);
        }



        // Simpan file JSA jika ada
        $jsa_cleaning = $request->file('jsa_file_cleaning_criticals')?->store('jsa_files', 'public');
        $jsa_just = $request->file('jsa_file_just_cleaning')?->store('jsa_files', 'public');
        $jsa_replacement = $request->file('jsa_file_replacement_part')?->store('jsa_files', 'public');
        $jsa_preventive = $request->file('jsa_file_preventive')?->store('jsa_files', 'public');



        // Buat activity
        $activity = ActivityTMS::create([
            'item_machine_id' => $request->item_machine_id,
            'date' => $request->date,
            'jsa_file_cleaning_criticals' => $jsa_cleaning,
            'jsa_file_just_cleaning' => $jsa_just,
            'jsa_file_replacement_part' => $jsa_replacement,
            'jsa_file_preventive' => $jsa_preventive,
        ]);

        $fotoGroups = [
            'cleaning_criticals' => $activity->cleaningCriticals(),
            'just_cleaning' => $activity->justCleaning(),
            'preventive' => $activity->preventive(),
            'replacement_part' => $activity->replacementPart(),
        ];

        foreach ($fotoGroups as $prefix => $relation) {
            foreach (['before', 'after'] as $status) {
                $files = $request->file("{$prefix}_foto_{$status}", []);
                foreach ($files as $file) {
                    $path = $file->store('photos', 'public');
                    $relation->create([
                        'foto' => $path,
                        'status' => $status,
                    ]);
                }
            }
        }


        return response()->json([
            'status' => 1,
            'message' => 'Activity TMS berhasil ditambahkan.',
        ], 201);
    }

    public function updateActivityTms(Request $request, $id)
    {
        $activity = ActivityTMS::find($id);

        if (!$activity) {
            return response()->json([
                'status' => 0,
                'message' => 'Data aktivitas TMS tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'item_machine_id' => 'sometimes|required|exists:item_machines,id',
            'date' => 'sometimes|required|date',

            // JSA file uploads
            'jsa_file_cleaning_criticals' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'jsa_file_just_cleaning' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'jsa_file_replacement_part' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'jsa_file_preventive' => 'nullable|file|mimes:pdf,jpg,jpeg,png',

            // Foto array
            'cleaning_criticals' => 'nullable|array',
            'cleaning_criticals.*.foto_before' => 'nullable|file|mimes:jpg,jpeg,png',
            'cleaning_criticals.*.foto_after' => 'nullable|file|mimes:jpg,jpeg,png',

            'just_cleaning' => 'nullable|array',
            'just_cleaning.*.foto_before' => 'nullable|file|mimes:jpg,jpeg,png',
            'just_cleaning.*.foto_after' => 'nullable|file|mimes:jpg,jpeg,png',

            'preventive' => 'nullable|array',
            'preventive.*.foto_before' => 'nullable|file|mimes:jpg,jpeg,png',
            'preventive.*.foto_after' => 'nullable|file|mimes:jpg,jpeg,png',

            'replacement_part' => 'nullable|array',
            'replacement_part.*.foto_before' => 'nullable|file|mimes:jpg,jpeg,png',
            'replacement_part.*.foto_after' => 'nullable|file|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => collect($validator->errors()->all())->first(),
            ], 422);
        }

        // Simpan file JSA jika dikirim
        $jsa_cleaning = $request->file('jsa_file_cleaning_criticals')?->store('jsa_files', 'public');
        $jsa_just = $request->file('jsa_file_just_cleaning')?->store('jsa_files', 'public');
        $jsa_replacement = $request->file('jsa_file_replacement_part')?->store('jsa_files', 'public');
        $jsa_preventive = $request->file('jsa_file_preventive')?->store('jsa_files', 'public');

        // Update data utama
        $activity->update([
            'item_machine_id' => $request->item_machine_id ?? $activity->item_machine_id,
            'date' => $request->date ?? $activity->date,
            'jsa_file_cleaning_criticals' => $jsa_cleaning ?? $activity->jsa_file_cleaning_criticals,
            'jsa_file_just_cleaning' => $jsa_just ?? $activity->jsa_file_just_cleaning,
            'jsa_file_replacement_part' => $jsa_replacement ?? $activity->jsa_file_replacement_part,
            'jsa_file_preventive' => $jsa_preventive ?? $activity->jsa_file_preventive,
        ]);

        // Hapus foto lama jika perlu (optional: kamu bisa modifikasi ini jika ingin keep yang lama)
        $activity->cleaningCriticals()->delete();
        $activity->justCleaning()->delete();
        $activity->preventive()->delete();
        $activity->replacementPart()->delete();

        // Simpan ulang foto jika ada kiriman baru
        $fotoGroups = [
            'cleaning_criticals' => $activity->cleaningCriticals(),
            'just_cleaning' => $activity->justCleaning(),
            'preventive' => $activity->preventive(),
            'replacement_part' => $activity->replacementPart(),
        ];

        foreach ($fotoGroups as $prefix => $relation) {
            foreach (['before', 'after'] as $status) {
                $files = $request->file("{$prefix}_foto_{$status}", []);
                foreach ($files as $file) {
                    $path = $file->store('photos', 'public');
                    $relation->create([
                        'foto' => $path,
                        'status' => $status,
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Activity TMS berhasil diperbarui.',
            'data' => $activity->fresh()->load([
                'itemMachine',
                'cleaningCriticals',
                'justCleaning',
                'preventive',
                'replacementPart',
            ])
        ]);
    }

    public function destroyActivityTms($id)
    {
        $activity = ActivityTMS::find($id);

        if (!$activity) {
            return response()->json([
                'status' => 0,
                'message' => 'Activity TMS tidak ditemukan.',
            ], 404);
        }

        // Hapus file JSA jika ada
        $jsaFiles = [
            $activity->jsa_file_cleaning_criticals,
            $activity->jsa_file_just_cleaning,
            $activity->jsa_file_replacement_part,
            $activity->jsa_file_preventive,
        ];

        foreach ($jsaFiles as $filePath) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }

        // Hapus semua foto dari setiap relasi dan file-nya di storage
        $fotoRelations = [
            'cleaningCriticals',
            'justCleaning',
            'preventive',
            'replacementPart',
        ];

        foreach ($fotoRelations as $relation) {
            foreach ($activity->$relation as $foto) {
                if ($foto->foto && Storage::disk('public')->exists($foto->foto)) {
                    Storage::disk('public')->delete($foto->foto);
                }
                $foto->delete(); // hapus record di DB
            }
        }

        // Hapus activity
        $activity->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Activity TMS berhasil dihapus.',
        ]);
    }

    
}
