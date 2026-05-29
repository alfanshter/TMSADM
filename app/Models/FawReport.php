<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FawReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'date',
        'result'
    ];

    public function photos()
    {
        return $this->hasMany(FawReportPhoto::class);
    }
}
