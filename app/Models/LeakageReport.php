<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeakageReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_scan',
        'lokasi',
        'date'
    ];
}
