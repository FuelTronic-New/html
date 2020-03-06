<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tags extends Model
{
    use SoftDeletes;
    protected $table = 'tags';
    public $fillable = array('type', 'usage', 'name');

    public function attendants()
    {
        return $this->belongsTo('App\Models\Attendants', 'id', 'tag_id');
    }

    public function vehicles()
    {
        return $this->belongsTo('App\Models\Vehicles', 'id', 'tag_id');
    }

    public function jobs()
    {
        return $this->belongsTo('App\Models\Jobs', 'id', 'tag_id');
    }

    public function sites()
    {
        return $this->belongsToMany('App\Models\Sites', 'site_tag', 'tag_id', 'site_id')->withTimestamps();
    }

}
