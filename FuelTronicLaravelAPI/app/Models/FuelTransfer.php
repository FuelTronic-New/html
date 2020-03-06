<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTransfer extends Model
{
    use SoftDeletes;
    protected $table = 'fuel_transfers';
    public $fillable = array('name', 'from_site', 'from_tank', 'to_site', 'to_tank', 'litres');

    public function fromSite()
    {
        return $this->belongsTo('App\Models\Sites','from_site');
    }
    public function toSite()
    {
        return $this->belongsTo('App\Models\Sites','to_site');
    }
    public function fromTank()
    {
        return $this->belongsTo('App\Models\Tanks','from_tank')->withTrashed();
    }
    public function toTank()
    {
        return $this->belongsTo('App\Models\Tanks','to_tank')->withTrashed();
    }

}
