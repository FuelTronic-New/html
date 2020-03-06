<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardAtg extends Model
{
    protected $table = 'dashboardatg';

	public function tank()
	{
		return $this->belongsTo('App\Models\Tanks', 'tank_id');
    }
}
