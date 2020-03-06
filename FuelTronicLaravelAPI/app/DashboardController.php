<?php

namespace App\Http\Controllers;

use App\Interfaces\SiteRepositoryInterface;
use App\Models\Customers;
use App\Models\DashboardAtg;
use App\Models\DashboardTankLevel;
use App\Models\DashboardVehicleEco;
use App\Models\DashboardVehicleFueling;
use App\Models\Pumps;
use App\Models\Sites;
use App\Models\Tanks;
use App\Models\Vehicles;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;
use App\User;
use Carbon\Carbon;
use DB;
class DashboardController extends JwtAuthController
{
	public function __construct(Request $request, SiteRepositoryInterface $siteRepo)
	{
		parent::__construct();
		$this->site = $siteRepo;
		$this->request = $request;
	}

	public function index($id='')
	{
		try {
				date_default_timezone_set('Africa/Johannesburg');
			$dash_cur=date("Y-m-d", time());
	$dash_cur_date=(string)$dash_cur;
		//$dash_cur_date="2019-08-30";
		$query = auth()->user()->sites()->with([
				'tanks' => function ($q1){
					$q1->with([
						'grades'         => function ($q2) {
							$q2->select('id', 'name');
						},
						'tankHoses.pump' => function ($q1) {
//							$q1->take(2);
						}
					]);
				}
			]);
			if($id){
				$site = $query->find($id);
			}else{
				$site = $query->first();
			}
			/*old*/
//			$data['pumps'] = Pumps::select('id', 'name', 'updated_at')->where('site_id', $site->id)->get();
//			foreach ($data['pumps'] as $pump){
//				$customer_transactions = $pump->customer_transaction()
//					->with(['attendant'=>function($q1){
//						$q1->select('id','name');
//					}])
//					->orderBy('end_date', 'desc')->get();
//				$temp = [];
//				$i= 0;
//				foreach ($customer_transactions as $key=>$customer_transaction){
//					$endDate = Carbon::parse($customer_transaction->end_date);
//					if($endDate->lt(Carbon::now()) && Carbon::now()->diffInHours($endDate) <= 24) {
//						$temp[$key]['id'] = $customer_transaction->id;
//						$temp[$key]['litres'] = $customer_transaction->litres;
//						$temp[$key]['attendant_name'] = ($customer_transaction->attendant) ? $customer_transaction->attendant->name : "";
//						$temp[$key]['end_date'] = date('Y-m-d H:i:s', strtotime($customer_transaction->end_date));
//						$i++;
//					}
//					if($i == 3){
//						break;
//					}
//				}
//				$pump->customer_transactions = $temp;
//				$pump->date_diff = Carbon::now()->diffInHours(Carbon::parse($pump->updated_at));
//			}
			$Data = auth()->user()->sites()->select('id', 'name')->with('tanks')->get();
			$finalData = [];
			$finalChartData = [];
			$xaxis = [];
			$dash_cur=date("Y-m-d", time());
			$dash_cur_date=(string)$dash_cur;
$mytime = \Carbon\Carbon::now();
 $newdatetime = $mytime->toDateTimeString();
 list($newdate, $newtime) = explode(' ', $newdatetime);
 // echo $newdate;die;
 $test_date=(string)$newdate;
			// var_dump($dash_cur_date);die;
				foreach ($site->tanks as $key=>$tank){
					// $dash_cur_date;die;
					$finalChartData[$key] = [];
					$tempCD = [];
					$tempCD['graph']['name'] = $tank->name;
					$tempCD['graph']['tank_id'] = $tank->id;
			//		$daily_fuel = \DB::select('call TankRecon('.$tank->id.', "'.$test_date.'")');
					// $daily_fuel = \DB::select("call TankRecon('.$tank->id.','2019-09-02')");
			//		$daily_fuel_json=json_encode($daily_fuel,true);
			///		$daily_fuel_decode=json_decode($daily_fuel_json,true);
			//		$daid_fuel_count=count($daily_fuel_decode)-1;
					$tempCD['graph']['total'] = number_format($tank->volume,2);
					$tempCD['graph']['grade_name'] = ($tank->grades) ? $tank->grades->name : '';
					if($tank->atg == 'On'){
						$value = !empty($tank->cur_atg_level) ? $tank->cur_atg_level : 0;
					}else{
						$value = !empty($tank->litre) ? $tank->litre : 0;
					}
					/* this code use for get last updated cron for dashboard  start*/
				
					$dashborad_tank_level_updated = DashboardTankLevel::select('TransactionDate')->where('site_id', $site->id)->where('TankId', $tank->id)->orderBy('TransactionDate','DESC')->take(1)->get();
					$daily_cron_update_json=json_encode($dashborad_tank_level_updated,true);
					$daily_cron_update_decode=json_decode($daily_cron_update_json,true);
				
					if(isset($daily_cron_update_decode[0]['TransactionDate'])){
					$tempCD['graph']['TransactionLastUpdate'] = $daily_cron_update_decode[0]['TransactionDate'];
					}
					else{
						$tempCD['graph']['TransactionLastUpdate'] = '';
					}
					print_r($daily_cron_update_decode);die;
				/* this code use for get last updated cron for dashboard end */
				//	$tempCD['graph']['RunningTotal'] = number_format($daily_fuel_decode[$daid_fuel_count]['RunningTotal'],2);
					$tempCD['graph']['current'] = number_format($value,2);
					$tempCD['graph']['percentage'] = number_format((float)(($value*100)/$tank->volume), 2, '.','');
					$tempCD['graph']['rotate'] = (360 * (1 - ($tempCD['graph']['percentage'] / 100))) / 2;
	
					/*get pumps of tanks using hose - new*/
					foreach ($tank->tankHoses as $hoseKey=>$hose){
						$pump = $hose->pump;
						$customer_transactions = $pump->customer_transaction()
							->with(['attendant'=>function($q1){
								$q1->select('id','name');
							}])
							->orderBy('end_date', 'desc')->get();
						$temp = [];
						$i= 0;
						foreach ($customer_transactions as $key2=>$customer_transaction){
							$endDate = Carbon::parse($customer_transaction->end_date);
							if($endDate->lt(Carbon::now()) && Carbon::now()->diffInHours($endDate) <= 24) {
								$temp[$key2]['id'] = $customer_transaction->id;
								$temp[$key2]['litres'] = $customer_transaction->litres;
								$temp[$key2]['attendant_name'] = ($customer_transaction->attendant) ? $customer_transaction->attendant->name : "";
								$temp[$key2]['end_date'] = date('Y-m-d H:i:s', strtotime($customer_transaction->end_date));
								$i++;
							}
							if($i == 3){
								break;
							}
						}
						$tempCD['pumps'][$hoseKey]['name'] = $pump->name;
						$tempCD['pumps'][$hoseKey]['customer_transactions'] = $temp;
						$tempCD['pumps'][$hoseKey]['date_diff'] = Carbon::now()->diffInHours(Carbon::parse($pump->updated_at));

//						$pump->customer_transactions = $temp;
//						$pump->date_diff = Carbon::now()->diffInHours(Carbon::parse($pump->updated_at));
					}
					
					$finalChartData[$key] = $tempCD;
//					dd($tank->tankHoses);

					/* Old for bar chart */
//					if($tank->atg == 'On'){
//						$value = !empty($tank->cur_atg_level) ? $tank->cur_atg_level : 0;
//					}else{
//						$value = !empty($tank->litre) ? $tank->litre : 0;
//					}
//					$finalData[]=[($key+1),(float)$value];
//					$xaxis[]= [($key+1),$tank->name];
				}
			/*	$SD=json_encode($finalChartData,true);
				$Sss=json_decode($SD,true);
				$c_count= count($Sss[4]['graph']['daily_fuel'])-1;
				
echo '<pre>';				print_r($Sss[4]['graph']['daily_fuel'][$c_count]['RunningTotal']); echo  '</pre>';*/
			$graphData = [];
			$lineChart = [];
			$response = [];

			/* Graph */
//			$start_date_time = date('Y-m-d H:i:s', strtotime('-3 months'));
//			$end_date_time = date('Y-m-d H:i:s');
////			dd($start_date_time, $end_date_time);
//
//			/* bar chart */
//			$whereCondition  = 'atg_transactions.atg_id = atg_data.id and atg_data.id = tanks.atg_id ';
//
//			$whereCondition  .= ' and concat(atg_transactions.date," ",atg_transactions.time) between "' . $start_date_time.'" AND "' .$end_date_time.'"';
//
//			$atgData = \DB::select('Select atg_data.name, atg_transactions.date,tanks.name AS tank_name,
//					concat(TIME_FORMAT(atg_transactions.time, \'%H\') ,\':00\') time, round(avg(atg_transactions.cm),2) cm,
//					round(avg(atg_transactions.liters),2) liters
//
//					from atg_transactions,tanks,atg_data
//					where '.$whereCondition.' and tanks.site_id = '.$site->id.' and atg_transactions.liters > 0
//					group by atg_data.name, atg_transactions.date,hour( time )');
//
//			$dateGroup = collect($atgData)->groupBy('date');
//
//
//			/* line chart */
//
//			$tankReconData = \DB::select("call TankReconDates('4', '2018-01-01', '2018-03-31')");
//			foreach ($tankReconData as $tankRecon){
//				$tankRecon->date = date('Y-m-d', strtotime($tankRecon->TransactionDate));
//			}
//			$tankReconDataGroupByTank = collect($tankReconData)->groupBy('TankName');
//			$tankReconDataGroupByDate = [];
//			foreach ($tankReconDataGroupByTank as $key=>$value){
//				$tankReconDataGroupByDate[$key] = [];
//				foreach ($value as $Tkey=>$Tvalue){
//					$tankReconDataGroupByDate[$key][$Tvalue->date][] = $Tvalue;
//				}
//			}
//
//
//
////			$tanks = Tanks::where('site_id', $site->id)->get();
////			$response['tank_count'] = count($tanks);
////			foreach ($tanks as $key=>$tank){
////				$tankReconData = \DB::select('call TankRecon("'.$tank->id.'", "2018-04-10")');
////
////				foreach ($tankReconData as $tankRecon){
////					$tankRecon->date = date('Y-m-d', strtotime($tankRecon->TransactionDate));
////				}
////
////				$tankReconDataGroupByDate = collect($tankReconData)->groupBy('date');
////				$tankReconDataGroupDateArr[$key] = $tankReconDataGroupByDate->toArray();
////			}
//
//			$graphData = [];
//			$max = 0;$i=0;
//			foreach ($dateGroup as $key=>$value){       // merge bar and line chart
//				if(count($value) > $max)
//					$max = count($value);
//
//				$graphData[$i]['date'] = $key;
//				$graphData[$i]['litres'] = $value->pluck('liters')->toArray();
//				$j=0;
//				foreach ($tankReconDataGroupByDate as $transKey=>$trans){
//					if(isset($trans[$key])) {
//						$graphData[$i]['line_data'][$j] = collect($trans[$key])->pluck('TotalLiters')->toArray();
//						$j++;
//					}
//				}
////				foreach ($value as $transactionKey=>$transaction){
////					$graphData[$i]['date'][$transactionKey] = $transaction->liters;
////				}
//				$i++;
//			}
////dd($graphData);
//			foreach ($tankReconDataGroupByDate as $key=>$value){
//				if(count($value) > $max)
//					$max = count($value);
//
//				$lineChart[$i]['date'] = $key;
//				$lineChart[$i]['litres'] = $value->pluck('Diffrance')->toArray();
////				foreach ($value as $transactionKey=>$transaction){
////					$graphData[$i]['date'][$transactionKey] = $transaction->liters;
////				}
//				$i++;
//			}
//			dd($lineChart);
			$data['tanks'] = Tanks::select('id', 'name')->where('site_id', $site->id)->get();
			$data['customers'] = Customers::select('id', 'name')->whereHas('sites', function ($q1) use($site){
				$q1->where('id', $site->id);
			})->get();
			$returnData = array ( 'status' => 'success', 'data'=> ['sites'=>$Data,'xaxis'=>$xaxis, 'chartData' =>
				$finalChartData, 'graphData' => $graphData, 'lineChart' => $lineChart,
			     'resData'=>$data , 'response' => $response], 'code' => 200);
//			$returnData = array ( 'status' => 'success', 'data'=> ['sites'=>$Data,'xaxis'=>$xaxis, 'tankData' => $finalData], 'code' => 200);
		}
		catch (\Exception $e) {dd($e->getMessage());
			$returnData = array ( 'status' => 'failure', 'message' => 'Transactions not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	public function index2($id='')
	{
		try {
			if($id){
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->find($id);
			}else{
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->first();
			}
			$Data = auth()->user()->sites()->select('id', 'name')->with('tanks')->get();

			$data['tanks'] = Tanks::select('id', 'name')->where('site_id', $site->id)->get();
			$data['vehicles'] = Vehicles::select('id', 'name')->whereHas('sites', function ($q1) use($site){
				$q1->where('id', $site->id);
			})->get();
			$data['customers'] = Customers::select('id', 'name')->whereHas('sites', function ($q1) use($site){
				$q1->where('id', $site->id);
			})->get();

			$returnData = ['status' => 'success', 'data'=> ['sites'=>$Data,'data' => $data], 'code' => 200];
		} catch (\Exception $e) {dd($e->getMessage());
			$returnData = array ( 'status' => 'failure', 'message' => 'Transactions not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	public function chart2(Request $request, $id='')
	{
		try {
			if($id){
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->find($id);
			}else{
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->first();
			}

			/* Graph */
			$start_date_time = date('Y-m-d H:i:s', strtotime('-3 months'));
			$end_date_time = date('Y-m-d H:i:s');
			if($request->has('days') && $request->days > 0){
				$start_date_time = date('Y-m-d H:i:s', strtotime('-7 days'));
				$end_date_time = date('Y-m-d H:i:s');
			}
			elseif($request->has('date_range')){
				$date = explode(' - ', $request->date_range);
				$start_date_time = date('Y-m-d H:i:s', strtotime($date[0].'00:00:00'));
				$end_date_time = date('Y-m-d H:i:s', strtotime($date[1].'23:59:59'));
			}

			$query = DashboardVehicleFueling::where('site_id', $site->id);
			if($request->has('vehicle_id')){
				$ids = explode(',', $request->vehicle_id);
				$query = $query->whereIn('vehicle_id', $ids);
			}
			if($request->has('customer_id')){
				$ids = explode(',', $request->customer_id);
				$query = $query->whereIn('customer_id', $ids);
			}
			$vehicleFuel = $query->whereBetween('TransactionDate', [$start_date_time, $end_date_time])->get();

			foreach ($vehicleFuel as $value){
				$value->date = date('Y-m-d', strtotime($value->TransactionDate));
			}
			$vehicleFuelByDate = $vehicleFuel->groupBy('date');

			$graphData2 = [];$vehicleIds = [];$vehicles = [];
			$i=0;
			foreach ($vehicleFuelByDate as $key=>$value){       // merge bar and line chart
				$graphData2[$i]['date'] = $key;
				foreach ($value as $dateKey=>$dateVal){
					if(!in_array($dateVal->vehicle_id, $vehicleIds)){
						$vehicleIds[] = $dateVal->vehicle_id;
						$vehicles[$dateVal->vehicle_id] = $dateVal->name;
					}
					$graphData2[$i]['atg'][$dateKey]['vehicle_id'] = $dateVal->vehicle_id;
					$graphData2[$i]['atg'][$dateKey]['vehicle'] = $dateVal->name;
					$graphData2[$i]['atg'][$dateKey]['litre'] = $dateVal->litres;
					$graphData2[$i]['atg'][$dateKey]['total_cost'] = $dateVal->total_cost;
					$graphData2[$i]['atg'][$dateKey]['odo_meter'] = $dateVal->odo_meter;
					$graphData2[$i]['atg'][$dateKey]['registration_number'] = $dateVal->registration_number;
					$graphData2[$i]['atg'][$dateKey]['date'] = $dateVal->TransactionDate;
				}
				$i++;
			}
			$data['vehicleIds'] = $vehicleIds;
			$data['vehicles'] = $vehicles;

			$returnData = ['status' => 'success', 'data'=> ['graphData2' => $graphData2,'data' => $data], 'code' => 200];
		} catch (\Exception $e) {dd($e->getMessage());
			$returnData = array ( 'status' => 'failure', 'message' => 'Transactions not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}

	public function chart3(Request $request, $id='')
	{
		try {
			if($id){
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->find($id);
			}else{
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->first();
			}

			/* Graph */
			$start_date_time = date('Y-m-d H:i:s', strtotime('-3 months'));
			$end_date_time = date('Y-m-d H:i:s');
			if($request->has('days') && $request->days > 0){
				$start_date_time = date('Y-m-d H:i:s', strtotime('-7 days'));
				$end_date_time = date('Y-m-d H:i:s');
			}
			elseif ($request->has('date_range')){
				$date = explode(' - ', $request->date_range);
				$start_date_time = date('Y-m-d H:i:s', strtotime($date[0].'00:00:00'));
				$end_date_time = date('Y-m-d H:i:s', strtotime($date[1].'23:59:59'));
			}

			$query = DashboardVehicleEco::with('vehicle');
			if($request->has('vehicle_id')){
				$ids = explode(',', $request->vehicle_id);
				$query = $query->whereIn('vehicle_id', $ids);
			}
			if($request->has('customer_id')){
				$ids = explode(',', $request->customer_id);
				$query = $query->whereIn('customer_id', $ids);
			}
			$vehicleEco = $query->where('site_id', $site->id)
			->whereBetween('start_date', [$start_date_time, $end_date_time])->get();
//			/* line chart */
			foreach ($vehicleEco as $value){
				$value->date = date('Y-m-d', strtotime($value->start_date));
			}

			$tankReconDataGroupByDate = collect($vehicleEco)->groupBy('date');
//			$tankReconDataGroupByDate = [];
//			foreach ($tankReconDataGroupByTank as $key=>$value){
//				$tankReconDataGroupByDate[$key] = [];
//				foreach ($value as $Tkey=>$Tvalue){
//					$tankReconDataGroupByDate[$key][$Tvalue->date][] = $Tvalue;
//				}
//			}

			$graphData2 = [];$vehicleIds = [];$vehicles = [];
			$i=0;
			foreach ($tankReconDataGroupByDate as $transKey=>$trans){
				$graphData2[$i]['date'] = $transKey;
				foreach ($trans as $dateKey=>$dateVal){
					if(!in_array($dateVal->vehicle_id, $vehicleIds)){
						$vehicleIds[] = $dateVal->vehicle_id;
						$vehicles[$dateVal->vehicle_id] = ($dateVal->vehicle) ? $dateVal->vehicle->name : '';
					}
					$graphData2[$i]['line'][$dateKey]['vehicle_id'] = $dateVal->vehicle_id;
					$graphData2[$i]['line'][$dateKey]['vehicle'] = ($dateVal->vehicle) ?
						$dateVal->vehicle->name : '';
					$graphData2[$i]['line'][$dateKey]['litre'] = $dateVal->total_liter;
					$graphData2[$i]['line'][$dateKey]['CostPer100Km'] = $dateVal->CostPer100Km;
					$graphData2[$i]['line'][$dateKey]['date'] = $dateVal->start_date;
					$graphData2[$i]['line'][$dateKey]['kmL'] = $dateVal->KmL;
					$graphData2[$i]['line'][$dateKey]['current_cost'] = $dateVal->current_cost;
					$graphData2[$i]['line'][$dateKey]['current_litre'] = $dateVal->current_litre;
					$graphData2[$i]['line'][$dateKey]['KmTraveled'] = $dateVal->KmTraveled;
				}
				$i++;
			}

			$data['vehicleIds'] = $vehicleIds;
			$data['vehicles'] = $vehicles;

			$returnData = ['status' => 'success', 'data'=> ['graphData2' => $graphData2,'data' => $data], 'code' => 200];
		} catch (\Exception $e) {dd($e->getMessage(), $e->getLine());
			$returnData = array ( 'status' => 'failure', 'message' => 'Transactions not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	public function chart1(Request $request, $id='')
	{
		try {
			if($id){
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->find($id);
			}else{
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->first();
			}

			/* Graph */
			$start_date_time = date('Y-m-d H:i:s', strtotime('-3 months'));
			$end_date_time = date('Y-m-d H:i:s');
			if($request->has('days') && $request->days > 0){
				$start_date_time = date('Y-m-d H:i:s', strtotime('-7 days'));
				$end_date_time = date('Y-m-d H:i:s');
			}
			elseif($request->has('date_range')){
				$date = explode(' - ', $request->date_range);
				$start_date_time = date('Y-m-d H:i:s', strtotime($date[0]));
				$end_date_time = date('Y-m-d H:i:s', strtotime($date[1]));
			}
//			if($request->has('end_date')){
//				$end_date_time = date('Y-m-d H:i:s', strtotime($request->end_date));
//			}
			$query = DashboardTankLevel::where('site_id', $site->id);
			if($request->has('tank_id')){
				$query = $query->where('TankId', $request->tank_id);
			}
			$tankLevel = $query->whereBetween('TransactionDate', [$start_date_time, $end_date_time])->get();

			$query = DashboardAtg::with(['tank'=>function($q1){
							$q1->select('id', 'name');
						}]);
			if($request->has('tank_id')){
				$query = $query->where('tank_id', $request->tank_id);
			}
			$dateGroup = $query->where('site_id', $site->id)
			->whereBetween('TransactionDate', [$start_date_time, $end_date_time])->get()->groupBy('date');

//			/* line chart */
			foreach ($tankLevel as $tankRecon){
				$tankRecon->date = date('Y-m-d', strtotime($tankRecon->TransactionDate));
			}

			$tankReconDataGroupByTank = collect($tankLevel)->groupBy('TankName');
			$tankReconDataGroupByDate = [];
			foreach ($tankReconDataGroupByTank as $key=>$value){
				$tankReconDataGroupByDate[$key] = [];
				foreach ($value as $Tkey=>$Tvalue){
					$tankReconDataGroupByDate[$key][$Tvalue->date][] = $Tvalue;
				}
			}

			$graphData = [];
			$max = 0;$i=0;
			foreach ($dateGroup as $key=>$value){       // merge bar and line chart
				if(count($value) > $max)
					$max = count($value);

				$graphData[$i]['date'] = $key;
				foreach ($value as $dateKey=>$dateVal){
					$graphData[$i]['atg'][$dateKey]['tank_name'] = ($dateVal->tank) ? $dateVal->tank->name : '';
					$graphData[$i]['atg'][$dateKey]['litre'] = $dateVal->liters;
					$graphData[$i]['atg'][$dateKey]['date'] = $dateVal->TransactionDate;
				}

				$j=0;
				foreach ($tankReconDataGroupByDate as $transKey=>$trans){
					if(isset($trans[$key])) {
						foreach ($trans[$key] as $dateKey=>$dateVal){
							$graphData[$i]['line'][$j][$dateKey]['tank_name'] = $dateVal->TankName;
							$graphData[$i]['line'][$j][$dateKey]['supplier_name'] = $dateVal->SupplierCustomer;
							$graphData[$i]['line'][$j][$dateKey]['type'] = $dateVal->Type;
							$graphData[$i]['line'][$j][$dateKey]['litre'] = $dateVal->TotalLiters;
							$graphData[$i]['line'][$j][$dateKey]['running_total'] = $dateVal->RunningTotal;
							$graphData[$i]['line'][$j][$dateKey]['date'] = $dateVal->TransactionDate;
						}
						$j++;
					}
				}
				$i++;
			}

			$returnData = ['status' => 'success', 'data'=> ['graphData' => $graphData], 'code' => 200];
		} catch (\Exception $e) {dd($e->getMessage());
			$returnData = array ( 'status' => 'failure', 'message' => 'Transactions not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	public function chart1new(Request $request, $id='')
	{
		try {
			if($id){
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->find($id);
			}else{
				$site = auth()->user()->sites()->with(['tanks.grades'=>function($q1){
					$q1->select('id','name');
				}])->first();
			}

			/* Graph */
			$start_date_time = date('Y-m-d H:i:s', strtotime('-3 months'));
			$end_date_time = date('Y-m-d H:i:s');
			if($request->has('days') && $request->days > 0){
				$start_date_time = date('Y-m-d H:i:s', strtotime('-7 days'));
				$end_date_time = date('Y-m-d H:i:s');
			}
			elseif($request->has('date_range')){
				$date = explode(' - ', $request->date_range);
				$start_date_time = date('Y-m-d H:i:s', strtotime($date[0]));
				$end_date_time = date('Y-m-d H:i:s', strtotime($date[1]));
			}
//			if($request->has('end_date')){
//				$end_date_time = date('Y-m-d H:i:s', strtotime($request->end_date));
//			}
			$query = DashboardTankLevel::where('site_id', $site->id);
			if($request->has('tank_id')){
				$query = $query->where('TankId', $request->tank_id);
			}
			$tankLevel = $query->whereBetween('TransactionDate', [$start_date_time, $end_date_time])->get();

			$query = DashboardAtg::with(['tank'=>function($q1){
							$q1->select('id', 'name');
						}]);
			if($request->has('tank_id')){
				$query = $query->where('tank_id', $request->tank_id);
			}
			$dateGroup = $query->where('site_id', $site->id)
			->whereBetween('TransactionDate', [$start_date_time, $end_date_time])->get()->groupBy('date');

//			/* line chart */
			foreach ($tankLevel as $tankRecon){
				$tankRecon->date = date('Y-m-d', strtotime($tankRecon->TransactionDate));
			}

			$tankReconDataGroupByTank = collect($tankLevel)->groupBy('TankName');
			$tankReconDataGroupByDate = [];
			foreach ($tankReconDataGroupByTank as $key=>$value){
				$tankReconDataGroupByDate[$key] = [];
				foreach ($value as $Tkey=>$Tvalue){
					$tankReconDataGroupByDate[$key][$Tvalue->date][] = $Tvalue;
				}
			}

			$graphData = [];
			$max = 0;$i=0;
			foreach ($dateGroup as $key=>$value){       // merge bar and line chart
				if(count($value) > $max)
					$max = count($value);

				$graphData[$i]['date'] = $key;
				foreach ($value as $dateKey=>$dateVal){
					$graphData[$i]['atg'][$dateKey]['tank_name'] = ($dateVal->tank) ? $dateVal->tank->name : '';
					$graphData[$i]['atg'][$dateKey]['litre'] = $dateVal->liters;
					$graphData[$i]['atg'][$dateKey]['date'] = $dateVal->TransactionDate;
				}

//				$j=0;
//				foreach ($tankReconDataGroupByDate as $transKey=>$trans){
//					if(isset($trans[$key])) {
//						foreach ($trans[$key] as $dateKey=>$dateVal){
//							$graphData[$i]['line'][$j][$dateKey]['tank_name'] = $dateVal->TankName;
//							$graphData[$i]['line'][$j][$dateKey]['supplier_name'] = $dateVal->SupplierCustomer;
//							$graphData[$i]['line'][$j][$dateKey]['type'] = $dateVal->Type;
//							$graphData[$i]['line'][$j][$dateKey]['litre'] = $dateVal->TotalLiters;
//							$graphData[$i]['line'][$j][$dateKey]['running_total'] = $dateVal->RunningTotal;
//							$graphData[$i]['line'][$j][$dateKey]['date'] = $dateVal->TransactionDate;
//						}
//						$j++;
//					}
//				}
				$i++;
			}
			$j=0;
			foreach ($tankReconDataGroupByDate as $transKey2=>$trans2){
				foreach ($trans2 as $transKey=>$trans){
					$status = false;
					foreach ($graphData as $key=>$datum){
						if($datum['date'] == $transKey){
							$status = true;
							foreach ($trans as $dateKey => $dateVal) {
								$graphData[$key]['line'][$j][$dateKey]['tank_name'] = $dateVal->TankName;
								$graphData[$key]['line'][$j][$dateKey]['supplier_name'] = $dateVal->SupplierCustomer;
								$graphData[$key]['line'][$j][$dateKey]['type'] = $dateVal->Type;
								$graphData[$key]['line'][$j][$dateKey]['litre'] = $dateVal->TotalLiters;
								$graphData[$key]['line'][$j][$dateKey]['running_total'] = $dateVal->RunningTotal;
								$graphData[$key]['line'][$j][$dateKey]['date'] = $dateVal->TransactionDate;
							}
						}
					}
					if(!$status){
						$graphData[$i]['date'] = $transKey;
						foreach ($trans as $dateKey => $dateVal) {
							$graphData[$i]['line'][$j][$dateKey]['tank_name'] = $dateVal->TankName;
							$graphData[$i]['line'][$j][$dateKey]['supplier_name'] = $dateVal->SupplierCustomer;
							$graphData[$i]['line'][$j][$dateKey]['type'] = $dateVal->Type;
							$graphData[$i]['line'][$j][$dateKey]['litre'] = $dateVal->TotalLiters;
							$graphData[$i]['line'][$j][$dateKey]['running_total'] = $dateVal->RunningTotal;
							$graphData[$i]['line'][$j][$dateKey]['date'] = $dateVal->TransactionDate;
						}
						$i++;

					}
				}
				$j++;
			}
			$returnData = ['status' => 'success', 'data'=> ['graphData' => $graphData], 'code' => 200];
		} catch (\Exception $e) {dd($e->getMessage(), $e->getLine());
			$returnData = array ( 'status' => 'failure', 'message' => 'Transactions not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}

	public function vehicles(Request $request)
	{
		$query = Vehicles::select('id','name','customer_id');
		if($request->has('customer_ids')){
			$ids = explode(',', $request->customer_ids);
			$query = $query->whereIn('customer_id', $ids);
		}
		$vehicles = $query->get();
		return response()->json(['data'=>$vehicles], 200);
	}
	/*chart2 and chart3 combined*/
	public function chart2Old(Request $request, $id = '')
	{
		try {
			if ($id) {
				$site = auth()->user()->sites()->with([
					'tanks.grades' => function ($q1) {
						$q1->select('id', 'name');
					}
				])->find($id);
			}
			else {
				$site = auth()->user()->sites()->with([
					'tanks.grades' => function ($q1) {
						$q1->select('id', 'name');
					}
				])->first();
			}
			/* Graph */
			$start_date_time = date('Y-m-d H:i:s', strtotime('-3 months'));
			$end_date_time = date('Y-m-d H:i:s');
			if ($request->has('date_range')) {
				$date = explode(' - ', $request->date_range);
				$start_date_time = date('Y-m-d H:i:s', strtotime($date[0] . '00:00:00'));
				$end_date_time = date('Y-m-d H:i:s', strtotime($date[1] . '23:59:59'));
			}
			$query = DashboardVehicleEco::with('vehicle');
			if ($request->has('vehicle_id')) {
				$query = $query->where('vehicle_id', $request->vehicle_id);
			}
			if ($request->has('customer_id')) {
				$query = $query->where('customer_id', $request->customer_id);
			}
			$vehicleEco = $query->where('site_id', $site->id)->whereBetween('start_date', [
					$start_date_time,
					$end_date_time
				])->get();
			$query = DashboardVehicleFueling::where('site_id', $site->id);
			if ($request->has('vehicle_id')) {
				$query = $query->where('vehicle_id', $request->vehicle_id);
			}
			if ($request->has('customer_id')) {
				$query = $query->where('customer_id', $request->customer_id);
			}
			$vehicleFuel = $query->whereBetween('TransactionDate', [
				$start_date_time,
				$end_date_time
			])->get();
			foreach ($vehicleFuel as $value) {
				$value->date = date('Y-m-d', strtotime($value->TransactionDate));
			}
			$vehicleFuelByDate = $vehicleFuel->groupBy('date');
			//			/* line chart */
			foreach ($vehicleEco as $value) {
				$value->date = date('Y-m-d', strtotime($value->start_date));
			}
			$tankReconDataGroupByTank = collect($vehicleEco)->groupBy('vehicle_id');
			$tankReconDataGroupByDate = [];
			foreach ($tankReconDataGroupByTank as $key => $value) {
				$tankReconDataGroupByDate[$key] = [];
				foreach ($value as $Tkey => $Tvalue) {
					$tankReconDataGroupByDate[$key][$Tvalue->date][] = $Tvalue;
				}
			}
			$graphData2 = [];
			$vehicleIds = [];
			$i = 0;
			foreach ($vehicleFuelByDate as $key => $value) {       // merge bar and line chart
				$graphData2[$i]['date'] = $key;
				foreach ($value as $dateKey => $dateVal) {
					if (!in_array($dateVal->vehicle_id, $vehicleIds)) {
						$vehicleIds[] = $dateVal->vehicle_id;
					}
					$graphData2[$i]['atg'][$dateKey]['vehicle'] = $dateVal->name;
					$graphData2[$i]['atg'][$dateKey]['litre'] = $dateVal->litres;
					$graphData2[$i]['atg'][$dateKey]['total_cost'] = $dateVal->total_cost;
					$graphData2[$i]['atg'][$dateKey]['odo_meter'] = $dateVal->odo_meter;
					$graphData2[$i]['atg'][$dateKey]['registration_number'] = $dateVal->registration_number;
					$graphData2[$i]['atg'][$dateKey]['date'] = $dateVal->TransactionDate;
				}
				$j = 0;
				foreach ($tankReconDataGroupByDate as $transKey => $trans) {
					if (isset($trans[$key])) {
						foreach ($trans[$key] as $dateKey => $dateVal) {
							$graphData2[$i]['line'][$j][$dateKey]['vehicle_id'] = $dateVal->vehicle_id;
							$graphData2[$i]['line'][$j][$dateKey]['vehicle'] = ($dateVal->vehicle) ? $dateVal->vehicle->name : '';
							$graphData2[$i]['line'][$j][$dateKey]['litre'] = $dateVal->total_liter;
							$graphData2[$i]['line'][$j][$dateKey]['CostPer100Km'] = $dateVal->CostPer100Km;
							$graphData2[$i]['line'][$j][$dateKey]['date'] = $dateVal->start_date;
							$graphData2[$i]['line'][$j][$dateKey]['kmL'] = $dateVal->KmL;
							$graphData2[$i]['line'][$j][$dateKey]['current_cost'] = $dateVal->current_cost;
							$graphData2[$i]['line'][$j][$dateKey]['current_litre'] = $dateVal->current_litre;
							$graphData2[$i]['line'][$j][$dateKey]['KmTraveled'] = $dateVal->KmTraveled;
						}
						$j++;
					}
				}
				$i++;
			}
			$data['vehicleIds'] = $vehicleIds;
			//dd($graphData2);
			$returnData = [
				'status' => 'success',
				'data'   => [
					'graphData2' => $graphData2,
					'data'       => $data
				],
				'code'   => 200
			];
		}
		catch (\Exception $e) {
			dd($e->getMessage());
			$returnData = array (
				'status' => 'failure',
				'message' => 'Transactions not found',
				'code' => 400
			);
		}
		return response()->json($returnData, $returnData['code']);
	}
}