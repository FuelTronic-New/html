<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationSite extends Model
{
	protected $table = 'location_sites';
    protected $fillable = ['location_id', 'site_id'];
}
