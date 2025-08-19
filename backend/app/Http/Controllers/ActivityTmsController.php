<?php

namespace App\Http\Controllers;

use App\Models\ActivityTMS;
use App\Models\CleaningCritical;
use App\Models\ItemMachine;
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
            //scope of work safety
            'safety_scan' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'production_scan' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'outgoing'   => 'nullable|numeric',       // karena float bisa dicek pakai numeric
            'ingoing'    => 'nullable|numeric',
            'temp'       => 'nullable|numeric',       // bisa float
            'deviation'  => 'nullable|string|max:255',
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

        // Simpan file JSA jika ada, sekaligus simpan nama file asli
        $jsa_cleaning = $request->file('jsa_file_cleaning_criticals')?->store('jsa_files', 'public');
        $jsa_cleaning_name = $request->file('jsa_file_cleaning_criticals')?->getClientOriginalName();

        $jsa_just = $request->file('jsa_file_just_cleaning')?->store('jsa_files', 'public');
        $jsa_just_name = $request->file('jsa_file_just_cleaning')?->getClientOriginalName();

        $jsa_replacement = $request->file('jsa_file_replacement_part')?->store('jsa_files', 'public');
        $jsa_replacement_name = $request->file('jsa_file_replacement_part')?->getClientOriginalName();

        $jsa_preventive = $request->file('jsa_file_preventive')?->store('jsa_files', 'public');
        $jsa_preventive_name = $request->file('jsa_file_preventive')?->getClientOriginalName();

        //get data itemmachine
        $itemmachine = ItemMachine::where('id', $request->item_machine_id)->first();
        // default value (biar gak undefined variable)
        $incoming_rs = $incoming_rt = $incoming_st = null;
        $outgoing_rs = $outgoing_rt = $outgoing_st = null;
        $deviation = $temp = null;
        $safety_scan = null;
        $safety_scan_filename = null;
        $production_scan = null;
        $production_scan_filename = null;

        if ($itemmachine->scope_of_work == "safety") {
            $incoming_rs = $request->incoming_rs;
            $incoming_rt = $request->incoming_rt;
            $incoming_st = $request->incoming_st;
            $outgoing_rs = $request->outgoing_rs;
            $outgoing_rt = $request->outgoing_rt;
            $outgoing_st = $request->outgoing_st;
            $deviation = $request->deviation;
            $temp = $request->temp;

            $safety_scan = $request->file('safety_scan')?->store('safety_scan', 'public');
            $safety_scan_filename = $request->file('safety_scan')?->getClientOriginalName();

        } else if ($itemmachine->scope_of_work == "production") {
            $production_scan = $request->file('production_scan')?->store('production_scan', 'public');
            $production_scan_filename = $request->file('production_scan')?->getClientOriginalName();

        }



        // Buat activity
        $activity = ActivityTMS::create([
            'item_machine_id' => $request->item_machine_id,
            'date' => $request->date,
            'jsa_file_cleaning_criticals' => $jsa_cleaning,
            'jsa_filename_cleaning_criticals' => $jsa_cleaning_name,
            'jsa_file_just_cleaning' => $jsa_just,
            'jsa_filename_just_cleaning' => $jsa_just_name,
            'jsa_file_replacement_part' => $jsa_replacement,
            'jsa_filename_replacement_part' => $jsa_replacement_name,
            'jsa_file_preventive' => $jsa_preventive,
            'jsa_filename_preventive' => $jsa_preventive_name,
            'incoming_rs' => $incoming_rs,
            'incoming_rt' => $incoming_rt,
            'incoming_st' => $incoming_st,
            'outgoing_rs' => $outgoing_rs,
            'outgoing_rt' => $outgoing_rt,
            'outgoing_st' => $outgoing_st,
            'deviation' => $deviation,
            'temp' => $temp,
            'safety_scan' => $safety_scan,
            'production_scan' => $production_scan,
            'safety_scan_filename' => $safety_scan_filename,
            'production_scan_filename' => $production_scan_filename,
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
        $activity = ActivityTMS::findOrFail($id);


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
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Update JSA file, hapus lama jika ada file baru
        $jsaFiles = [
            'jsa_file_cleaning_criticals',
            'jsa_file_just_cleaning',
            'jsa_file_replacement_part',
            'jsa_file_preventive'
        ];

        foreach ($jsaFiles as $field) {
            if ($request->hasFile($field)) {
                if ($activity->$field) {
                    Storage::disk('public')->delete($activity->$field);
                }
                $activity->$field = $request->file($field)->store('jsa_files', 'public');
            }
        }

        $activity->item_machine_id = $request->item_machine_id;
        $activity->date = $request->date;
        $activity->save();

        // Update foto relasi
        $fotoGroups = [
            'cleaning_criticals' => $activity->cleaningCriticals(),
            'just_cleaning' => $activity->justCleaning(),
            'preventive' => $activity->preventive(),
            'replacement_part' => $activity->replacementPart(),
        ];

        foreach ($fotoGroups as $prefix => $relation) {
            foreach (['before', 'after'] as $status) {

                // Kalau field tidak ada di request → skip (tidak hapus, tidak tambah)
                if (!$request->has("{$prefix}_foto_{$status}")) {
                    continue;
                }

                $files = $request->file("{$prefix}_foto_{$status}");

                if ($files && count($files) > 0) {
                    // Hapus lama & simpan baru
                    $oldPhotos = $relation->where('status', $status)->get();
                    foreach ($oldPhotos as $photo) {
                        Storage::disk('public')->delete($photo->foto);
                        $photo->delete();
                    }
                    foreach ($files as $file) {
                        $path = $file->store('photos', 'public');
                        $relation->create([
                            'foto' => $path,
                            'status' => $status,
                        ]);
                    }
                } else {
                    // Field ada tapi kosong → hapus semua lama
                    $oldPhotos = $relation->where('status', $status)->get();
                    foreach ($oldPhotos as $photo) {
                        Storage::disk('public')->delete($photo->foto);
                        $photo->delete();
                    }
                }
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Activity TMS berhasil diupdate.',
        ], 200);
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
