<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FawReportPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'faw_report_id',
        'photo_path'
    ];

    public function report()
    {
        return $this->belongsTo(FawReport::class);
    }
}
