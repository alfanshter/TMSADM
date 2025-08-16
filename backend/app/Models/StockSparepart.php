<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockSparepart extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_sparepart',
        'spec',
        'loc',
        'type',
        'category',
        'stok',
        'remark',
        'stok_awal',
        'incoming',
        'usage',
        'end_month_stock'
    ];
    
}
