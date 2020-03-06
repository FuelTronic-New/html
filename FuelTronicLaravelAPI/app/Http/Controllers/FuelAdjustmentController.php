<?php

namespace App\Http\Controllers;

use App\Interfaces\FuelAdjustmentRepositoryInterface;
use App\Interfaces\TankRepositoryInterface;
use App\Models\FuelAdjustment;
use App\Models\Payment;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class FuelAdjustmentController extends JwtAuthController
{
    public function __construct(Request $request, FuelAdjustmentRepositoryInterface $fuelAdjustmentRepo, TankRepositoryInterface $tankRepo)
    {
        parent::__construct();
        $this->fuel_adjustment = $fuelAdjustmentRepo;
        $this->tank = $tankRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->fuel_adjustment->getAll();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Adjustments not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $Data['created_by'] = auth()->user()->id;
        $validator = \Validator::make($Data, [
            'site_id' => 'required|numeric',
            'tank_id' => 'required|numeric',
            'litres' => 'required|numeric',
            'motivation' => 'required',
            'mode' => 'required'
        ]);

        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->fuel_adjustment->store($Data);

            // Update amount to tanks - To Tank
            $toTankDetail = $this->tank->show($Data['tank_id']);
            if($toTankDetail) {
	            if ($Data['mode'] == '+') {
		            $litresDiffToTank = $toTankDetail->litres + $Data['litres'];
	            }
	            else {
		            $litresDiffToTank = $toTankDetail->litres - $Data['litres'];
	            }
	            $updateFromTank = $this->tank->updateFuel($Data['tank_id'], $litresDiffToTank);
            }
            $returnData = array('status' => 'success', 'message' => 'Fuel Adjustment saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Adjustment not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $tank = $this->fuel_adjustment->show($id);
            $returnData = array('status' => 'success', 'data' => $tank, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Adjustment not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $Data['created_by'] = auth()->user()->id;
        $validator = \Validator::make($Data, [
            'site_id' => 'required|numeric',
            'tank_id' => 'required|numeric',
            'litres' => 'required|numeric',
            'motivation' => 'required',
            'mode' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $currentAdjustment = FuelAdjustment::find($id);
            if(!$currentAdjustment){
            	return response()->json(['status' => 'failure', 'message' => 'Fuel Adjustment not found', 'code' => 400],400);
            }
            $this->fuel_adjustment->update($id, $Data);
            // Update amount to tanks - To Tank
            $toTankDetail = $this->tank->show($Data['tank_id']);
            if($toTankDetail) {
	            if ($Data['mode'] == '+') {
		            $litresDiffToTank = $toTankDetail->litres - $currentAdjustment->litres + $Data['litres'];
	            }
	            else {
		            $litresDiffToTank = $toTankDetail->litres - $currentAdjustment->litres - $Data['litres'];
	            }
	            $updateFromTank = $this->tank->updateFuel($Data['tank_id'], $litresDiffToTank);
            }
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Fuel Adjustment updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Adjustment not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->fuel_adjustment->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Fuel Adjustment deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Adjustment not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create($siteId)
    {
        $sites = auth()->user()->load(['sites' => function ($query) use ($siteId) {
            return $query->whereId($siteId);
        }, 'sites.tanks' => function ($que) {
            return $que->whereStatus('Active');
        }]);
        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function edit($id)
    {
        $fuelAdjustment = FuelAdjustment::find($id);
	    $sites = [];
        if($fuelAdjustment) {
	        $saved_site = $fuelAdjustment->site_id;
	        $sites = auth()->user()->load([
		        'sites'       => function ($query) use ($saved_site) {
			        return $query->whereId($saved_site);
		        },
		        'sites.tanks' => function ($que) {
			        return $que->whereStatus('Active');
		        }
	        ]);
        }
        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

}
