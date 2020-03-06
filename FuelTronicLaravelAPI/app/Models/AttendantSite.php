<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendantSite extends Model
{
    protected $table = 'attendant_site';
    public $fillable = array('attendant_id', 'site_id');
}
