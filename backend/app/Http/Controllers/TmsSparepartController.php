<?php

namespace App\Http\Controllers;

use App\Models\TmsSparepart;
use Illuminate\Http\Request;

class TmsSparepartController extends Controller
{
      // List all reports
      public function index()
      {
          $data = TmsSparepart::latest()->get();
          return response()->json([
              'status' => true,
              'data' => $data,
              'message' => 'List tms sparepart retrieved successfully'
          ]);
      }
}
