<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicles extends Model
{
    use SoftDeletes;
    public $table = 'vehicles';
    public $fillable = array('name', 'make', 'model', 'odo_meter', 'registration_number', 'contact_id', 'tag_id',
                             'site_id', 'customer_id', 'image','status','sars_type','fuel_allocation', 'code');

    public function sites()
    {
        return $this->belongsToMany('App\Models\Sites', 'site_vehicle', 'vehicle_id', 'site_id')->withTimestamps();
    }

    public function tag()
    {
        return $this->hasOne('App\Models\Tags', 'id', 'tag_id')->withTrashed();
    }


    public function transactions()
    {
        return $this->hasMany('App\Models\CustomerTransaction', 'vehicle_id');
    }

    public function customer()
    {
        return $this->hasOne('App\Models\Customers', 'id', 'customer_id');
    }

}
