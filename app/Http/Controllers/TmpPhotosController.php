<?php

namespace App\Http\Controllers;

use App\Models\TmpPhotos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TmpPhotosController extends Controller
{
    // Upload photo sementara
    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048'
        ]);

        $path = $request->file('photo')->store('tmp', 'public');

        $tmpPhoto = TmpPhotos::create([
            'path' => $path,
            'user_id' => Auth::id() ?? null,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => true,
            'data' => $tmpPhoto
        ]);
    }

    // Cancel upload (hapus file & data tmp)
    public function cancel($id)
    {
        $tmpPhoto = TmpPhotos::findOrFail($id);

        if ($tmpPhoto->status === 'pending') {
            Storage::disk('public')->delete($tmpPhoto->path);
            $tmpPhoto->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Upload dibatalkan'
        ]);
    }

    // Finalisasi (misalnya dipanggil setelah posting berita selesai)
    public function finalize($id)
    {
        $tmpPhoto = TmpPhotos::findOrFail($id);
        $tmpPhoto->status = 'used';
        $tmpPhoto->save();

        return response()->json([
            'status' => true,
            'message' => 'Foto dipakai untuk postingan'
        ]);
    }

    // Cleanup otomatis (opsional, bisa dipanggil via cronjob)
    public function cleanup()
    {
        $expired = TmpPhotos::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(2))
            ->get();

        foreach ($expired as $photo) {
            Storage::disk('public')->delete($photo->path);
            $photo->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Tmp photos dibersihkan'
        ]);
    }
}
