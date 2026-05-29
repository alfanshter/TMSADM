<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD

class StockSparepart extends Model
{
    use HasFactory;
=======
use Illuminate\Database\Eloquent\SoftDeletes;

class StockSparepart extends Model
{
    use HasFactory, SoftDeletes;
>>>>>>> temp-main

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
<<<<<<< HEAD
        return $this->belongsToMany(ActivityTms::class, 'activity_tms_spareparts')
=======
        // Pivot table: tms_spareparts
        // FK di pivot untuk StockSparepart : stock_sparepart_id
        // FK di pivot untuk ActivityTms    : activity_tms_id
        return $this->belongsToMany(
                ActivityTms::class,
                'tms_spareparts',       // nama tabel pivot yang benar
                'stock_sparepart_id',   // FK kolom untuk model ini (StockSparepart)
                'activity_tms_id'       // FK kolom untuk model target (ActivityTms)
            )
>>>>>>> temp-main
            ->withPivot('qty')
            ->withTimestamps();
    }


    public function usages()
    {
        return $this->hasMany(TmsSparepart::class, 'stock_sparepart_id');
    }
<<<<<<< HEAD
=======

    public function logs()
    {
        return $this->hasMany(SparepartLog::class, 'stock_sparepart_id');
    }
>>>>>>> temp-main
}
