<?php

namespace App\Http\Controllers;

use App\Interfaces\HoseRepositoryInterface;
use App\Models\Hoses;
use App\Models\Pumps;
use App\Models\Tanks;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class HosesController extends JwtAuthController
{
    public function __construct(Request $request, HoseRepositoryInterface $hoseRepo)
    {
        parent::__construct();
        $this->hose = $hoseRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->hose->getAll();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Hose not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'pump_id' => 'required|numeric',
            'tank_id' => 'required|numeric',
            'site_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->hose->store($Data);
            $returnData = array('status' => 'success', 'message' => 'Hose saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
                $returnData = array('status' => 'failure', 'message' => 'Hose not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }


    public function show($id)
    {
        try {
            $hose = $this->hose->show($id);
            $returnData = array('status' => 'success', 'data' => $hose, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Hose not found', 'code' => 400);
        }
        return response()->json($returnData, 200);
    }

    public function update($id)
    {
        $Data = $this->request->all();

        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'pump_id' => 'required|numeric',
            'tank_id' => 'required|numeric',
            'site_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->hose->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Hose updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Hose not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);

    }

    public function delete($id)
    {
        try {
            $this->hose->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Hose deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Hose not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create($siteID)
    {
        $pumps = Pumps::select('id', 'name')->where('site_id', $siteID)->get();
        $tanks = Tanks::with('grades')->select('id', 'name', 'grade_id')->where('site_id', $siteID)->get();
        $returnData = array('status' => 'success', 'data' => array('pumps' => $pumps, 'tanks' => $tanks), 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }


    public function edit($id)
    {
        $hoseData = Hoses::find($id);
	    if(!$hoseData){
	        return response()->json(['status' => 'failure', 'message' => 'Hose not found', 'code' => 400],400);
	     }
        $pumps = Pumps::select('id', 'name')->where('site_id', $hoseData->site_id)->get();
        $tanks = Tanks::with('grades')->select('id', 'name', 'grade_id')->where('site_id', $hoseData->site_id)->get();
        $returnData = array('status' => 'success', 'data' => array('pumps' => $pumps, 'tanks' => $tanks), 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

}
