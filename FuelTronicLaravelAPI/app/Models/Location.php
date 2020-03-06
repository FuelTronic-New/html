<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'description'];

	public function sites()
 {
     return $this->belongsToMany('App\Models\Sites', 'location_sites', 'location_id', 'site_id')->withTimestamps();
 }
}
