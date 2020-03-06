<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelDrop extends Model
{
    use SoftDeletes;
    protected $table = 'fuel_drops';
    public $fillable = array('site_id', 'tank_id', 'grade_id', 'supplier_id', 'litres', 'purchase_date', 'per_litre_price', 'tot_exc_vat', 'tot_inc_vat', 'vat');

    public function supplier()
    {
        return $this->belongsTo('App\Models\Suppliers', 'supplier_id', 'id')->withTrashed();
    }

    public function tank()
    {
        return $this->belongsTo('App\Models\Tanks', 'tank_id', 'id')->withTrashed();
    }

    public function grade()
    {
        return $this->belongsTo('App\Models\Grades', 'grade_id', 'id')->withTrashed();
    }

}
