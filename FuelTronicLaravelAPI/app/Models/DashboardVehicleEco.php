<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardVehicleEco extends Model
{
    protected $table = 'dashboardvehicleeco';

	public function vehicle()
	{
		return $this->belongsTo('App\Models\Vehicles', 'vehicle_id');
    }
}
