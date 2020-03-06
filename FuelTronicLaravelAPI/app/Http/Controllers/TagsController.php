<?php

namespace App\Http\Controllers;

use App\Interfaces\TagRepositoryInterface;
use App\Models\Attendants;
use App\Models\Jobs;
use App\Models\Tags;
use App\Models\Vehicles;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class TagsController extends JwtAuthController
{
    public function __construct(Request $request, TagRepositoryInterface $tagRepo)
    {
        parent::__construct();
        $this->tag = $tagRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data['sites'] = $sites = auth()->user()->sites()->select('id', 'name')->get();
            $Data['tags'] = $this->tag->getAll();
            foreach ($Data['tags'] as $key => $value) {
                if (count($value->sites) > 0) {
                    $Data['tags'][$key]->tagSites = $value->sites->lists('id');
                } else {
                    if ($this->user->role == '2')
                        unset($Data['tags'][$key]);
                    else
                        $Data['tags'][$key]->tagSites = [];
                }
            }
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tags not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'type' => 'required|max:255',
            'name' => 'required|max:255',
            'site_id' => 'required|numeric',
            'usage' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $site_id = $Data['site_id'];unset($Data['site_id']);
            $tag = $this->tag->store($Data);
            $tagData = Tags::find($tag->id);
            $tagData->sites()->attach($site_id);
            $returnData = array('status' => 'success', 'message' => 'Tag saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tag not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $tag = $this->tag->show($id);
            $returnData = array('status' => 'success', 'data' => $tag, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tag not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'type' => 'required|max:255',
            'name' => 'required|max:255',
            'usage' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->tag->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Tag updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tag not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $tag = $this->tag->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Tag deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tag not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create()
    {

    }


    public function edit($id)
    {

    }

    public function getSitesOfTag($tagId)
    {
        try {
            $sites = auth()->user()->sites()->get();
            $tag = Tags::find($tagId);
            $tagSites = $tag->sites()->lists('id');
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'tagSites' => $tagSites), 'message' => 'Selected sites of tag listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tag sites not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function updateSitesOfTag()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'tag_id' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $updateData = explode(',', $Data['site_ids']);
            $sites = auth()->user()->sites()->get();
            $tag = Tags::find($Data['tag_id']);
	        if(!$tag){
	            return response()->json(['status' => 'failure', 'message' => 'Tag not found', 'code' => 400],400);
	         }
            $tagSites = $tag->sites()->sync($updateData);

            // Moving This Tag's Attendant, Vehicles or Jobs between selected sites
            if($tag->usage == 'Attendants'){
                $attendantData = Attendants::where('tag_id', $Data['tag_id'])->first();
                if($attendantData) {
	                $attendantData->sites()->sync($updateData);
                }
            } elseif($tag->usage == 'Vehicles'){
                $vehicleData = Vehicles::where('tag_id', $Data['tag_id'])->first();
                if($vehicleData) {
	                $vehicleData->sites()->sync($updateData);
                }
            } elseif($tag->usage == 'Jobs'){
                $jobData = Jobs::where('tag_id', $Data['tag_id'])->first();
                if($jobData) {
	                $jobData->sites()->sync($updateData);
                }
            }

            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'tagSites' => $tagSites), 'message' => 'Tag sites updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tag sites not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

}
