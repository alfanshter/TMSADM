<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityTms;
use App\Models\FawReport;
use App\Models\LeakageReport;
use App\Models\ItemMachine;
use App\Models\User;
use App\Models\StockSparepart;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $month = $request->query('month'); // format 'YYYY-MM'

        $userCount = User::count();
        $itemMachineCount = ItemMachine::count();
        $stokSparepartCount = StockSparepart::count();
        $activityCount = ActivityTms::count();
        $fawCount = FawReport::count();
        $leakageCount = LeakageReport::count();

        // schedule diambil dari ActivityTms yang tanggalnya di bulan yg diminta
        $scheduleCount = ActivityTms::where('date', 'like', "$month%")->count();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics retrieved successfully.',
            'data' => [
                'user_count' => $userCount,
                'item_machine_count' => $itemMachineCount,
                'stok_sparepart_count' => $stokSparepartCount,
                'activity_tms_count' => $activityCount,
                'faw_report_count' => $fawCount,
                'leakage_report_count' => $leakageCount,
                'schedule_count' => $scheduleCount,

            ],
        ], 200);
    }
}
