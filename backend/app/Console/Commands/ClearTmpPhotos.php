<?php

namespace App\Console\Commands;

use App\Models\TmpPhotos;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearTmpPhotos extends Command
{

    protected $signature = 'photos:clear-tmp';
    protected $description = 'Hapus tmp_photos yang lebih dari 1 jam dan tidak dipakai';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expired = Carbon::now()->subHours(1);

        $tmpPhotos = TmpPhotos::where('created_at', '<', $expired)->get();

        foreach ($tmpPhotos as $photo) {
            // hapus file dari storage
            if (Storage::disk('public')->exists($photo->path)) {
                Storage::disk('public')->delete($photo->path);
            }
            $photo->delete();
        }

        $this->info('Tmp photos dibersihkan: ' . count($tmpPhotos));

    }
}
