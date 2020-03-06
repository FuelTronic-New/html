<?php
namespace App\Repositories;

use App\Interfaces\SiteRepositoryInterface;
use App\Models\Sites;

class SiteRepository implements SiteRepositoryInterface
{
    protected $model;

    public function __construct(Sites $sites)
    {
        $this->model = $sites;
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

