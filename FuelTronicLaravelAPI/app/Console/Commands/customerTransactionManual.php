<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
use JWTAuth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class customerTransactionManual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customerTransactionManual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'customerTransactionManual';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $Data = [
//        	'hose_id' => 16,
//        	'pump_id' => 16,
//        	'odo_meter' => 0,
//	        'attendant_id' => 27,
//	        'vehicle_id' => 4,
//	        'job_id' => -1,
//	        'litres' => 0.00,
//        	'start_date' => '2018-04-20 12:10:36',
//        	'end_date' => '2018-04-20 12:12:21'
//        ];

	    $path = storage_path() . "/json/res1.json"; // ie: /var/www/laravel/app/storage/json/filename.json
	    $json = json_decode(file_get_contents($path), true);

	    if(!empty($json)){

	    	foreach ($json as $key =>  $Data){

	    		echo $key.' --------- Started';

//	    		echo '<pre>';
//	    		print_r($Data);
//			    echo '</pre>';

//			    Log::info('createcustomertransaction ' . print_r($Data, true).' guid : ');
	            $validator = \Validator::make($Data, [
	                'hose_id'      => 'required|numeric',
	                'attendant_id' => 'required|numeric',
	                'vehicle_id'   => 'required|numeric',
	    //            'location_id'  => 'required|numeric',
	    //            'job_id'       => 'required|numeric',
	                'litres'       => 'required|numeric',
	            ]);
	            if ($validator->fails()) {
	                $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
	                Log::error('createcustomertransaction $validator->fails' . print_r($returnData, true));
	                echo 'createcustomertransaction $validator->fails';
		            echo $key.' --------- Ended';
	                continue;
	                //return response()->json($returnData, 400);
	            }
	            try {
	                // check any pump has the guid or not
	    //            $pump = Pumps::where('guid', $guid)->first();
	                $pump = Pumps::find($Data['pump_id']);

	                //dd($pump->job_tag, $pump->vehicle_tag, $pump->attendent_tag);

	                if (!$pump) {
	                    Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Pump not found.!'], true));
	                    echo 'Pump not found';
		                echo $key.' --------- Ended';
		                continue;
//	                    return response()->json(['status' => 'error', 'message' => 'Pump not found.', 'code' => 400], 400);
	                }
	                $site_id = $pump->site_id;

	                // check the request location id is belongs to the site of the pump or not if not it return error
	    //            $location = Location::whereHas('sites', function($q1) use($site_id){
	    //                $q1->where('site_id', $site_id);
	    //            })->where('id', $Data['location_id'])->first();

	    //            if (!$location) {
	    //                Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Location not found.!'], true));
	    //                return response()->json(['status' => 'error', 'message' => 'Location not found for the site of the pump.', 'code'
	    //                => 400], 400);
	    //            }

	                // check the request attendant id is belongs to the site of the pump or not if not it return error
	                if($pump->attendent_tag) {
	                    $attendant = Attendants::whereHas('sites', function ($q1) use ($site_id) {
	                        $q1->where('site_id', $site_id);
	                    })->where('id', $Data['attendant_id'])->first();
	                    if (!$attendant) {
	                        Log::error('createcustomertransaction' . print_r(['status'  => 'error', 'message' => 'Attendant not found for the site of the pump.'], true));
	                        echo 'Attendant not found for the site of the pump.';
		                    echo $key.' --------- Ended';
		                    continue;
//	                        return response()->json(['status'  => 'error', 'message' => 'Attendant not found for the site of the pump.', 'code' => 400], 400);
	                    }
	                }

	                // check the request job id is belongs to the site of the pump or not if not it return error
	                if($pump->job_tag){
	                    $job = Jobs::whereHas('sites', function ($q1) use ($site_id) {
	                        $q1->where('site_id', $site_id);
	                    })->where('id', $Data['job_id'])->first();

	                    if (!$job) {
	                        Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Job not found for the site of the pump.'], true));
	                        echo 'Job not found for the site of the pump.';
		                    echo $key.' --------- Ended';
		                    continue;
//	                        return response()->json(['status' => 'error', 'message' => 'Job not found for the site of the pump.', 'code' => 400], 400);
	                    }
	                }

	                // check the request hose id is belongs to the site of the pump or not if not it return error
	                $hoseDetail = Hoses::select('tank_id', 'site_id')->where('id', $Data['hose_id'])->where('site_id', $site_id)->first();

	                if(!$hoseDetail){
	                    Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Hose not found for the site of the pump.'], true));
	                    echo 'Hose not found for the site of the pump.';
		                echo $key.' --------- Ended';
		                continue;
//	                    return response()->json(['status' => 'error', 'message' => 'Hose not found for the site of the pump.', 'code' => 400], 400);
	                }

	                $Data['site_id'] = $hoseDetail->site_id;
	                $tank_id = $hoseDetail->tank_id;
	                // Tank id to update litres
	                // check the tank id of the hose is belongs to the site of the pump or not if not it return error
	                $tankDetail = Tanks::where('site_id', $site_id)->find($tank_id);

	                if(!$tankDetail){
	                  Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Tank not found for the site of the pump.'], true));
	                  echo 'Tank not found for the site of the pump.';
		                echo $key.' --------- Ended';
	                  continue;
//	                  return response()->json(['status' => 'error', 'message' => 'Tank not found for the site of the pump.', 'code' => 400], 400);
	                 }
	                // check the grade id of the tank is belongs to the site of the pump or not if not it return error
	                $GradesDetail = Grades::where('site_id', $site_id)->find($tankDetail->grade_id);

	                if(!$GradesDetail){
	                  Log::error('createcustomertransaction' . print_r(['status' => 'error', 'message' => 'Grade not found for the site of the pump.'], true));
		                echo 'Grade not found for the site of the pump.';
		                echo $key.' --------- Ended';
		                continue;
//	                  return response()->json(['status' => 'error', 'message' => 'Grade not found for the site of the pump.', 'code' => 400], 400);
	                 }
	                $litresDiff = $tankDetail->litres - $Data['litres'];

	                // check the request vehicle id is belongs to the site of the pump or not if not it return error
	                if($pump->vehicle_tag) {
	                    $vehicle = Vehicles::whereHas('sites', function ($q1) use ($site_id) {
	                        $q1->where('site_id', $site_id);
	                    })->where('id', $Data['vehicle_id'])->first();
	                    if (!$vehicle) {
	                        Log::error('createcustomertransaction' . print_r(['status'  => 'error', 'message' => 'vehicle not found for the site of the pump.' ], true));
		                    echo 'vehicle not found for the site of the pump.';
		                    echo $key.' --------- Ended';
		                    continue;
//	                        return response()->json(['status'  => 'error', 'message' => 'vehicle not found for the site of the pump.', 'code'    => 400], 400);
	                    }
	                    $Data['customer_id'] = $vehicle->customer_id;
	                    // check the customer id of the vehicle is belongs to the site of the pump or not if not it return error
	                    $customer = Customers::whereHas('sites', function ($q1) use ($site_id) {
	                        $q1->where('site_id', $site_id);
	                    })->find($Data['customer_id']);
	                    if (!$customer) {
	                        Log::error('createcustomertransaction' . print_r(['status'  => 'error', 'message' => 'customer not found for the site of the pump.' ], true));
	                        echo 'customer not found for the site of the pump.';
		                    echo $key.' --------- Ended';
		                    continue;
//	                        return response()->json(['status'  => 'error', 'message' => 'customer not found for the site of the pump.', 'code' => 400 ], 400);
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

	    	        $deleteDuplicates = CustomerTransaction::where('hose_id', $Data['hose_id'])
	    		        ->where('start_date', $Data['start_date'])
	    		        ->where('end_date', $Data['end_date'])
	    		        ->delete();

//		            echo '<pre>';
//	                print_r($Data);
//		            echo '</pre>';
//		            echo $key.' --------- Ended';
//	                continue;

	                $message = CustomerTransaction::create($Data);

	                // Update amount to tanks
	                $Data['litre'] = $tankDetail->litre + $litresDiff;
	                $tankDetail->touch();
	                $pump->touch();
	                DB::select('CALL UpdateTankLevel(?)',array($tank_id));
	                $returnData = array('status' => 'success', 'message' => 'Success ' . $message, 'code' => 200);
	                Log::info('createcustomertransaction' . print_r($returnData, true));
	            } catch (\Exception $e) {
	                $returnData = array('status' => 'failure', 'message' => 'transaction not saved.', 'code' => 400);
	                Log::error('createcustomertransaction $validator->fails' . print_r($returnData, true));
	            }
			    echo $key.' --------- Ended';
//	            return response()->json($returnData, $returnData['code']);

		    }

	    }

//        $Data = [
//        	'hose_id' => 13,
//        	'pump_id' => 11,
//        	'odo_meter' => 33049,
//	        'attendant_id' => 23,
//	        'vehicle_id' => 86,
//	        'job_id' => -1,
//	        'litres' => 440.00,
//        	'start_date' => '2018-04-20 04:47:04',
//        	'end_date' => '2018-04-20 05:04:57'
//        ];

    }
}
