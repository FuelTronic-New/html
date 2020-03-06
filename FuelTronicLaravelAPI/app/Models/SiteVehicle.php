<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVehicle extends Model
{
    protected $table = 'site_vehicle';
    public $fillable = array('site_id', 'vehicle_id');
}
