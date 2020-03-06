<?php

namespace App\Http\Controllers;

use App\Interfaces\FuelTransferRepositoryInterface;
use App\Interfaces\TankRepositoryInterface;
use App\Models\FuelTransfer;
use App\Models\Tanks;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class FuelTransfersController extends JwtAuthController
{
    public function __construct(Request $request, FuelTransferRepositoryInterface $fuelTransferRepo, TankRepositoryInterface $tankRepo)
    {
        parent::__construct();
        $this->fuel_transfer = $fuelTransferRepo;
        $this->tank = $tankRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->fuel_transfer->getAll();
	        $sites = auth()->user()->sites()->select('id', 'name')->get();
            $returnData = array('status' => 'success', 'data' => $Data, 'sites' => $sites, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Transfers not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'from_site' => 'required|numeric',
            'from_tank' => 'required|numeric',
            'to_site' => 'required|numeric',
            'to_tank' => 'required|numeric',
            'litres' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->fuel_transfer->store($Data);
            // Update amount to tanks - From Tank
            $fromTankDetail = $this->tank->show($Data['from_tank']);
            if($fromTankDetail) {
	            $litresDiffFromTank = $fromTankDetail->litres - $Data['litres'];
	            $updateFromTank = $this->tank->updateFuel($Data['from_tank'], $litresDiffFromTank);
            }
            // Update amount to tanks - To Tank
            $toTankDetail = $this->tank->show($Data['from_tank']);
            if($toTankDetail) {
	            $litresDiffToTank = $toTankDetail->litres + $Data['litres'];
	            $updateFromTank = $this->tank->updateFuel($Data['to_tank'], $litresDiffToTank);
            }
            $returnData = array('status' => 'success', 'message' => 'Fuel Transfer saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Transfer not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $fuelDrop = $this->fuel_transfer->show($id);
            $returnData = array('status' => 'success', 'data' => $fuelDrop, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Transfer not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'from_site' => 'required|numeric',
            'from_tank' => 'required|numeric',
            'to_site' => 'required|numeric',
            'to_tank' => 'required|numeric',
            'litres' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            // Update amount to tanks - From Tank
            $oldFuelTransfer = $this->fuel_transfer->show($id);
	        if(!$oldFuelTransfer){
	            return response()->json(['status' => 'failure', 'message' => 'Fuel Transfer not found', 'code' => 400],400);
	         }

            $this->fuel_transfer->update($id, $Data);

            $oldFromTankDetail = $this->tank->show($Data['from_tank']);
            if($oldFromTankDetail) {
	            $litresDiffFromTank = $oldFromTankDetail->litres - ($Data['litres'] - $oldFuelTransfer->litres);
	            $updateFromTank = $this->tank->updateFuel($Data['from_tank'], $litresDiffFromTank);
            }

            // Update amount to tanks - To Tank
            $oldToTankDetail = $this->tank->show($Data['from_tank']);
            if($oldToTankDetail) {
	            $litresDiffToTank = $oldToTankDetail->litres + ($Data['litres'] - $oldFuelTransfer->litres);
	            $updateFromTank = $this->tank->updateFuel($Data['to_tank'], $litresDiffToTank);
            }
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Fuel Transfer updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Transfer not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $fuelDrop = $this->fuel_transfer->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Fuel Transfer deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Fuel Transfer not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create()
    {
        $sites = auth()->user()->load(['sites', 'sites.tanks']);
        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function edit($id)
    {
        $fuelTransfer = FuelTransfer::find($id);
        $from_site = $fuelTransfer->from_site;
        $to_site = $fuelTransfer->to_site;
        $sites = auth()->user()->load(['sites', 'sites.tanks']);
        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

}
