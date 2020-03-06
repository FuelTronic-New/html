<?php
namespace App\Repositories;

use App\Interfaces\JobRepositoryInterface;
use App\Interfaces\LocationRepositoryInterface;
use App\Models\Jobs;
use App\Models\Location;
use App\Models\Tags;
use App\Models\Sites;

class LocationRepository implements LocationRepositoryInterface
{
    protected $model;

    public function __construct(Location $location, Tags $tags)
    {
        $this->model = $location;
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
    }

    public function edit($id)
    {
        return $this->model->where('id', $id)->first();
    }
}

