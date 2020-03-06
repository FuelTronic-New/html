<?php

namespace App\Http\Controllers;

use App\Interfaces\JobRepositoryInterface;
use App\Models\Jobs;
use App\Models\Tags;
use JWTAuth;
use Illuminate\Http\Request;

class JobsController extends JwtAuthController
{
    public function __construct(Request $request, JobRepositoryInterface $jobRepo)
    {
        parent::__construct();
        $this->job = $jobRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data['sites'] = $sites = auth()->user()->sites()->select('id', 'name')->get();
            $Data['jobs'] = $this->job->getAll();
            foreach($Data['jobs'] as $key => $value){
                if (count($value->sites->lists('id')) > 0) {
                    $Data['jobs'][$key]->jobSites = $value->sites->lists('id');
                } else {
                    if ($this->user->role == '2')
                        unset($Data['jobs'][$key]);
                    else
                        $Data['jobs'][$key]->jobSites = [];
                }
            }
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Jobs not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'tag_id' => 'required|numeric',
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
	        $exist = Jobs::where('code', $Data['code'])->whereHas('sites', function ($q1) use($Data){
        		$q1->where('id', $Data['site_id']);
	        })->first();
        	if($exist){
		        return response()->json(['status' => 'failure', 'message' => 'The code is already available, you can not use it again', 'code' => 400], 400);
	        }
            $site_id = $Data['site_id'];unset($Data['site_id']);
            $job = $this->job->store($Data);
            $jobData = Jobs::find($job->id);
            $jobData->sites()->attach($site_id);
            $returnData = array('status' => 'success', 'message' => 'Job saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Job not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $job = $this->job->show($id);
	        if(!$job){
	            return response()->json(['status' => 'failure', 'message' => 'Job not found', 'code' => 400],400);
	         }
            $job['tag_name'] = Tags::where('id', $job->tag_id)->pluck('name');
            $returnData = array('status' => 'success', 'data' => $job, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Job not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'tag_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
	        $exist = Jobs::where('code', $Data['code'])->whereHas('sites', function ($q1) use($Data){
        		$q1->where('id', $Data['site_id']);
	        })->where('id', '!=', $id)->first();
        	if($exist){
		        return response()->json(['status' => 'failure', 'message' => 'The code is already available, you can not use it again', 'code' => 400], 400);
	        }
	        unset($Data['site_id']);
            $this->job->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Job updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Job not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $tank = $this->job->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Job deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Job not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create($site_id)
    {
        $tags = $this->job->create($site_id);
        $returnData = array('status' => 'success', 'data' => $tags, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }


    public function edit($id)
    {
        $tags = $this->job->edit($id);
        $returnData = array('status' => 'success', 'data' => $tags, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function getSitesOfJob($jobId)
    {
        try {
            $sites = auth()->user()->sites()->get();
            $job = Jobs::find($jobId);
            $jobSites = $job->sites()->lists('id');
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'jobSites' => $jobSites), 'message' => 'Selected sites of job listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Job sites not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function updateSitesOfJob()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'job_id' => 'required',
//            'site_ids' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $updateData = explode(',', $Data['site_ids']);
	        $job = Jobs::find($Data['job_id']);
	        if($job) {
//		        $newSiteIds = array_diff($updateData,$attendant->sites()->get()->pluck('id')->toArray());
		        if (!empty($Data['site_ids'])) {
			        $exist = Jobs::whereHas('sites', function ($q1) use ($updateData) {
				        $q1->whereIn('id', $updateData);
			        })->where('code', $job->code)->where('id', '!=', $Data['job_id'])->first();

			        if ($exist) {
				        return response()->json(['status'  => 'failure',
				                                 'message' => 'The code is already available for one of the job of the site you are trying to move to',
				                                 'code'    => 400
				        ], 400);
			        }
		        }
		        $sites = auth()->user()->sites()->get();
	            $jobSites = $job->sites()->sync($updateData);

		        // Moving Jobs's tag between selected sites
		        $tagData = Tags::find($job->tag_id);
		        if ($tagData) {
			        $tagSites = $tagData->sites()->sync($updateData);
		        }
	        }else{
		        return response()->json(['status' => 'failure', 'message' => 'Job not found', 'code' => 400], 400);
	        }
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'jobSites' => $jobSites), 'message' => 'Job sites updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => $e->getMessage(), 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }
}
