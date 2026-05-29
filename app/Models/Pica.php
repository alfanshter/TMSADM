<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pica extends Model
{
    use HasFactory;

    protected $fillable = [
        'problem',
        'cause',
        'corrective_action',
        'date',
        'pic',
        'status',
    ];
}
