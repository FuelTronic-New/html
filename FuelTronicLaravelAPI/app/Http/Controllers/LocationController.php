<?php

namespace App\Http\Controllers;

use App\Interfaces\JobRepositoryInterface;
use App\Interfaces\LocationRepositoryInterface;
use App\Models\Jobs;
use App\Models\Location;
use App\Models\Tags;
use JWTAuth;
use Illuminate\Http\Request;

class LocationController extends JwtAuthController
{
    public function __construct(Request $request, LocationRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repo = $repository;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data['sites'] = $sites = auth()->user()->sites()->select('id', 'name')->get();
            $Data['locations'] = $this->repo->getAll();
            foreach($Data['locations'] as $key => $value){
                if (count($value->sites->lists('id')) > 0) {
                    $Data['locations'][$key]->locationSites = $value->sites->lists('id');
                } else {
                    if ($this->user->role == '2')
                        unset($Data['locations'][$key]);
                    else
                        $Data['locations'][$key]->locationSites = [];
                }
            }
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Locations not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $site_id = $Data['site_id'];unset($Data['site_id']);
            $location = $this->repo->store($Data);
	        $location->sites()->attach($site_id);
            $returnData = array('status' => 'success', 'message' => 'Location saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Location not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $job = $this->repo->show($id);
            if($job) {
	            $job['tag_name'] = Tags::where('id', $job->tag_id)->pluck('name');
            }
            $returnData = array('status' => 'success', 'data' => $job, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Location not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->repo->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Location updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Location not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->repo->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Location deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Location not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create($site_id)
    {
        $tags = $this->repo->create($site_id);
        $returnData = array('status' => 'success', 'data' => $tags, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }


    public function edit($id)
    {
        $tags = $this->repo->edit($id);
        $returnData = array('status' => 'success', 'data' => $tags, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function getSitesOfLocation($id)
    {
        try {
            $sites = auth()->user()->sites()->get();
            $location = Location::with('sites')->find($id);
	        $locationSites = [];
	        if($location->sites) {
		        $locationSites = $location->sites->pluck('id')->toArray();
	        }
            $returnData = ['status' => 'success', 'data' => ['sites' => $sites, 'locationSites' => $locationSites],
                                'message' => 'Selected sites of location listed successfully!', 'code' => 200];
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Location sites not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function updateSitesOfLocation()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'location_id' => 'required',
//            'site_ids' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $updateData = explode(',', $Data['site_ids']);
	        $location = Location::find($Data['location_id']);
	        $sites = []; $lSites = [];
	        if($location) {
//		        $newSiteIds = array_diff($updateData,$attendant->sites()->get()->pluck('id')->toArray());
		        $sites = auth()->user()->sites()->get();
	            $lSites = $location->sites()->sync($updateData);
	        }
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'locationSites' => $lSites)
                                , 'message' => 'Location sites updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Location sites not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }
}
