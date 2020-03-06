<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobSite extends Model
{
    protected $table = 'job_site';
    public $fillable = array('job_id', 'site_id');
}
