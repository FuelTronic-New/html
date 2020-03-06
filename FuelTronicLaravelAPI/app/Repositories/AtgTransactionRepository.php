<?php
namespace App\Repositories;

use App\Interfaces\AtgTransactionRepositoryInterface;
use App\Models\AtgData;
use App\Models\AtgTransaction;

class AtgTransactionRepository implements AtgTransactionRepositoryInterface
{
    protected $model;

    public function __construct(AtgTransaction $atgTransaction)
    {
        $this->model = $atgTransaction;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function store($Data)
    {
        return $this->model->create($Data);
    }

    public function show($guid)
    {
        return $this->model->where('guid', $guid)->first();
    }

    public function update($guid, $Data)
    {
        return $this->model->where('guid', $guid)->update($Data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

}

