<?php
namespace App\Repositories;

use App\Interfaces\TankRepositoryInterface;
use App\Models\Tanks;
use App\Models\Sites;

class TankRepository implements TankRepositoryInterface
{
    protected $model;

    public function __construct(Tanks $tanks)
    {
        $this->model = $tanks;
    }

    public function getAll()
    {
        return auth()->user()->sites()->with('tanks')->get();
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

    public function updateFuel($tank_id, $litres)
    {
        $currentTank = $this->show($tank_id);
        if($currentTank) {
	        $Data['litre'] = $currentTank->litre + $litres;
	        $updateTank = $this->update($tank_id, $Data);
	        return $updateTank;
        }
        return true;
    }

}

