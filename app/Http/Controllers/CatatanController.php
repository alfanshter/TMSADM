<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityTms;
use Illuminate\Support\Facades\Validator;

class CatatanController extends Controller
{
     public function update(Request $request, $id)
    {
        $activity = ActivityTms::find($id);

        if (!$activity) {
            return response()->json([
                'status' => 0,
                'message' => 'Activity tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
<<<<<<< HEAD
            'catatan_teamleader_cleaning_criticals' => 'nullable|string|max:255',
            'catatan_supervisor_cleaning_criticals' => 'nullable|string|max:255',
            'catatan_teamleader_just_cleaning' => 'nullable|string|max:255',
            'catatan_supervisor_justcleaning' => 'nullable|string|max:255',
            'catatan_teamleader_replacement_part' => 'nullable|string|max:255',
            'catatan_supervisor_replacement_part' => 'nullable|string|max:255',
            'catatan_teamleader_preventive_pm' => 'nullable|string|max:255',
            'catatan_supervisor_preventive_pm' => 'nullable|string|max:255',
=======
            // Team Leader
            'catatan_teamleader_cleaning_criticals' => 'nullable|string',
            'catatan_supervisor_cleaning_criticals' => 'nullable|string',
            'catatan_teknisi_cleaning_criticals'    => 'nullable|string',
            'catatan_teamleader_just_cleaning'      => 'nullable|string',
            'catatan_supervisor_justcleaning'       => 'nullable|string',
            'catatan_teknisi_just_cleaning'         => 'nullable|string',
            'catatan_teamleader_replacement_part'   => 'nullable|string',
            'catatan_supervisor_replacement_part'   => 'nullable|string',
            'catatan_teknisi_replacement_part'      => 'nullable|string',
            'catatan_teamleader_preventive_pm'      => 'nullable|string',
            'catatan_supervisor_preventive_pm'      => 'nullable|string',
            'catatan_teknisi_preventive_pm'         => 'nullable|string',
>>>>>>> temp-main
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $activity->update($request->only([
            'catatan_teamleader_cleaning_criticals',
            'catatan_supervisor_cleaning_criticals',
<<<<<<< HEAD
            'catatan_teamleader_just_cleaning',
            'catatan_supervisor_justcleaning',
            'catatan_teamleader_replacement_part',
            'catatan_supervisor_replacement_part',
            'catatan_teamleader_preventive_pm',
            'catatan_supervisor_preventive_pm',
        ]));

        return response()->json([
            'status' => 1,
            'message' => 'Catatan berhasil diperbarui.',
=======
            'catatan_teknisi_cleaning_criticals',
            'catatan_teamleader_just_cleaning',
            'catatan_supervisor_justcleaning',
            'catatan_teknisi_just_cleaning',
            'catatan_teamleader_replacement_part',
            'catatan_supervisor_replacement_part',
            'catatan_teknisi_replacement_part',
            'catatan_teamleader_preventive_pm',
            'catatan_supervisor_preventive_pm',
            'catatan_teknisi_preventive_pm',
        ]));

        // Reload activity dengan relasi terbaru
        $activity = $activity->fresh();
        $activity->load([
            'itemMachine',
            'cleaningCriticals',
            'justCleaning',
            'preventive',
            'replacementPart',
            'spareparts'
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Catatan berhasil diperbarui.',
            'data' => $activity,
>>>>>>> temp-main
        ], 200);
    }
}