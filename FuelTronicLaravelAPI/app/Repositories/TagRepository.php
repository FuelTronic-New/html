<?php
namespace App\Repositories;

use App\Interfaces\TagRepositoryInterface;
use App\Models\Tags;
use App\Models\Sites;

class TagRepository implements TagRepositoryInterface
{
    protected $model;

    public function __construct(Tags $tags)
    {
        $this->model = $tags;
    }

    public function getAll()
    {
        if (auth()->user()->role == 2) {
            return $this->model->whereHas('sites', function ($q) {
                $q->whereIn('id', auth()->user()->sites()->lists('id'));
            })->with(['sites' => function ($q) {
                $q->whereIn('id', auth()->user()->sites()->lists('id'));
            }])->get();
        } elseif (auth()->user()->role == 1) {
            return $this->model->with('sites')->get();
        }
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

