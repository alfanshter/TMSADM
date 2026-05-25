<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockSparepart extends Model
{
    use HasFactory, SoftDeletes;

    // protected $fillable = [
    //     'nama_sparepart',
    //     'spec',
    //     'loc',
    //     'type',
    //     'category',
    //     'stok',
    //     'remark',
    //     'stok_awal',
    //     'incoming',
    //     'usage',
    //     'end_month_stock'
    // ];

    protected $guarded = ['id'];

    public function activities()
    {
        return $this->belongsToMany(ActivityTms::class, 'activity_tms_spareparts')
            ->withPivot('qty')
            ->withTimestamps();
    }


    public function usages()
    {
        return $this->hasMany(TmsSparepart::class, 'stock_sparepart_id');
    }

    public function logs()
    {
        return $this->hasMany(SparepartLog::class, 'stock_sparepart_id');
    }
}
