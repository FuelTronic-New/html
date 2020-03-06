<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierSite extends Model
{
    protected $table = 'supplier_site';
    public $fillable = array('supplier_id', 'site_id');
}
