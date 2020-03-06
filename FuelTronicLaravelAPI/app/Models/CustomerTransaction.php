<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerTransaction extends Model
{
    use SoftDeletes;
    protected $table = 'customer_transactions';
    public $fillable = array('name', 'hose_id', 'pump_id', 'attendant_id','job_id', 'vehicle_id', 'litres',
                             'site_id', 'location_id', 'pin', 'odo_meter', 'cost_exc_vat', 'vat', 'total_cost',
                             'customer_id', 'start_date', 'end_date','order_number');

	public function scopeWithAndWhereHas($query, $relation, $constraint){
	    return $query->whereHas($relation, $constraint)
	                 ->with([$relation => $constraint]);
	}

	public function hose()
	{
		return $this->belongsTo('App\Models\Hoses', 'hose_id')->withTrashed();
	}
	public function job()
	{
		return $this->belongsTo('App\Models\Jobs', 'job_id');
	}
	public function attendant()
	{
		return $this->belongsTo('App\Models\Attendants', 'attendant_id')->withTrashed();
	}

	public function customer()
	{
		return $this->belongsTo('App\Models\Customers', 'customer_id')->withTrashed();
	}
	public function vehicle()
	{
		return $this->belongsTo('App\Models\Vehicles', 'vehicle_id')->withTrashed();
	}
	public function pump()
	{
		return $this->belongsTo('App\Models\Pumps', 'pump_id');
	}
}
