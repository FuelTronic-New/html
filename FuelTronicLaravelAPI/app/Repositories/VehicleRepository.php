<?php
namespace App\Repositories;

use App\Interfaces\VehicleRepositoryInterface;
use App\Models\Tags;
use App\Models\Vehicles;
use App\Models\Sites;

class VehicleRepository implements VehicleRepositoryInterface
{
    protected $model;

    public function __construct(Vehicles $vehicles, Tags $tags)
    {
        $this->model = $vehicles;
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

    public function create()
    {
        $siteIds = auth()->user()->sites->lists('id');
        $tags = $this->tags->whereHas('sites', function ($query) use ($siteIds) {
            $query->whereIn('id', $siteIds);
        })->with('vehicles')->where('usage', '=', 'Vehicles')->get()->filter(function ($item) {
            if ($item->vehicles == null) {
                return $item;
            }
        })->values();
        return $tags;
        //return $this->tags->has('vehicles', '=', 0)->where(['usage'=>'Vehicles'])->get();
    }

    public function edit($vehicleId)
    {
        $siteIds = auth()->user()->sites->lists('id');
        $tags = $this->tags->select('id', 'name')->whereHas('sites', function ($query) use ($siteIds) {
            $query->whereIn('id', $siteIds);
        })->where(function ($q1) use($vehicleId){
        	$q1->doesntHave('vehicles')->orWhereHas('vehicles', function ($q2) use($vehicleId){
        		$q2->where('id', $vehicleId);
	        });
        })->where(['usage' => 'Vehicles'])->get();
        return $tags;
    }

}
