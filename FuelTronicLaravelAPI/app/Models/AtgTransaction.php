<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtgTransaction extends Model
{
    use SoftDeletes;
    protected $table = 'atg_transactions';
    public $fillable = array('guid', 'date', 'time', 'cm', 'liters','atg_id');

	public function AtgData()
	{
		return $this->belongsTo('App\Models\AtgData','atg_id');
	}
}
