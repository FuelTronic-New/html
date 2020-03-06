<?php
namespace App\Repositories;

use App\Interfaces\SiteusersRepositoryInterface;
use App\User;

class SiteusersRepository implements SiteusersRepositoryInterface
{
    protected $model;

    public function __construct(User $users)
    {
        $this->model = $users;
    }

    public function getAll($except)
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

