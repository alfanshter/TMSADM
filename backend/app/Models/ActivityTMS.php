<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityTMS extends Model
{
    use HasFactory;

    protected $table = 'activity_tms'; //  Tambahkan ini!

    protected $guarded = [
        'id'
    ];


    public function itemMachine()
    {
        return $this->belongsTo(ItemMachine::class);
    }

    public function cleaningCriticals()
    {
        return $this->hasMany(CleaningCritical::class, 'activity_tms_id');
    }

    public function justCleaning()
    {
        return $this->hasMany(JustCleaning::class , 'activity_tms_id');
    }

    public function preventive()
    {
        return $this->hasMany(Preventive::class , 'activity_tms_id');
    }

    public function replacementPart()
    {
        return $this->hasMany(ReplacementPart::class ,'activity_tms_id');
    }


    // public function maintenanceType()
    // {
    //     return $this->belongsTo(MaintenanceType::class);
    // }
}
