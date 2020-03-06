<?php

namespace App\Http\Controllers;

use App\Interfaces\AtgReadingsRepositoryInterface;
use App\Models\AtgReadings;
use App\Models\Tanks;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class AtgReadingsController extends JwtAuthController
{
    public function __construct(Request $request, AtgReadingsRepositoryInterface $atgrRepo)
    {
        parent::__construct();
        $this->atg_readings = $atgrRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->atg_readings->getAll();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG Readings not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'litre_readings' => 'required|numeric',
            'site_id' => 'required|numeric',
            'tank_id' => 'required|numeric',
            'reading_time' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {

            //$Data['reading_time'] = \Carbon\Carbon::parse($this->request->reading_time)->format('Y-m-d H:i:s');
            $atg_readings = $this->atg_readings->store($Data);
	        $tank = Tanks::find($Data['tank_id']);
	        if($tank) {
		        $tank->last_dip_reading = $Data['litre_readings'];
		        $tank->save();
	        }
            $returnData = array('status' => 'success', 'message' => 'ATG Readings saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG Reading not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $atg_readings = $this->atg_readings->show($id);
            $returnData = array('status' => 'success', 'data' => $atg_readings, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG Reading not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'litre_readings' => 'required|numeric',
            'site_id' => 'required|numeric',
            'tank_id' => 'required|numeric',
            'reading_time' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            //$Data['reading_time'] = \Carbon\Carbon::parse($this->request->reading_time)->format('Y-m-d H:i:s');
            $this->atg_readings->update($id, $Data);
            $last_atg_readings = AtgReadings::where('site_id', $Data['site_id'])->orderBy('id', 'DESC')->first();
            $tank = Tanks::find($last_atg_readings->tank_id);
            if($tank) {
	            $tank->last_dip_reading = $Data['litre_readings'];
	            $tank->save();
            }
            $returnData = array('status' => 'success', 'message' => 'ATG Readings updated successfully!', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG Reading not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $atg_readings = $this->atg_readings->delete($id);
            $returnData = array('status' => 'success', 'message' => 'ATG Readings deleted successfully!', 'data' => $atg_readings, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG Reading not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create($siteId)
    {
        $tanks = Tanks::select('id', 'name')->where(['site_id'=>$siteId, 'status'=>'Active'])->get();
        $returnData = array('status' => 'success', 'data' => $tanks, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function edit($id)
    {
        $atg_readings = AtgReadings::find($id);
        $tanks = Tanks::select('id', 'name')->where(['site_id'=>$atg_readings->site_id, 'status'=>'Active'])->get();
        $returnData = array('status' => 'success', 'data' => $tanks, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

}
