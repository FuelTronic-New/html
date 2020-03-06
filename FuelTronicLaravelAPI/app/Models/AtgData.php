<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtgData extends Model
{
    use SoftDeletes;
    protected $table = 'atg_data';
    public $fillable = array('name', 'ip_address', 'port_num', 'tank_type', 'sensor_height', 'fill_height', 'site_id',
        'riser_height', 'tank_height', 'cylinder_length', 'endcap_length', 'tank_diameter', 'height', 'guid');

	public function atgTransaction()
	{
		return $this->hasMany('App\Models\AtgTransaction','atg_id');
	}
	public function tank()
	{
		return $this->belongsTo('App\Models\Tanks','id','atg_id');
	}
}
