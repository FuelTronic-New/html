<?php
namespace App\Repositories;

use App\Interfaces\JobRepositoryInterface;
use App\Models\Jobs;
use App\Models\Tags;
use App\Models\Sites;

class JobRepository implements JobRepositoryInterface
{
    protected $model;

    public function __construct(Jobs $jobs, Tags $tags)
    {
        $this->model = $jobs;
        $this->tags = $tags;
    }

    public function getAll()
    {
        if (auth()->user()->role == 2) {
            return $this->model->whereHas('sites', function ($q) {
                $q->whereIn('id', auth()->user()->sites()->lists('id'));
            })->with(['sites' => function ($q) {
                $q->whereIn('id', auth()->user()->sites()->lists('id'));
            }])->get();
        } elseif (auth()->user()->role == 1) {
            return $this->model->with('sites')->get();
        }
    }

    public function store($Data)
    {
        return $this->model->create($Data);
    }

    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    public function update($id, $Data)
    {
        return $this->model->findOrFail($id)->update($Data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function create($site_id)
    {
        $siteIds = auth()->user()->sites->lists('id');
        $tags = $this->tags->whereHas('sites', function ($query) use ($site_id) {
            $query->where('id', $site_id);
        })->with('jobs')->where('usage', '=', 'Jobs')->get()->filter(function ($item) {
            if ($item->jobs == null) {
                return $item;
            }
        })->values();
        return $tags;
        //return $this->tags->has('jobs', '=', 0)->where(['usage' => 'Jobs'])->get();
    }

    public function edit($jobId)
    {
        $siteIds = auth()->user()->sites->lists('id');
        $job = Jobs::with('sites')->where('id', $jobId)->first();
        $siteIds = $job->sites->pluck('id')->toArray();

        $tags = $this->tags->select('id', 'name')->whereHas('sites', function ($query) use ($siteIds) {
            $query->whereIn('id', $siteIds);
        })->where(function ($q1) use($jobId){
        	$q1->doesntHave('jobs')->orWhereHas('jobs', function ($q2) use($jobId){
				$q2->where('id', $jobId);
	        });
        })->where(['usage' => 'Jobs'])->get();
        return $tags;
    }
}

