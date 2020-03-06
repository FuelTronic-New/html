<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pumps extends Model
{
    use SoftDeletes;
    protected $table = 'pumps';
    public $fillable = array('name', 'ip', 'code', 'optional1', 'optional2', 'optional3', 'attendent_tag', 'vehicle_tag', 'odo_meter',
        'pin', 'job_tag', 'group_tag_1', 'group_tag_2', 'group_tag_3', 'group_tag_4', 'group_tag_5', 'site_id', 'guid', 'location','driver_fingerprint','order_number');

    public function customer_transaction()
    {
        return $this->hasManyThrough('App\Models\CustomerTransaction','App\Models\Hoses', 'pump_id','hose_id');
    }

	public function hoses()
	{
		return $this->hasMany('App\Models\Hoses','pump_id');
    }
	public function site()
	{
		return $this->belongsTo('App\Models\Sites','site_id');
    }
	public function hose()
	{
		return $this->belongsTo('App\Models\Hoses','id','pump_id');
    }

}
