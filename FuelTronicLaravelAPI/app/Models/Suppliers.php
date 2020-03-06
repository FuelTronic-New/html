<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suppliers extends Model
{
    use SoftDeletes;
    protected $table = 'suppliers';
    public $fillable = array('accountNumber', 'usage', 'status', 'name', 'first_name', 'last_name',
                    'email_address', 'phone', 'fax', 'mobile', 'site_id');

    public function sites()
    {
        return $this->belongsToMany('App\Models\Sites', 'supplier_site', 'supplier_id', 'site_id')->withTimestamps();
    }

    public function fueldrops()
    {
        return $this->hasMany('App\Models\FuelDrop', 'supplier_id', '');
    }

	public function payments()
	{
		return $this->hasMany('App\Models\Payment', 'supplier_id');
	}

}
