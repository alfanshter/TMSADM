<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemMachine extends Model
{

    protected $fillable = [
        'name',
        'code',
        'location',
        'scope_of_work',
    ];

    // Relasi: ItemMachine -> hasMany ActivityTMS
    public function activityTms()
    {
        return $this->hasMany(ActivityTMS::class);
    }
}
