<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pica;
use Illuminate\Http\Request;

class PicaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $picas = Pica:: all();
        return response()->json([
            'success' => true,
            'message' => 'daftar semua pica',
            'data' => $picas
        ], 200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $validated = $request->validate([
            'problem' => 'required|string',
            'cause' => 'required|string',
            'corrective_action' => 'required|string',
            'date' => 'required|date',
            'pic' => 'required|max:255',
            'status' => 'required|string',
        ]);

        // Pastikan pic selalu disimpan sebagai string
        $validated['pic'] = (string) $validated['pic'];

        $pica = Pica::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'PICA berhasil ditambahkan',
            'data' => $pica
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pica = Pica::find($id);

        if(!$pica) {
            return response()->json([
                'success' => false,
                'message' => 'data pica tidak ditemukan',
            ], 404);
        }

         return response()->json([
            'success' => true,
            'message' => 'Detail PICA',
            'data' => $pica
        ], 200);
    }


    public function update(Request $request, string $id)
    {
         $pica = Pica::find($id);
        if (!$pica) {
            return response()->json([
                'success' => false,
                'message' => 'Data PICA tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'problem' => 'required|string',
            'cause' => 'required|string',
            'corrective_action' => 'required|string',
            'date' => 'required|date',
            'pic' => 'required|max:255',
            'status' => 'required|string',
        ]);

        // Pastikan pic selalu disimpan sebagai string
        $validated['pic'] = (string) $validated['pic'];

        $pica->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'PICA berhasil diperbarui',
            'data' => $pica
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $pica = Pica::find($id);
        if (!$pica) {
            return response()->json([
                'success' => false,
                'message' => 'Data PICA tidak ditemukan'
            ], 404);
        }

        $pica->delete();

        return response()->json([
            'success' => true,
            'message' => 'PICA berhasil dihapus'
        ], 200);
    }
}
