<?php
namespace App\Repositories;

use App\Interfaces\GradeRepositoryInterface;
use App\Models\Grades;
use App\Models\Sites;

class GradeRepository implements GradeRepositoryInterface
{
    protected $model;

    public function __construct(Grades $grades)
    {
        $this->model = $grades;
    }

    public function getAll()
    {
        return auth()->user()->sites()->with('grades')->get();
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

