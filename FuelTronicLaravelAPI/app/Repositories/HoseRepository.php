<?php
namespace App\Repositories;

use App\Interfaces\HoseRepositoryInterface;
use App\Models\Hoses;
use App\Models\Sites;

class HoseRepository implements HoseRepositoryInterface
{
    protected $model;

    public function __construct(Hoses $hoses)
    {
        $this->model = $hoses;
    }

    public function getAll()
    {
        return auth()->user()->sites()->with(['hoses'=>function($query){
	        $query->with(['tank'=>function($q){
		        $q->selectRaw('id,name');
	        }]);
        }])->get();
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
        return $this->model->findOrFail($id)->delete($id);
    }

}

