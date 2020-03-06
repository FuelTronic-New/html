<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendants extends Model
{
    use SoftDeletes;
    protected $table = 'attendants';
    public $fillable = array('name', 'surname', 'cell', 'said', 'site_id', 'tag_id', 'image', 'pin', 'fuel_allocation', 'code');

	public function sites()
    {
        return $this->belongsToMany('App\Models\Sites', 'attendant_site', 'attendant_id', 'site_id')->withTimestamps();
    }


    public function tag()
    {
        return $this->hasOne('App\Models\Tags', 'id', 'tag_id')->withTrashed();
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\CustomerTransaction', 'attendant_id', 'id');
    }

}
