<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceType extends Model
{
    protected $guarded = ['id']; //kolom untuk jenis maintenance

      public function activityTms()
    {
        return $this->belongsTo(ActivityTms::class, 'tms_id');
    }
}
