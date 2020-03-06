<?php
namespace App\Repositories;

use App\Interfaces\AttendantRepositoryInterface;
use App\Models\Attendants;
use App\Models\Sites;
use App\Models\Tags;

class AttendantRepository implements AttendantRepositoryInterface
{
    protected $model;

    public function __construct(Attendants $attendants, Tags $tags)
    {
        $this->model = $attendants;
        $this->tags = $tags;
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
        return $this->model->find($id);
    }

    public function update($id, $Data)
    {
        return $this->model->findOrFail($id)->update($Data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function create()
    {
        $siteIds = auth()->user()->sites->lists('id');
        $tags = $this->tags->whereHas('sites', function ($query) use ($siteIds) {
            $query->whereIn('id', $siteIds);
        })->with('attendants')->where('usage', '=', 'Attendants')->get()->filter(function ($item) {
            if ($item->attendants == null) {
                return $item;
            }
        })->values();
        return $tags;
        //return $this->tags->has('attendants', '=', 0)->where(['usage'=>'Attendants'])->get();
    }

    public function edit($attendantId)
    {
        $siteIds = auth()->user()->sites->lists('id');
//        $tags = $this->tags->whereHas('sites', function ($query) use ($siteIds) {
//            $query->whereIn('id', $siteIds);
//        })->with('attendants')->where(['usage'=>'Attendants'])->get()->filter(function ($item) {
//            if ($item->attendants == null) {
//                return $item;
//            }
//        })->values();
	    $tags = $this->tags->select('id', 'name')->whereHas('sites', function ($query) use ($siteIds) {
		    $query->whereIn('id', $siteIds);
	    })->where(function ($q1) use($attendantId){
		    $q1->doesntHave('attendants')->orWhereHas('attendants', function ($q2) use($attendantId){
			    $q2->where('id', $attendantId);
		    });
	    })->where('usage', '=', 'Attendants')->get();
        return $tags;
    }

}

