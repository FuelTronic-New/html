<?php

namespace App\Http\Controllers;

use App\Interfaces\AttendantRepositoryInterface;
use App\Models\Attendants;
use App\Models\Tags;
use JWTAuth;
use Illuminate\Http\Request;

class AttendantsController extends JwtAuthController
{
    public function __construct(Request $request, AttendantRepositoryInterface $attendantRepo)
    {
        parent::__construct();
        $this->attendant = $attendantRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data['sites'] = auth()->user()->sites()->select('id', 'name')->get();
            $Data['attendants'] = $this->attendant->getAll();
            foreach($Data['attendants'] as $key => $value){
                if (count($value->sites->lists('id')) > 0) {
                    $Data['attendants'][$key]->attendantSites = $value->sites->lists('id');
                } else {
                    if ($this->user->role == '2')
                        unset($Data['attendants'][$key]);
                    else
                        $Data['attendants'][$key]->attendantSites = [];
                }
            }
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Attendants not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }


    public function uploadImage()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'image' => 'mimes:jpeg,jpg,png,gif'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $file = request()->file('file');
            $Data['image'] = '';
            if (request()->hasFile('file')) {
                $image_name = time() . "-" . $file->getClientOriginalName();
                $file->move('uploads/attendants/', $image_name);
                $image_path = 'uploads/attendants/' . $image_name;
                $Data['image_path'] = $image_path;
                $Data['image'] = asset($image_path);
            }
            $returnData = array('status' => 'success', 'message' => 'Attendant image uploaded successfully!', 'saved_image' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Image upload failed', 'code' => 400);
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
        	$exist = Attendants::where('code', $Data['code'])->whereHas('sites', function ($q1) use($Data){
        		$q1->where('id', $Data['site_id']);
	        })->first();
        	if($exist){
		        return response()->json(['status' => 'failure', 'message' => 'The code is already available, you can not use it again',
		                                 'code' => 400],
			        400);
	        }
            $site_id = $Data['site_id'];unset($Data['site_id']);
            $attendant = $this->attendant->store($Data);
            $attendantData = Attendants::find($attendant->id);
            if($attendantData) {
	            $attendantData->sites()->attach($site_id);
            }
            $returnData = array('status' => 'success', 'message' => 'Attendant saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Attendant not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $attendant = $this->attendant->show($id);
            if(!$attendant){
	            $returnData = array('status' => 'failure', 'message' => 'Attendant not found', 'code' => 400);
            }else {
	            $attendant['tag_name'] = Tags::where('id', $attendant->tag_id)->pluck('name');
	            $attendant['image'] = \File::exists(public_path() . '/' . $attendant['image']) ? url() . '/' . $attendant['image'] : '';
	            $returnData = array ( 'status' => 'success', 'data' => $attendant, 'code' => 200);
            }
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Attendant not found', 'code' => 400);
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
	        $exist = Attendants::whereHas('sites', function ($q1) use($Data){
        		$q1->where('id', $Data['site_id']);
	        })->where('code', $Data['code'])->where('id', '!=', $id)->first();
        	if($exist){
		        return response()->json(['status' => 'failure', 'message' => 'The code is already available, you can not use it again', 'code' => 400], 400);
	        }
	        unset($Data['site_id']);
            $attendant = $this->attendant->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Attendant updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Attendant not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->attendant->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Attendant deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Attendant not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create()
    {
        $tags = $this->attendant->create();
        $returnData = array('status' => 'success', 'data' => $tags, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function edit($id)
    {
        $tags = $this->attendant->edit($id);
        $returnData = array('status' => 'success', 'data' => $tags, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function getSitesOfAttendant($attendantId)
    {
        try {
            $sites = auth()->user()->sites()->get();
            $attendant = Attendants::find($attendantId);
            $attendantSites = $attendant->sites()->lists('id');
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'attendantSites' => $attendantSites), 'message' => 'Selected sites of attendant listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Attendants not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function updateSitesOfAttendant()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'attendant_id' => 'required',
            //'site_ids' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $updateData = explode(',', $Data['site_ids']);
	        $attendant = Attendants::find($Data['attendant_id']);
	        if($attendant) {
//		        $newSiteIds = array_diff($updateData,$attendant->sites()->get()->pluck('id')->toArray());
		        if (!empty($Data['site_ids'])) {
			        $exist = Attendants::whereHas('sites', function ($q1) use ($updateData) {
				        $q1->whereIn('id', $updateData);
			        })->where('code', $attendant->code)->where('id', '!=', $Data['attendant_id'])->first();

			        if ($exist) {
				        return response()->json(['status'  => 'failure',
				                                 'message' => 'The code is already available for one of the attendant of the site you are trying to move to',
				                                 'code'    => 400
				        ], 400);
			        }
		        }
		        $sites = auth()->user()->sites()->get();
		        $attendantSites = $attendant->sites()->sync($updateData);
		        // Moving Attendants's tag between selected sites
		        $tagData = Tags::find($attendant->tag_id);
		        if ($tagData) {
			        $tagSites = $tagData->sites()->sync($updateData);
		        }
	        }

            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'attendantSites' => $attendantSites), 'message' => 'Attendant sites updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Attendant site not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }
}
