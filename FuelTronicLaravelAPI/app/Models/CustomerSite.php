<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSite extends Model
{
    protected $table = 'customer_site';
    public $fillable = array('customer_id', 'site_id');
}
