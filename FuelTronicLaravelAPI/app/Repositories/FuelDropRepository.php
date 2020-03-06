<?php
namespace App\Repositories;

use App\Interfaces\FuelDropRepositoryInterface;
use App\Models\FuelDrop;

class FuelDropRepository implements FuelDropRepositoryInterface
{
    protected $model;

    public function __construct(FuelDrop $fuelDrop)
    {
        $this->model = $fuelDrop;
    }

    public function getAll()
    {
        //return auth()->user()->sites()->with('fuel_drops')->get();
        return auth()->user()->sites()
            ->with(['fuel_drops', 'fuel_drops.supplier' => function ($q) {
                $q->selectRaw("id, name, first_name, last_name");
            }, 'fuel_drops.tank' => function ($q) {
                $q->selectRaw("id, name");
            }])->get();
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

