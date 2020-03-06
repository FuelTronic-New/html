<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hoses extends Model
{
    use SoftDeletes;
    protected $table = 'hoses';
    public $fillable = array('name', 'optional1', 'pump_id', 'tank_id', 'site_id');

	public function scopeWithAndWhereHas($query, $relation, $constraint){
	    return $query->whereHas($relation, $constraint)
	                 ->with([$relation => $constraint]);
	}

	public function pump()
	{
		return $this->belongsTo('App\Models\Pumps', 'pump_id')->withTrashed();
	}

	public function tank()
	{
		return $this->belongsTo('App\Models\Tanks', 'tank_id')->withTrashed();
	}
	public function customer_transactions()
    {
        return $this->hasMany('App\Models\CustomerTransaction','hose_id', 'id');
    }
}
