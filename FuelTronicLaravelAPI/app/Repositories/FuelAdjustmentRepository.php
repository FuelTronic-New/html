<?php
namespace App\Repositories;

use App\Interfaces\FuelAdjustmentRepositoryInterface;
use App\Models\FuelAdjustment;
use App\Models\Payment;

class FuelAdjustmentRepository implements FuelAdjustmentRepositoryInterface
{
    protected $model;

    public function __construct(FuelAdjustment $fuelAdjustment)
    {
        $this->model = $fuelAdjustment;
    }

    public function getAll()
    {
        return auth()->user()->sites()->with('fuel_adjustments','fuel_adjustments.tank')->get();
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

}