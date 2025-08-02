<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplacementPart extends Model
{
    protected $guarded = [
        'id'
    ];

    public function activity()
    {
        return $this->belongsTo(ActivityTMS::class, 'activity_tms_id');
    }
}
