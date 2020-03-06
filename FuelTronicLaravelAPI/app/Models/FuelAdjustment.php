<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelAdjustment extends Model
{
    use SoftDeletes;
    protected $table = 'fuel_adjustments';
    public $fillable = array('tank_id', 'litres', 'mode', 'created_by', 'site_id', 'motivation');

    public function customer()
    {
        //return $this->belongsTo('App\Models\Customers', 'customer_id', 'id');
    }

    public function supplier()
    {
        //return $this->belongsTo('App\Models\Suppliers', 'supplier_id', 'id');
    }

	public function tank()
	{
		return $this->belongsTo('App\Models\Tanks', 'tank_id')->withTrashed();
	}
}
