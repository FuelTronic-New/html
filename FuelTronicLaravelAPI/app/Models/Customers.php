<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customers extends Model
{
    use SoftDeletes;
    protected $table = 'customers';
    public $fillable = array('accountNumber', 'usage', 'status', 'name', 'first_name', 'last_name','fuel_price',
                    'email_address', 'phone', 'fax', 'mobile', 'site_id','address_line_1', 'address_line_2', 'address_line_3', 'address_line_4' );

	public function sites()
    {
        return $this->belongsToMany('App\Models\Sites', 'customer_site', 'customer_id', 'site_id')->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\CustomerTransaction', 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Payment', 'customer_id');
    }

    public function vehicles()
    {
        return $this->hasMany('App\Models\Vehicles', 'customer_id');
    }

}
