<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityTMS extends Model
{
    use HasFactory;

    protected $table = 'activity_tms'; //  Tambahkan ini!

    protected $fillable = [
        'date',
        'item_machine_id',
        'maintenance_type_id',
        'photo_before',
        'photo_after',
        'jsa_file',
    ];

    public function itemMachine()
    {
        return $this->belongsTo(ItemMachine::class);
    }

    public function maintenanceType()
    {
        return $this->belongsTo(MaintenanceType::class);
    }
}
