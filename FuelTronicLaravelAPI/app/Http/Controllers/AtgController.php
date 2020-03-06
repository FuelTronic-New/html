<?php

namespace App\Http\Controllers;

use App\Models\AtgData;
use App\Models\AtgTransaction;
use App\Models\Attendants;
use App\Models\Customers;
use App\Models\CustomerTransaction;
use App\Models\Hoses;
use App\Models\Grades;
use App\Models\Jobs;
use App\Models\Location;
use App\Models\Pumps;
use App\Models\Tanks;
use App\Models\Vehicles;
use App\Models\Tags;
use JWTAuth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;
use App\User;

class AtgController extends Controller {

    public function __construct(Request$request) {
        $this->request = $request;
    }

    public function getAtgDetail($guid) {
        try {
            $atgData = AtgData::where('guid', $guid)->first();
            $returnData = array('status' => 'success', 'data' => $atgData, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG not found.', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store($guid) {
        $Data = $this->request->all();
        $Data['guid'] = $guid;
        $validator = \Validator::make($Data, ['name' => 'required|max:255', 'ip_address' => 'required|max:255', 'port_num' => 'required|numeric', 'tank_type' => 'required|numeric', 'guid' => 'required|max:255',]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $atgData = AtgData::where('guid', $guid)->first();
            if (empty($atgData)) {
                // Create New
                AtgData::create($Data);
                $message = "ATG Data saved successfully!";
            } else {
                // Update
                AtgData::findOrFail($atgData->id)->update($Data);
                $message = "ATG Data updated successfully!";
            }
            $returnData = array('status' => 'success', 'message' => $message, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => '', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function storeTransaction($guid) {
        $Data = $this->request->all();
        $Data['guid'] = $guid;
        $validator = \Validator::make($Data, ['guid' => 'required|max:255', 'cm' => 'required|numeric', 'liters' => 'required|max:255',]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $atgData = AtgData::where('guid', $guid)->first();
            if ($atgData) {
                $Data['atg_id'] = $atgData->id;
                $atgTrans = AtgTransaction::create($Data);
                Tanks::where('atg_id', $atgData->id)->update(['cur_atg_level' => $atgTrans->liters]);
                $message = "ATG Transaction saved successfully!";
                $returnData = array('status' => 'success', 'message' => $message, 'code' => 200);
            }else{
	            $returnData = array('status' => 'failure', 'message' => 'ATG not found', 'code' => 400);
            }
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    /* Pump System Functions */

    public function getPumpDetail($guid) {
        try {
	        $pump = Pumps::has('site')->has('hose')->with([
		        'site' => function ($q1) {
			        $q1->with(['attendants.tag', 'vehicles.tag', 'vehicles.customer', 'jobs.tag', 'locations']);
		        },
		        'hose.tank'
	        ])->where('guid', $guid)->first();
	        if ($pump) {
		        if (!$pump->hose->tank) {
			        return response()->json([ 'status' => 'failure', 'message' => "Tank not found", 'code' => 400], 400);
		        }
		        $Tanks = Tanks::has('grades')->with([
			        'grades' => function ($q1) {
				        $q1->where('rate_increased_at', '<>', date("Y-m-d H:i:s"))->first();
			        }
		        ])->where('id', $pump->hose->tank->id)->first();
		        /* location */
		        $data['Location'] = (string)$pump->location;
		        $data['Locations'] = $pump->site->locations;
		        /* attendant */
		        $data['Attendant'] = (string)$pump->attendent_tag;
		        $data['Attendants'] = $pump->site->attendants;
		        foreach ($data['Attendants'] as $Attendant) {
			        $Attendant['pin'] = intval($Attendant['pin']);
		        }
		        /* vehicle */
		        $data['Vehicle'] = (string)$pump->vehicle_tag;
		        $tempVehicles = $pump->site->vehicles;
		        foreach ($tempVehicles as $Vehicle) {
			        $Vehicle['odo_meter'] = intval($Vehicle['odo_meter']);
			        $Vehicle['fuel_price'] = ($Vehicle->customer) ? $Vehicle->customer->fuel_price : 0;
			        unset($Vehicle->customer);
			        $data['Vehicles'][] = $Vehicle;
		        }

		        $data['Odometer'] = $pump->odo_meter;
		        $data['order_number'] = $pump->order_number;
		        $data['driver'] = $pump->driver_fingerprint;
		        $data['Calibration'] = $pump->code;
		        $data['Pin'] = $pump->pin;
		        $data['Job'] = (string)$pump->job_tag;
		        $data['Jobs'] = $pump->site->jobs;
		        $data['Tank'] = intval($pump->hose->tank->litre);
		        $data['hose_id'] = $pump->hose->id;
		        $data['pump_id'] = $pump->id;
		        $data['TankVol'] = intval($pump->hose->tank->volume);
		        $data['TankMin'] = intval($pump->hose->tank->min_level);
		        $data['FuelPrice'] = ($Tanks) ? floatval($Tanks->grades->price) : 0;
		        return response()->json([ 'status' => 'success','data'   => $data, 'code' => 200], 200);
	        }
	        else {
		        return response()->json([ 'status' => 'failure', 'message' => 'Pump not found', 'code' => 400], 400);
	        }
        }
        catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function getAttendantDetail($guid) {
        try {
            $pump = Pumps::has('site')->with(['site' => function ($q1) {
                            $q1->with('attendants');
                        }])->where('guid', $guid)->first();
            if ($pump) {
                foreach ($pump->site->attendants as $key => $attendant) {
                    $data[$key] = [];
                    $data[$key]['TagNumber'] = $guid;
                    $data[$key]['AttendantID'] = $attendant->id;
                    $data[$key]['PIN'] = $attendant->pin;
                    $data[$key]['Fuel Allocation'] = $attendant->fuel_allocation;
                }
                $returnData = array('status' => 'success', 'data' => $data, 'code' => 200);
            } else {
                $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
            }
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function getVehicleDetail($guid) {
        try {
            $pump = Pumps::has('site')->with(['site' => function ($q1) {
                            $q1->with('vehicles');
                        }])->where('guid', $guid)->first();
            if ($pump) {
                foreach ($pump->site->vehicles as $key => $vehicle) {
                    $data[$key] = [];
                    $data[$key]['TagNumber'] = $guid;
                    $data[$key]['VehicleID'] = $vehicle->id;
                    $data[$key]['LastODO'] = $vehicle->odo_meter;
                    $data[$key]['Fuel Allocation'] = $vehicle->fuel_allocation;
                }
                $returnData = array('status' => 'success', 'data' => $data, 'code' => 200);
            } else {
                $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
            }
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function getJobDetail($guid) {
        try {
            $pump = Pumps::has('site')->with(['site' => function ($q1) {
                            $q1->with('jobs');
                        }])->where('guid', $guid)->first();
            if ($pump) {
                foreach ($pump->site->jobs as $key => $job) {
                    $data[$key] = [];
                    $data[$key]['TagNumber'] = $guid;
                    $data[$key]['JobID'] = $job->id;
                    $data[$key]['Fuel Allocation'] = $job->fuel_allocation;
                }
                $returnData = array('status' => 'success', 'data' => $data, 'code' => 200);
            } else {
                $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
            }
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function customertransaction($guid) {
        try {
            $pump = Pumps::has('site')->with(['site' => function ($q1) {
                            $q1->has('customer_transaction')->with('customer_transaction');
                        }])->where('guid', $guid)->first();
            if ($pump) {
                foreach ($pump->site->customer_transaction as $key => $ct) {
                    $data[$key] = [];
                    $data[$key]['hose_id'] = $ct->hose_id;
                    $data[$key]['job_id'] = $ct->job_id;
                    $data[$key]['odo_meter'] = $ct->odo_meter;
                    $data[$key]['attendant_id'] = $ct->attendant_id;
                    $data[$key]['vehicle_id'] = $ct->vehicle_id;
                    $data[$key]['litres'] = $ct->litres;
                    $data[$key]['start_date'] = $ct->start_date;
                    $data[$key]['end_date'] = $ct->end_date;
                }
                $returnData = array('status' => 'success', 'data' => $data, 'code' => 200);
            } else {
                $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
            }
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function createcustomertransaction($guid) {
	    try {
        $Data = $this->request->all();
    //    Log::info('createcustomertransaction ' . print_r($Data, true).' guid : '.$guid);
	    $validator = \Validator::make($Data, [
		    'hose_id'      => 'required|numeric',
		    'attendant_id' => 'required|numeric',
		    'vehicle_id'   => 'required|numeric',
//		    'location_id'  => 'required|numeric',
//		    'job_id'       => 'required|numeric',
		    'litres'       => 'required|numeric',
	    ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
        //    Log::error('createcustomertransaction $validator->fails' . print_r($returnData, true));
            return response()->json($returnData, 400);
        }

        	// check any pump has the guid or not
            $pump = Pumps::where('guid', $guid)->first();

            if (!$pump) {
          //     Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Pump not found.!'], true));
                return response()->json(['status' => 'error', 'message' => 'Pump not found.', 'code' => 400], 400);
            }
            $site_id = $pump->site_id;

			// check the request location id is belongs to the site of the pump or not if not it return error
//            $location = Location::whereHas('sites', function($q1) use($site_id){
//            	$q1->where('site_id', $site_id);
//            })->where('id', $Data['location_id'])->first();

//            if (!$location) {
//                Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Location not found.!'], true));
//                return response()->json(['status' => 'error', 'message' => 'Location not found for the site of the pump.', 'code' => 400], 400);
//            }

	        // check the request attendant id is belongs to the site of the pump or not if not it return error
	        if($pump->attendent_tag) {
		        $attendant = Attendants::whereHas('sites', function ($q1) use ($site_id) {
			        $q1->where('site_id', $site_id);
		        })->where('id', $Data['attendant_id'])->first();
		        if (!$attendant) {
			     //   Log::error('createcustomertransaction' . print_r(['status'  => 'error',
			  //                                                      'message' => 'Attendant not found for the site of the pump.'
			//	        ], true));
			        return response()->json(['status'  => 'error',
			                                 'message' => 'Attendant not found for the site of the pump.',
			                                 'code'    => 400
			        ], 400);
		        }
	        }

	        // check the request job id is belongs to the site of the pump or not if not it return error
	        if($pump->job_tag) {
		        $job = Jobs::whereHas('sites', function ($q1) use ($site_id) {
			        $q1->where('site_id', $site_id);
		        })->where('id', $Data['job_id'])->first();
		        if (!$job) {
			      //  Log::error('createcustomertransaction' . print_r(['status'  => 'error',
			    //                                                      'message' => 'Job not found for the site of the pump.'
				        //], true));
			        return response()->json(['status'  => 'error',
			                                 'message' => 'Job not found for the site of the pump.',
			                                 'code'    => 400
			        ], 400);
		        }
	        }

	        // check the request hose id is belongs to the site of the pump or not if not it return error
            $hoseDetail = Hoses::select('tank_id', 'site_id')->where('id', $Data['hose_id'])->where('site_id', $site_id)->first();

            if(!$hoseDetail){
	       //     Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Hose not found for the site of the pump.'], true));
	            return response()->json(['status' => 'error', 'message' => 'Hose not found for the site of the pump.', 'code' => 400], 400);
            }
            $Data['site_id'] = $hoseDetail->site_id;
            $tank_id = $hoseDetail->tank_id;
            // Tank id to update litres
	        // check the tank id of the hose is belongs to the site of the pump or not if not it return error
            $tankDetail = Tanks::where('site_id', $site_id)->find($tank_id);

	        if(!$tankDetail){
	       //   Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Tank not found for the site of the pump.'], true));
	          return response()->json(['status' => 'error', 'message' => 'Tank not found for the site of the pump.', 'code' => 400], 400);
	         }
	        // check the grade id of the tank is belongs to the site of the pump or not if not it return error
            $GradesDetail = Grades::where('site_id', $site_id)->find($tankDetail->grade_id);

	        if(!$GradesDetail){
	   //       Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Grade not found for the site of the pump.'], true));
	          return response()->json(['status' => 'error', 'message' => 'Grade not found for the site of the pump.', 'code' => 400], 400);
	         }
            $litresDiff = $tankDetail->litres - $Data['litres'];

	        // check the request vehicle id is belongs to the site of the pump or not if not it return error
	        if($pump->vehicle_tag) {
		        $vehicle = Vehicles::whereHas('sites', function ($q1) use ($site_id) {
			        $q1->where('site_id', $site_id);
		        })->where('id', $Data['vehicle_id'])->first();
		        if (!$vehicle) {
			 //       Log::error('createcustomertransaction' . print_r(['status'  => 'error',
			  //                                                       'message' => 'vehicle not found for the site of the pump.'
			//	      ], true));
			        return response()->json(['status'  => 'error',
			                                 'message' => 'vehicle not found for the site of the pump.',
			                                 'code'    => 400
			        ], 400);
		        }
		        $Data['customer_id'] = $vehicle->customer_id;
		        // check the customer id of the vehicle is belongs to the site of the pump or not if not it return error
		        $customer = Customers::whereHas('sites', function ($q1) use ($site_id) {
			        $q1->where('site_id', $site_id);
		        })->find($Data['customer_id']);
		        if (!$customer) {
			///        Log::error('createcustomertransaction' . print_r(['status'  => 'error',
		//	                                                          'message' => 'customer not found for the site of the pump.'
		//		        ], true));
			        return response()->json(['status'  => 'error',
			                                 'message' => 'customer not found for the site of the pump.',
			                                 'code'    => 400
			        ], 400);
		        }
		        $tPrice = $GradesDetail->price;
		        if ($customer && $customer->fuel_price > 0) {
			        $tPrice = $customer->fuel_price;
		        }
		        $Data['pump_id'] = $pump->id;
		        $Data['name'] = $GradesDetail->id;
		        $Data['total_cost'] = $tPrice * $Data['litres'];
		        $Data['vat'] = $Data['total_cost'] / (100 + $GradesDetail->vat_rate) * $GradesDetail->vat_rate;
		        $Data['cost_exc_vat'] = $Data['total_cost'] / (100 + $GradesDetail->vat_rate) * 100;
	        } else {
		        $Data['customer_id'] = 0;
                $tPrice = $GradesDetail->price;
                $Data['pump_id'] = $pump->id;
                $Data['name'] = $GradesDetail->id;
                $Data['total_cost'] = $tPrice * $Data['litres'];
                $Data['vat'] = $Data['total_cost'] / (100 + $GradesDetail->vat_rate) * $GradesDetail->vat_rate;
                $Data['cost_exc_vat'] = $Data['total_cost'] / (100 + $GradesDetail->vat_rate) * 100;
	        }

	        /* Uncomment this at last
	        $deleteDuplicates = CustomerTransaction::where('hose_id', $Data['hose_id'])
					            ->where('start_date', $Data['start_date'])
					            ->where('end_date', $Data['end_date'])
					            ->delete();
	        */
$exist_record = CustomerTransaction::where('hose_id', $Data['hose_id'])
					            ->where('start_date', $Data['start_date'])
					            ->where('end_date', $Data['end_date'])
					            ->get();
	if(count($exist_record)>0){
     //   Log::info('createcustomertransaction' . print_r(['status' => 'success', 'message' => 'This record already exist in database.'], true));
	       return response()->json(['status' => 'success', 'message' => 'This record already exist in database', 'code' => 200], 200);
	}else{							

            $message = CustomerTransaction::create($Data);
            // Update amount to tanks
            $Data['litre'] = $tankDetail->litre + $litresDiff;
            $tankDetail->touch();
	        $pump->touch();
         //   DB::select('CALL UpdateTankLevel(?)',array($tank_id));
	}
	$returnData = array('status' => 'success', 'message' => 'Success ' . $message, 'code' => 200);
     //       Log::info('createcustomertransaction' . print_r($returnData, true));
        }
        catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'transaction not saved. Reason: '.$e->getMessage(), 'code' => 400);
	 //       Log::error('createcustomertransaction catch error' . print_r($returnData, true).' '.json_encode($e->getMessage()));
        }
        return response()->json($returnData, $returnData['code']);
    }

}
