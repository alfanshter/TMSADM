<?php

namespace App\Http\Controllers;

use App\Exports\PMScheduleReport;
use App\Models\ActivityTMS;
use App\Models\ItemMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleController extends Controller
{
    // List all reports
    public function index(Request $request)
    {
        // ambil bulan dari request, kalau kosong pakai bulan sekarang
        $month = $request->get('month') ?? date('Y-m'); // format: YYYY-MM

        $query = ItemMachine::select(
            'item_machines.id',
            'item_machines.name',
            'item_machines.code',
            'item_machines.location',
            DB::raw('COUNT(activity_tms.id) as act_per_month'),
            DB::raw('GROUP_CONCAT(activity_tms.date ORDER BY activity_tms.date ASC) as dates'),
            DB::raw('GROUP_CONCAT(activity_tms.id ORDER BY activity_tms.date ASC) as act_per_month_ids')
        )
            ->join('activity_tms', 'activity_tms.item_machine_id', '=', 'item_machines.id')
            ->whereRaw("DATE_FORMAT(activity_tms.date, '%Y-%m') = ?", [$month]);

        $data = $query
            ->groupBy('item_machines.id', 'item_machines.code', 'item_machines.name', 'item_machines.location')
            ->orderBy('item_machines.code', 'ASC')
            ->get()
            ->map(function ($item) {
                $weeksTemplate = [
                    1 => ['count' => 0, 'ids' => []],
                    2 => ['count' => 0, 'ids' => []],
                    3 => ['count' => 0, 'ids' => []],
                    4 => ['count' => 0, 'ids' => []],
                ];

                // $item->dates sudah berisi tanggal, tapi kita juga butuh ID
                $activityDates = $item->dates ? explode(',', $item->dates) : [];
                $activityIds = $item->act_per_month_ids ? explode(',', $item->act_per_month_ids) : [];
                $activityIds = array_filter($activityIds, fn($id) => !empty($id));

                foreach ($activityDates as $index => $d) {
                    $day = (int) date('d', strtotime($d));
                    $week = min(4, floor(($day - 1) / 7) + 1);
                    $weeksTemplate[$week]['count']++;
                    // pastikan index ada
                    if (isset($activityIds[$index])) {
                        $weeksTemplate[$week]['ids'][] = $activityIds[$index];
                    }
                }

                return [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'code'          => $item->code,
                    'location'      => $item->location,
                    'act_per_month' => (int) $item->act_per_month,
                    'week_1'        => $weeksTemplate[1]['count'],
                    'week_1_ids'    => $weeksTemplate[1]['ids'],
                    'week_2'        => $weeksTemplate[2]['count'],
                    'week_2_ids'    => $weeksTemplate[2]['ids'],
                    'week_3'        => $weeksTemplate[3]['count'],
                    'week_3_ids'    => $weeksTemplate[3]['ids'],
                    'week_4'        => $weeksTemplate[4]['count'],
                    'week_4_ids'    => $weeksTemplate[4]['ids'],
                ];
            });


        return response()->json([
            'status'  => true,
            'data'    => $data,
            'message' => "Activity summary retrieved successfully for $month"
        ]);
    }


    private function getDataFromQuery($month)
    {
        return ItemMachine::select(
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
                $weeksTemplate = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

                $dates = $item->dates ? explode(',', $item->dates) : [];
                foreach ($dates as $d) {
                    $day = (int) date('d', strtotime($d));
                    $week = min(4, floor(($day - 1) / 7) + 1);
                    $weeksTemplate[$week]++;
                }

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
    }

    public function export(Request $request)
    {
        $month = $request->get('month'); // format YYYY-MM

        $data = $this->getDataFromQuery($month); // <-- isi dengan query kamu tadi



        $fileName = "pm-schedule-{$month}.xlsx";
        $path = "exports/{$fileName}";

        Excel::store(new PMScheduleReport($month, $data), $path, 'public');

        return response()->json([
            'status' => true,
            'message' => 'Export berhasil',
            'data' => [
                'download_link' => url("storage/{$path}")
            ]
        ]);
    }
}
