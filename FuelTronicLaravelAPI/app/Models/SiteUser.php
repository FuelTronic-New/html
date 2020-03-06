<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteUser extends Model
{
    protected $table = 'site_user';
    public $fillable = array('site_id', 'user_id');
}
