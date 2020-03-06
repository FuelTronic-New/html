<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    protected $table = 'payments';
    public $fillable = array('customer_id', 'supplier_id', 'amount', 'mode', 'created_by', 'site_id');

    public function customer()
    {
        return $this->belongsTo('App\Models\Customers', 'customer_id', 'id')->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\Suppliers', 'supplier_id', 'id');
    }

}
