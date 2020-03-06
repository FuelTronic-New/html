<?php
namespace App\Repositories;

use App\Interfaces\FuelTransferRepositoryInterface;
use App\Models\FuelTransfer;

class FuelTransferRepository implements FuelTransferRepositoryInterface
{
    protected $model;

    public function __construct(FuelTransfer $fuelTransfer)
    {
        $this->model = $fuelTransfer;
    }

    public function getAll()
    {
    	$authUserSites = auth()->user()->sites->pluck('id')->toArray();
        $query = $this->model->with(['fromSite' => function ($query) {
            return $query->selectRaw('id,name');
        }, 'toSite' => function ($query) {
            return $query->selectRaw('id,name');
        }, 'fromTank' => function ($query) {
            return $query->selectRaw('id,name');
        }, 'toTank' => function ($query) {
            return $query->selectRaw('id,name');
        }]);
        if(request()->has('site_id')){
	        $query = $query->where('from_site', request()->site_id)->orWhere('to_site', request()->site_id);
        }else{
	        $query = $query->whereIn('from_site', $authUserSites)->orWhereIn('to_site', $authUserSites);
        }
		$data = $query->get();
        return $data;

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

    public function create($siteId)
    {
        return $this->tags->has('jobs', '=', 0)->where(['site_id' => $siteId, 'usage' => 'Jobs'])->get();
    }

    public function edit($siteId)
    {
        return $this->tags->has('jobs', '=', 0)->where(['site_id' => $siteId, 'usage' => 'Jobs'])->get();
    }

}

