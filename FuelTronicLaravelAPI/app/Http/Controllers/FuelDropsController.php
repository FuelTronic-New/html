<?php

namespace App\Http\Controllers;

use App\Interfaces\FuelDropRepositoryInterface;
use App\Interfaces\TankRepositoryInterface;
use App\Models\FuelDrop;
use App\Models\Sites;
use App\Models\Tanks;
use App\User;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class FuelDropsController extends JwtAuthController
{
    public function __construct(Request $request, FuelDropRepositoryInterface $fuelDropRepo, TankRepositoryInterface $tankRepo)
    {
        parent::__construct();
        $this->fuel_drop = $fuelDropRepo;
        $this->tank = $tankRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->fuel_drop->getAll();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Drops not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'site_id' => 'required|numeric',
            'tank_id' => 'required|numeric',
            'litres' => 'required|numeric',
            'purchase_date' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            //$Data['purchase_date'] = \Carbon\Carbon::parse($this->request->purchase_date)->format('Y-m-d H:i:s');
            $Data['grade_id'] = Tanks::where('id', $Data['tank_id'])->pluck('grade_id');
            $this->fuel_drop->store($Data);
            // Add amount to tanks
            $updateTankLitres = $this->tank->updateFuel($Data['tank_id'], $Data['litres']);
            $returnData = array('status' => 'success', 'message' => 'Fuel Drop saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Drop not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $fuelDrop = $this->fuel_drop->show($id);
            $returnData = array('status' => 'success', 'data' => $fuelDrop, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Drop not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'site_id' => 'required|numeric',
            'tank_id' => 'required|numeric',
            'litres' => 'required|numeric',
            'purchase_date' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $fuelDropData = $this->fuel_drop->show($id);
	        if(!$fuelDropData){
	            return response()->json(['status' => 'failure', 'message' => 'Fuel Drop not found', 'code' => 400],400);
	         }
            $litresDiff = $Data['litres'] - $fuelDropData->litres;

            //$Data['purchase_date'] = \Carbon\Carbon::parse($this->request->purchase_date)->format('Y-m-d H:i:s');
            $Data['grade_id'] = Tanks::where('id', $Data['tank_id'])->pluck('grade_id');
            $this->fuel_drop->update($id, $Data);
            // Update amount to tanks
            $updateTankLitres = $this->tank->updateFuel($Data['tank_id'], $litresDiff);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Fuel Drop updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Drop not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $fuelDropData = $this->fuel_drop->show($id);
            $litresDiff = 0 - $fuelDropData->litres;
            $fuelDrop = $this->fuel_drop->delete($id);
            // Update amount to tanks
            $updateTankLitres = $this->tank->updateFuel($fuelDropData->tank_id, $litresDiff);
            $returnData = array('status' => 'success', 'message' => 'Fuel Drop deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Drop not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create($siteId)
    {
        $sites = auth()->user()->load(['sites'=>function($query) use ($siteId){
            return $query->whereId($siteId);
        }, 'sites.tanks', 'sites.suppliers' => function ($q) {
        			    return $q->where('status', 'active');
        		    },'sites.tanks.grades']);
        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }


    public function edit($id)
    {
        $fuelDrop = FuelDrop::find($id);
	    if(!$fuelDrop){
		    return response()->json(['status' => 'failure', 'message' => 'Fuel Drop not found', 'code' => 400],400);
		 }
        $saved_site = $fuelDrop->site_id;
	    $sites = auth()->user()->load([
		    'sites'           => function ($query) use ($saved_site) {
			    return $query->whereId($saved_site);
		    },
		    'sites.suppliers' => function ($q) {
			    return $q->where('status', 'active');
		    }
	    , 'sites.tanks', 'sites.tanks.grades']);


        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function getLitrePrice($tankID)
    {
        $tankGradePrice = Tanks::with('grades')->find($tankID);
        $returnData = array('status' => 'success', 'price' => $tankGradePrice->grades->price, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }
    
}
