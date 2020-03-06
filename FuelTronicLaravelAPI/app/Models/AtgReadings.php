<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtgReadings extends Model
{
    use SoftDeletes;
    protected $table = 'atg_readings';
    protected $fillable = ['name', 'litre_readings', 'tank_id','site_id','reading_time'];
}
