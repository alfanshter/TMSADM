<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparepartLog extends Model
{
    use HasFactory;

    protected $table = 'sparepart_logs';

    protected $guarded = ['id'];

    public function sparepart()
    {
        return $this->belongsTo(StockSparepart::class, 'stock_sparepart_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
