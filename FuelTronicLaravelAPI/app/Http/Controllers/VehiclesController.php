<?php

namespace App\Http\Controllers;

use App\Interfaces\VehicleRepositoryInterface;
use App\Models\Customers;
use App\Models\Tags;
use App\Models\Vehicles;
use JWTAuth;
use Illuminate\Http\Request;

class VehiclesController extends JwtAuthController
{
    public function __construct(Request $request, VehicleRepositoryInterface $vehicleRepo)
    {
        parent::__construct();
        $this->vehicle = $vehicleRepo;
        $this->request = $request;
    }

    public function index()
    {

        try {
            $Data['sites'] = $sites = auth()->user()->sites()->select('id', 'name')->get();
            $Data['vehicles'] = $this->vehicle->getAll();
            foreach ($Data['vehicles'] as $key => $value) {
                if(count($value->sites->lists('id')) > 0) {
                    $Data['vehicles'][$key]->vehicleSites = $value->sites->lists('id');
                } else {
                    if($this->user->role == '2')
                        unset($Data['vehicles'][$key]);
                    else
                        $Data['vehicles'][$key]->vehicleSites = [];
                }
            }
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Vehicles not found', 'code' => 400);
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
                $file->move('uploads/vehicles/', $image_name);
                $image_path = 'uploads/vehicles/' . $image_name;
                $Data['image_path'] = $image_path;
                $Data['image'] = asset($image_path);
            }
            $returnData = array('status' => 'success', 'message' => 'Vehicle image uploaded successfully!', 'saved_image' => $Data,'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Vehicle image not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'make' => 'required|max:255',
            'model' => 'required|max:255',
            'registration_number' => 'required|max:255',
            'customer_id' => 'required|numeric',
            'site_id' => 'required|numeric',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
	        $exist = Vehicles::where('code', $Data['code'])->whereHas('sites', function ($q1) use($Data){
        		$q1->where('id', $Data['site_id']);
	        })->first();
        	if($exist){
		        return response()->json(['status' => 'failure', 'message' => 'The code is already available, you can not use it again', 'code' => 400], 400);
	        }
            //$Data['name'] = Customers::findOrFail($Data['customer_id'])->pluck('name');
            $site_id = $Data['site_id'];unset($Data['site_id']);
            $vehicle = $this->vehicle->store($Data);
            $vehicleData = Vehicles::find($vehicle->id);
            $vehicleData->sites()->attach($site_id);
            $returnData = array('status' => 'success', 'message' => 'Vehicle saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Vehicle not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $vehicle = $this->vehicle->show($id);
	        if(!$vehicle){
	            return response()->json(['status' => 'failure', 'message' => 'Vehicle not found', 'code' => 400],400);
	         }
	        $vehicle['tag_name'] = Tags::where('id', $vehicle->tag_id)->pluck('name');
            $vehicle['image'] = \File::exists(public_path().'/'.$vehicle['image']) ? url().'/'.$vehicle['image'] : '';
            $returnData = array('status' => 'success', 'data' => $vehicle, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Vehicle not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'make' => 'required|max:255',
            'model' => 'required|max:255',
            'registration_number' => 'required|max:255',
            'customer_id' => 'required|numeric',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
	        $exist = Vehicles::where('code', $Data['code'])->whereHas('sites', function ($q1) use($Data){
        		$q1->where('id', $Data['site_id']);
	        })->where('id', '!=', $id)->first();
        	if($exist){
		        return response()->json(['status' => 'failure', 'message' => 'The code is already available, you can not use it again', 'code' => 400], 400);
	        }
	        unset($Data['site_id']);
            //$Data['name'] = Customers::findOrFail($Data['customer_id'])->pluck('name');
            $this->vehicle->update($id, $Data);
            $returnData = array('status' => 'success', 'message' => 'Vehicle updated successfully!', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Vehicle not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->vehicle->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Vehicle deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Vehicle not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

	public function create()
	{
        $tags = $this->vehicle->create();
//		$customers = Customers::select('id', 'name')->get();
		$customers = Customers::select('id', 'name')->whereHas('sites', function ($q1){
			$q1->where('site_id', $this->request->site_id);
		})->get();
		$returnData = array ( 'status' => 'success', 'data'   => array('customers'=>$customers, 'tags'=>$tags), 'code'   => 200);
		return response()->json($returnData, $returnData['code']);
	}

	public function edit($id)
	{
        $tags = $this->vehicle->edit($id);
		$customers = Customers::select('id', 'name')->whereHas('sites', function ($q1){
			$q1->where('site_id', $this->request->site_id);
		})->get();
		$returnData = array ( 'status' => 'success', 'data' => array('customers'=>$customers, 'tags'=>$tags), 'code'=> 200);
		return response()->json($returnData, $returnData['code']);
	}

    public function getSitesOfVehicle($vehicleId)
    {
        try {
            $sites = auth()->user()->sites()->get();
            $vehicle = Vehicles::find($vehicleId);
            $vehicleSites = $vehicle->sites()->lists('id');
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'vehicleSites' => $vehicleSites), 'message' => 'Selected sites of vehicle listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Vehicle sites not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function updateSitesOfVehicle()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'vehicle_id' => 'required',
//            'site_ids' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $updateData = explode(',', $Data['site_ids']);
	        $vehicle = Vehicles::find($Data['vehicle_id']);
	        if($vehicle) {
		        if (!empty($Data['site_ids'])) {
			        $exist = Vehicles::whereHas('sites', function ($q1) use ($updateData) {
				        $q1->whereIn('id', $updateData);
			        })->where('code', $vehicle->code)->where('id', '!=', $Data['vehicle_id'])->first();

			        if ($exist) {
				        return response()->json(['status'  => 'failure',
				                                 'message' => 'The code is already available for one of the vehicle of the site you are trying to move to',
				                                 'code'    => 400
				        ], 400);
			        }
		        }
		        $sites = auth()->user()->sites()->get();
	            $vehicleSites = $vehicle->sites()->sync($updateData);
		        // Moving Vehicles's tag between selected sites
		        $tagData = Tags::find($vehicle->tag_id);
		        if ($tagData) {
			        $tagSites = $tagData->sites()->sync($updateData);
		        }
	        }else{
		        return response()->json(['status'  => 'failure', 'message' => 'Vehicle not found', 'code' => 400], 400);
	        }
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'vehicleSites' => $vehicleSites), 'message' => 'Vehicle sites updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Vehicle sites not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }
}
