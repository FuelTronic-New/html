<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteTag extends Model
{
    protected $table = 'site_tag';
    public $fillable = array('site_id', 'tag_id');
}
