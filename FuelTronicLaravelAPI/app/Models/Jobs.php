<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jobs extends Model
{
    use SoftDeletes;
    protected $table = 'jobs';
    public $fillable = array('name', 'description', 'tag_id', 'site_id','fuel_allocation', 'code');

    public function tag()
    {
        return $this->hasOne('App\Models\Tags', 'id', 'tag_id');
    }


    public function sites()
    {
        return $this->belongsToMany('App\Models\Sites', 'job_site', 'job_id', 'site_id')->withTimestamps();
    }
}