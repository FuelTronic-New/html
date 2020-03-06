<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grades extends Model {

  use SoftDeletes;
  protected $table = 'grades';
  public $fillable = array('name', 'price', 'optional1', 'site_id', 'cur_rate', 'rate_increased_at', 'new_rate', 'vat_rate');

  public function tanks()
  {
    return $this->hasMany('App\Models\Tanks','grade_id');
  }

}
