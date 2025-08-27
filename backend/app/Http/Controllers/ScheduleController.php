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
        $month = $request->get('month'); // format: YYYY-MM
        if (!$month) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Month parameter is required (YYYY-MM)'
            ], 400);
        }
    
        // Ambil data per mesin + tanggal activity
        $data = ItemMachine::select(
            'item_machines.id',
            'item_machines.name',
            'item_machines.code',
            'item_machines.location',
            DB::raw('COUNT(activity_tms.id) as act_per_month'),
            DB::raw('GROUP_CONCAT(activity_tms.date ORDER BY activity_tms.date ASC) as dates')
        )
            ->join('activity_tms', 'activity_tms.item_machine_id', '=', 'item_machines.id')
            ->whereRaw("DATE_FORMAT(activity_tms.date, '%Y-%m') = ?", [$month])
            ->groupBy('item_machines.id', 'item_machines.code', 'item_machines.name', 'item_machines.location')
            ->orderBy('item_machines.code', 'ASC')
            ->get()
            ->map(function ($item) {
                // Template week 1–4
                $weeksTemplate = [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ];
    
                // Konversi setiap tanggal ke week number
                $dates = $item->dates ? explode(',', $item->dates) : [];
                foreach ($dates as $d) {
                    $day = (int) date('d', strtotime($d));
                    $week = min(4, floor(($day - 1) / 7) + 1); // Maksimal Week 4
                    $weeksTemplate[$week]++;
                }
    
                // Hasil akhir
                return [
                    'name'          => $item->name,
                    'code'          => $item->code,
                    'location'      => $item->location,
                    'act_per_month' => (int) $item->act_per_month,
                    'weeks'         => [
                        ['week' => 1, 'total' => $weeksTemplate[1]],
                        ['week' => 2, 'total' => $weeksTemplate[2]],
                        ['week' => 3, 'total' => $weeksTemplate[3]],
                        ['week' => 4, 'total' => $weeksTemplate[4]],
                    ]
                ];
            });
    
        return response()->json([
            'status'  => true,
            'data'    => $data,
            'message' => 'Activity summary retrieved successfully'
        ]);
    }
    

}
