<?php

namespace App\Http\Controllers;

use App\Models\ActivityTMS;
use App\Models\ItemMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    // List all reports
    public function index(Request $request)
    {
        $month = $request->get('month'); // format: YYYY-MM, misalnya '2025-06'
        if (!$month) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Month parameter is required (YYYY-MM)'
            ], 400);
        }

        $data = ItemMachine::select(
            'item_machines.name',
            'item_machines.code',
            'item_machines.location',
            DB::raw('COUNT(activity_tms.id) as act_per_month'),
            DB::raw('GROUP_CONCAT(activity_tms.date ORDER BY activity_tms.date ASC) as dates')
        )
            ->join('activity_tms', 'activity_tms.item_machine_id', '=', 'item_machines.id')
            ->whereRaw("DATE_FORMAT(activity_tms.date, '%Y-%m') = ?", [$month])
            ->groupBy('item_machines.code', 'item_machines.name', 'item_machines.location')
            ->orderBy('item_machines.code', 'ASC')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'code' => $item->code,
                    'location' => $item->location,
                    'act_per_month' => (int) $item->act_per_month,
                    'dates' => explode(',', $item->dates)
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Activity summary retrieved successfully'
        ]);
    }
}
