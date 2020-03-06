<?php
namespace App\Repositories;

use App\Interfaces\AtgReadingsRepositoryInterface;
use App\Models\AtgReadings;
use App\Models\Sites;
use App\User;

class AtgReadingsRepository implements AtgReadingsRepositoryInterface
{
    protected $model;

    public function __construct(AtgReadings $atgReadings)
    {
        $this->model = $atgReadings;
    }

    public function getAll()
    {
        return auth()->user()->sites()->with('atg_readings')->get();
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

