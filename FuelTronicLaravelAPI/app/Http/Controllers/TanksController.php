<?php

namespace App\Http\Controllers;

use App\Interfaces\TankRepositoryInterface;
use App\Models\AtgData;
use App\Models\AtgReadings;
use App\Models\CustomerTransaction;
use App\Models\FuelDrop;
use App\Models\Grades;
use App\Models\Tanks;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class TanksController extends JwtAuthController
{
    public function __construct(Request $request, TankRepositoryInterface $tankRepo)
    {
        parent::__construct();
        $this->tank = $tankRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->tank->getAll();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tanks not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'grade_id' => 'required|numeric',
            'site_id' => 'required|numeric',
            'atg' => 'required',
            'manual_reading' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->tank->store($Data);
            $returnData = array('status' => 'success', 'message' => 'Tank saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tank not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $tank = $this->tank->show($id);
	        if(!$tank){
	            return response()->json(['status' => 'failure', 'message' => 'Tank not found', 'code' => 400],400);
	         }
	        $atg = AtgData::where('site_id', $tank->site_id)->get();
            $returnData = array('status' => 'success', 'data' => ['tank'=>$tank,'atg'=>$atg], 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tank not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'grade_id' => 'required|numeric',
            'site_id' => 'required|numeric',
            'atg' => 'required',
            'manual_reading' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->tank->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Tank updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tank not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $tank = $this->tank->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Tank deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tank not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create($siteID)
    {
        $grades = Grades::select('id', 'name')->where('site_id', $siteID)->get();
	    $atg = AtgData::where('site_id', $siteID)->get();
        $fuelDrop = FuelDrop::where('site_id', $siteID)->sum('litres');
        $custTransactions = CustomerTransaction::where('site_id', $siteID)->sum('litres');
        $addToInitial = $fuelDrop - $custTransactions;
        $returnData = array('status' => 'success', 'data' => ['grades' => $grades,'atg' => $atg, 'addToInitial' => $addToInitial], 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }


    public function edit($tankId)
    {
        $tankDetails = Tanks::find($tankId);
	    if(!$tankDetails){
	         return response()->json(['status' => 'failure', 'message' => 'Tank not found', 'code' => 400],400);
	      }
	    $atg = AtgData::where('site_id', $tankDetails->site_id)->get();
        $grades = Grades::select('id', 'name')->where('site_id', $tankDetails->site_id)->get();
        $lastDipReading = '';
        if($tankId != ''){
            if(!empty($tankDetails)){
                $lastDipReading = AtgReadings::where('tank_id', $tankId)->orderBy('id', 'DESC')->pluck('litre_readings');
            }
        }
        $fuelDrop = FuelDrop::where('site_id', $tankDetails->site_id)->sum('litres');
        $customerTransactions = CustomerTransaction::where('site_id', $tankDetails->site_id)->sum('litres');
        $addToInitial = $fuelDrop - $customerTransactions;
        $returnData = array('status' => 'success', 'data' => ['grades' => $grades,'atg' => $atg, 'addToInitial' => $addToInitial, 'lastDipReading' => $lastDipReading], 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

}
