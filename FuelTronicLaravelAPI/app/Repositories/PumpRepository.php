<?php
namespace App\Repositories;

use App\Interfaces\PumpRepositoryInterface;
use App\Models\Pumps;
use App\Models\Sites;

class PumpRepository implements PumpRepositoryInterface
{
    protected $model;

    public function __construct(Pumps $pumps)
    {
        $this->model = $pumps;
    }

    public function getAll()
    {
        return auth()->user()->sites()->with('pumps')->get();
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

