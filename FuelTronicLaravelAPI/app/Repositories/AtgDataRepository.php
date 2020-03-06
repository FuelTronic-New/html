<?php
namespace App\Repositories;

use App\Interfaces\AtgDataRepositoryInterface;
use App\Models\AtgData;

class AtgDataRepository implements AtgDataRepositoryInterface
{
    protected $model;

    public function __construct(AtgData $atgData)
    {
        $this->model = $atgData;
    }

    public function getAll()
    {
        return $this->model->all();
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

