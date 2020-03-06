<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sites extends Model
{
    use SoftDeletes;
    protected $table = 'sites';
    protected $fillable = ['name'];

	public function grades()
	{
		return $this->hasMany('App\Models\Grades', 'site_id');
	}

	public function tanks()
	{
		return $this->hasMany('App\Models\Tanks', 'site_id');
	}

	public function pumps()
	{
		return $this->hasMany('App\Models\Pumps', 'site_id');
	}

	public function hoses()
	{
		return $this->hasMany('App\Models\Hoses', 'site_id');
	}

	public function attendants()
	{
		return $this->belongsToMany('App\Models\Attendants', 'attendant_site', 'site_id', 'attendant_id');
	}

	public function customers()
	{
		return $this->belongsToMany('App\Models\Customers','customer_site', 'site_id','customer_id');
	}

	public function vehicles()
	{
		//return $this->hasMany('App\Models\Vehicles', 'site_id');
		return $this->belongsToMany('App\Models\Vehicles', 'site_vehicle', 'site_id', 'vehicle_id');
	}

	public function suppliers()
	{
		return $this->belongsToMany('App\Models\Suppliers', 'supplier_site', 'site_id', 'supplier_id');
	}

	public function tags()
	{
		return $this->belongsToMany('App\Models\Tags', 'site_tag', 'site_id', 'tag_id');
	}

	public function jobs()
	{
		//return $this->hasMany('App\Models\Jobs', 'site_id');
		return $this->belongsToMany('App\Models\Jobs', 'job_site', 'site_id', 'job_id');
	}
	public function locations()
	{
		return $this->belongsToMany('App\Models\Location', 'location_sites', 'site_id', 'location_id');
	}

	public function atg_readings()
	{
		return $this->hasMany('App\Models\AtgReadings', 'site_id');
	}

	public function fuel_drops()
	{
		return $this->hasMany('App\Models\FuelDrop', 'site_id');
	}

	public function payments()
	{
		return $this->hasMany('App\Models\Payment', 'site_id');
	}

	public function users()
	{
		return $this->belongsToMany('App\User','site_user','user_id','site_id');
	}

	public function customer_transaction()
	{
		return $this->hasMany('App\Models\CustomerTransaction', 'site_id');
	}

	public function fuel_adjustments()
	{
		return $this->hasMany('App\Models\FuelAdjustment', 'site_id');
	}

}
