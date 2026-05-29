<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TmpPhotos extends Model
{
    protected $guarded = ['id'];
    public function isExpired()
    {
        return $this->expired_at && now()->greaterThan($this->expired_at);
    }
}
