<?php

namespace App\Http\Controllers;

use App\Exports\StockSparepartsExport;
use App\Models\SparepartLog;
use App\Models\StockSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class StockSparepartController extends Controller
{
    public function index(Request $request)
    {
        $query = StockSparepart::query();

        // Filter kategori
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Filter lokasi
        if ($request->has('loc') && !empty($request->loc)) {
            $query->where('loc', 'LIKE', "%{$request->loc}%");
        }

          // Tambahin usage (jumlah qty dari relasi tms_spareparts)
        $query->withSum('usages', 'qty');


        $data = $query->orderBy('nama_sparepart', 'ASC')
            ->get()
            ->map(function ($item) {
                 // pakai hasil dari withSum
                $item->usage = $item->usages_sum_qty ?? 0;
                $item->end_month_stock = $item->stok + $item->incoming - $item->usage;
                return $item;
            });

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'List spare parts retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_sparepart' => 'required|string|max:255',
            'spec' => 'nullable|string|max:255',
            'loc' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'category' => 'required|in:Belting & House,Safety,Tools,Spare part & Cons',
            'remark' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->stok != null) {
            $validated['stok'] = $request->stok;
        }

        // set stok awal & incoming default
        $validated['incoming'] = 0;
        $sparepart = StockSparepart::create($validated);

        // Log penambahan sparepart baru
        SparepartLog::create([
            'stock_sparepart_id' => $sparepart->id,
            'user_id'            => Auth::id(),
            'action'             => 'add_stock',
            'qty'                => $sparepart->stok ?? 0,
            'keterangan'         => 'Sparepart baru ditambahkan. Stok awal: ' . ($sparepart->stok ?? 0),
        ]);

        return response()->json([
            'status' => true,
            'data' => $sparepart,
            'message' => 'Spare part created successfully'
        ], 201);
    }

    public function show($id)
    {
        $sparepart = StockSparepart::withSum('usages', 'qty')
            ->with([
                'activities' => function ($q) {
                    $q->with('itemMachine:id,name,code,location,scope_of_work')
                      ->orderBy('date', 'desc');
                }
            ])
            ->find($id);

        if (!$sparepart) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Spare part not found'
            ], 404);
        }

        $sparepart['usage'] = $sparepart->usages_sum_qty ?? 0;
        $sparepart['end_month_stock'] = $sparepart['stok'] + $sparepart['incoming'] - $sparepart['usage'];

        // Format activities untuk frontend
        $sparepart['activity_usages'] = $sparepart->activities->map(function ($act) {
            return [
                'id'           => $act->id,
                'date'         => $act->date,
                'qty'          => $act->pivot->qty,
                'item_machine' => $act->itemMachine ? [
                    'name'          => $act->itemMachine->name,
                    'code'          => $act->itemMachine->code,
                    'location'      => $act->itemMachine->location,
                    'scope_of_work' => $act->itemMachine->scope_of_work,
                ] : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $sparepart,
            'message' => 'Spare part retrieved successfully'
        ]);
    }


    public function update(Request $request, $id)
    {
        $sparepart = StockSparepart::find($id);

        if (!$sparepart) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Spare part not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_sparepart' => 'sometimes|string|max:255',
            'spec' => 'sometimes|nullable|string|max:255',
            'loc' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|nullable|string|max:255',
            'category' => [
                'sometimes',
                'required',
                Rule::in(['Belting & House', 'Safety', 'Tools', 'Spare part & Cons']),
            ],
            'stok' => 'sometimes|integer|min:0',
            'incoming' => 'sometimes|integer|min:0',
            'remark' => 'sometimes|required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $validator->errors()
            ], 422);
        }

        // Simpan nilai lama sebelum update untuk log
        $oldStok     = $sparepart->stok;
        $oldIncoming = $sparepart->incoming;

        $validated = $validator->validated();
        $sparepart->update($validated);

        // Log perubahan stok
        if ($request->has('stok') && $request->stok != $oldStok) {
            $diff = ($request->stok ?? 0) - $oldStok;
            SparepartLog::create([
                'stock_sparepart_id' => $sparepart->id,
                'user_id'            => Auth::id(),
                'action'             => 'add_stock',
                'qty'                => abs($diff),
                'keterangan'         => 'Stok diubah dari ' . $oldStok . ' menjadi ' . $request->stok,
            ]);
        }

        // Log perubahan incoming
        if ($request->has('incoming') && $request->incoming != $oldIncoming) {
            $diff = ($request->incoming ?? 0) - $oldIncoming;
            SparepartLog::create([
                'stock_sparepart_id' => $sparepart->id,
                'user_id'            => Auth::id(),
                'action'             => 'add_incoming',
                'qty'                => abs($diff),
                'keterangan'         => 'Incoming diubah dari ' . $oldIncoming . ' menjadi ' . $request->incoming,
            ]);
        }

        // Re-fetch dengan withSum untuk mendapatkan data terbaru termasuk usages_sum_qty
        $sparepart = StockSparepart::withSum('usages', 'qty')->find($id);
        $sparepart['usage'] = $sparepart->usages_sum_qty ?? 0;
        $sparepart['end_month_stock'] = $sparepart['stok'] + $sparepart['incoming'] - $sparepart['usage'];

        return response()->json([
            'status' => true,
            'data' => $sparepart,
            'message' => 'Spare part updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $sparepart = StockSparepart::find($id);

        if (!$sparepart) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Spare part not found'
            ], 404);
        }

        // Log sebelum dihapus
        SparepartLog::create([
            'stock_sparepart_id' => $sparepart->id,
            'user_id'            => Auth::id(),
            'action'             => 'delete',
            'qty'                => 0,
            'keterangan'         => 'Sparepart "' . $sparepart->nama_sparepart . '" dihapus. Stok terakhir: ' . $sparepart->stok,
        ]);

        $sparepart->delete();

        return response()->json([
            'status' => true,
            'data' => null,
            'message' => 'Spare part deleted successfully'
        ]);
    }

    public function export(Request $request)
    {
        $year = $request->get('year');
    
        if (!$year || !is_numeric($year)) {
            return response()->json([
                'status' => false,
                'message' => 'Tahun tidak valid',
                'data' => null
            ], 422);
        }
    
        $filename = "stock_spareparts_{$year}.xlsx";
        $path = "exports/{$filename}";
    
        // Simpan file di storage/app/public/exports
        FacadesExcel::store(new StockSparepartsExport($year), $path, 'public');
    
        // Buat URL yang bisa diakses
        $downloadUrl = Storage::url($path);
    
        return response()->json([
            'status' => true,
            'message' => 'Export berhasil',
            'data' => [
                'download_link' => url($downloadUrl)
            ]
        ]);
    }

    /**
     * Ambil riwayat/log untuk satu sparepart
     */
    public function getLogs($id)
    {
        $sparepart = StockSparepart::find($id);

        if (!$sparepart) {
            return response()->json([
                'status' => false,
                'message' => 'Spare part not found',
                'data' => []
            ], 404);
        }

        $logs = SparepartLog::with('user:id,name')
            ->where('stock_sparepart_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id'          => $log->id,
                    'action'      => $log->action,
                    'qty'         => $log->qty,
                    'keterangan'  => $log->keterangan,
                    'user'        => $log->user?->name ?? 'System',
                    'created_at'  => $log->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'status'  => true,
            'message' => 'Logs retrieved successfully',
            'data'    => [
                'sparepart' => $sparepart->nama_sparepart,
                'logs'      => $logs,
            ]
        ]);
    }

    /**
     * Ambil semua riwayat log sparepart (semua item)
     */
    public function getAllLogs(Request $request)
    {
        $query = SparepartLog::with(['user:id,name', 'sparepart:id,nama_sparepart,loc,category'])
            ->orderBy('created_at', 'desc');

        // Filter by sparepart jika ada
        if ($request->has('sparepart_id') && $request->sparepart_id) {
            $query->where('stock_sparepart_id', $request->sparepart_id);
        }

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filter by month (format: YYYY-MM)
        if ($request->has('month') && $request->month) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
        }

        $logs = $query->get()->map(function ($log) {
            return [
                'id'             => $log->id,
                'sparepart'      => $log->sparepart?->nama_sparepart ?? '-',
                'sparepart_loc'  => $log->sparepart?->loc ?? '-',
                'sparepart_cat'  => $log->sparepart?->category ?? '-',
                'action'         => $log->action,
                'qty'            => $log->qty,
                'keterangan'     => $log->keterangan,
                'user'           => $log->user?->name ?? 'System',
                'created_at'     => $log->created_at->format('d M Y H:i'),
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'All sparepart logs retrieved successfully',
            'data'    => $logs,
        ]);
    }
}