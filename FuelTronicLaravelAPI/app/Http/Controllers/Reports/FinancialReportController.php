<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\JwtAuthController;
use App\Models\CustomerTransaction;
use App\Models\FuelDrop;
use App\Models\Hoses;
use App\Models\Sites;
use App\Models\Tanks;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Datatables;

class FinancialReportController extends JwtAuthController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
    }

	public function tankpumpsfromhose()
	{
		$Data = $this->request->all();

		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric',
			'hoses' => 'required'
		]);
		if ($validator->fails()) {
			$returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
			return response()->json($returnData, 400);
		}
		try {

			$hoseIds = [];
			if ($Data['hoses']) {
				$hoseIds = explode(',', $Data['hoses']);
			}

			$Data = auth()->user()->sites()->with(['hoses' => function ($query) use ($hoseIds) {
				if (count($hoseIds)) {
					$query = $query->whereIn('id', $hoseIds);
				}
				$query = $query->with(['pump' => function ($quer) {
					$quer = $quer->selectRaw('id, name');
				}, 'tank' => function ($quer) {
					$quer = $quer->selectRaw('id, name');
				}
				]);
			}])->find($Data['site_id']);

			$pumpArray = [];
			$tankArray = [];

			foreach ($Data->hoses as $hose) {
				$pumpArray[] = $hose->pump;
				$tankArray[] = $hose->tank;
			}

			$finalArray = ['pumps' => array_unique($pumpArray), 'tanks' => array_unique($tankArray)];

			$returnData = array('status' => 'success', 'data' => $finalArray, 'code' => 200);
		} catch (\Exception $e) {
			$returnData = array('status' => 'failure', 'message' => 'pumps not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	public function tanksfromsite()
	{
		$Data = $this->request->all();

		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric',
		]);
		if ($validator->fails()) {
			$returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
			return response()->json($returnData, 400);
		}
		try {
			$Data = auth()->user()->sites()->with(['tanks' => function ($query) {
				$query->select('id', 'name', 'site_id');
			}, 'locations'])->find($Data['site_id']);

			$returnData = array('status' => 'success', 'data' => $Data->tanks,'locations' => $Data->locations, 'code' => 200);
		} catch (\Exception $e) {
			$returnData = array('status' => 'failure', 'message' => 'Tanks not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}

	public function gradesfromtanks()
	{
		$Data = $this->request->all();

		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric',
			'tanks' => 'required'
		]);
		if ($validator->fails()) {
			$returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
			return response()->json($returnData, 400);
		}
		try {

			$tankIds = [];
			if ($Data['tanks']) {
				$tankIds = explode(',', $Data['tanks']);
			}

			$Data = auth()->user()->sites()->with(['tanks' => function ($query) use ($tankIds) {
				if (count($tankIds)) {
					$query = $query->whereIn('id', $tankIds);
				}
				$query = $query->with(['grades' => function ($quer) {
					$quer = $quer->selectRaw('id, name');
				}]);
			}])->find($Data['site_id']);

			$gradeArray = [];

			foreach ($Data->tanks as $tank) {
				$gradeArray[] = $tank->grades;
			}

			$finalArray = ['grades' => array_unique($gradeArray)];

			$returnData = array('status' => 'success', 'data' => $finalArray, 'code' => 200);
		} catch (\Exception $e) {
			$returnData = array('status' => 'failure', 'message' => 'Grades not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	
    public function sitecustomersfinancial()
    {
        $Data = $this->request->all();

        $validator = \Validator::make($Data, [
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $Data = auth()->user()->sites()->with('customers')->find($Data['site_id']);
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'customers not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function custAgeAnalysis()
    {
        $Data = $this->request->all();

        $validator = \Validator::make($Data, [
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
//            $date_range = explode(' - ', $Data['date_range']);
            $customerIds = [];
            if ($Data['customers']) {
                $customerIds = explode(',', $Data['customers']);
            }
            $sites = auth()->user()->sites()->with(['customers' => function ($query) use($customerIds, $Data) {
	            if (count($customerIds)) {
		            $query = $query->whereIn('id', $customerIds);
	            }
	            $query = $query->with([
		            'transactions' => function ($quer) use ($Data) {
			            $quer = $quer->selectRaw("customer_id, SUM(total_cost) as total_cost,created_at,end_date");
			            if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//                        $date_range[0] = $date_range[0] . " 00:00:00";
				            $quer = $quer->where('end_date', '>=', $Data['start_date'])->where('end_date', '<=', $Data['end_date']);
                        }
                }]);
	            $query = $query->with([
		            'payments' => function ($quer) use ($Data) {
			            $quer = $quer->selectRaw("customer_id, SUM(amount) as amount,created_at");
			            if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//                        $date_range[0] = $date_range[0] . " 00:00:00";
				            $quer = $quer->where('created_at', '>=', $Data['start_date'])->where('created_at', '<=', $Data['end_date']);
                        }
                }]);
            }])->find($Data['site_id']);
            $customers = $sites->customers;
            $returnData = array('status' => 'success', 'data' => $customers,'message' => 'Requested customers listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Requested customers listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

	public function sitesuppliersfinancial()
	{
		$Data = $this->request->all();
		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric'
		]);
		if ($validator->fails()) {
			$returnData = array (
				'status'            => 'failure',
				'validation_errors' => $validator->errors(),
				'code'              => 400
			);
			return response()->json($returnData, 400);
		}
		try {
			$Data = auth()->user()->sites()->with('suppliers')->find($Data['site_id']);
			$returnData = array (
				'status' => 'success',
				'data'   => $Data,
				'code'   => 200
			);
		}
		catch (\Exception $e) {
			$returnData = array (
				'status'  => 'failure',
				'message' => 'suppliers not found',
				'code'    => 400
			);
		}
		return response()->json($returnData, $returnData['code']);
	}

	public function supplierAgeAnalysis()
	{
		$Data = $this->request->all();
		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric'
		]);
		if ($validator->fails()) {
			$returnData = array (
				'status'            => 'failure',
				'validation_errors' => $validator->errors(),
				'code'              => 400
			);
			return response()->json($returnData, 400);
		}
		try {
//			$date_range = explode(' - ', $Data['date_range']);
			$supplierIds = [];
			if ($Data['suppliers']) {
				$supplierIds = explode(',', $Data['suppliers']);
			}
			$sites = auth()->user()->sites()->with([
				'suppliers' => function ($query) use ($supplierIds, $Data) {
					if (count($supplierIds)) {
						$query = $query->whereIn('id', $supplierIds);
					}
					$query = $query->with([
						'payments' => function ($quer) use ($Data) {
							$quer = $quer->selectRaw("supplier_id, SUM(amount) as amount,created_at");
							if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//								$date_range[0] = $date_range[0] . " 00:00:00";
								$quer = $quer->where('created_at', '>=', $Data['start_date'])->where('created_at', '<=', $Data['end_date']);
							}
						}
					]);
				}
			])->find($Data['site_id']);
			$suppliers = $sites->suppliers;
			return Datatables::of($suppliers)->make(true);

//			$returnData = array ( 'status' => 'success', 'data'   => $suppliers, 'message' => 'Requested suppliers listed successfully!',
//			                      'code'   => 200);
		}
		catch (\Exception $e) {
			$returnData = array (
				'status'  => 'failure',
				'message' => 'Requested suppliers listed not found',
				'code'    => 400
			);
		}
		return response()->json($returnData, $returnData['code']);
	}

	/* Suppliers -> Supplier List
	 * Getting Required inputs
	 */
    public function gradesListReport()
    {
        $Data = $this->request->all();

        $validator = \Validator::make($Data, [
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $Data = auth()->user()->sites()->with('grades')->find($Data['site_id']);
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Grades not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

	public function sitesalesfinancial()
	{
		$Data = $this->request->all();
		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric'
		]);
		if ($validator->fails()) {
			$returnData = array ( 'status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
			return response()->json($returnData, 400);
		}
		try {
			$Data = auth()->user()->sites()->with('customers','pumps','attendants','hoses','vehicles','grades','tanks')
				->find($Data['site_id']);
			$returnData = array ( 'status' => 'success', 'data'   => $Data, 'code'   => 200);
		}
		catch (\Exception $e) {
			$returnData = array (
				'status'  => 'failure',
				'message' => 'attendants not found',
				'code'    => 400
			);
		}
		return response()->json($returnData, $returnData['code']);
	}

	public function sales()
	{
		$Data = $this->request->all();
		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric'
		]);
		if ($validator->fails()) {
			$returnData = array ( 'status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
			return response()->json($returnData, 400);
		}
		try {
//			$date_range = explode(' - ', $Data['date_range']);
			$vehicleIds = [];
			$pumpIds = [];
			$attendantIds = [];
			$customerIds = [];
			$gradeIds = [];
			$tankIds = [];
			$hoseIds = [];
			if ($Data['vehicles']) {
				$vehicleIds = explode(',', $Data['vehicles']);
			}
			if ($Data['pumps']) {
				$pumpIds = explode(',', $Data['pumps']);
			}
			if ($Data['attendants']) {
				$attendantIds = explode(',', $Data['attendants']);
			}
			if ($Data['customers']) {
				$customerIds = explode(',', $Data['customers']);
			}
			if ($Data['grades']) {
				$gradeIds = explode(',', $Data['grades']);
			}
			if ($Data['tanks']) {
				$tankIds = explode(',', $Data['tanks']);
			}
			if ($Data['hoses']) {
				$hoseIds = explode(',', $Data['hoses']);
			}
			$site = Sites::with([
				'customer_transaction' => function ($que) use ($hoseIds,$vehicleIds,$pumpIds, $attendantIds, $customerIds,$gradeIds, $tankIds, $Data) {
					if (count($customerIds)) {
						$que = $que->whereIn('customer_id', $customerIds);
					}
					if (count($hoseIds)) {
						$que = $que->whereIn('hose_id', $hoseIds);
					}
					if (count($attendantIds)) {
						$que = $que->whereIn('attendant_id', $attendantIds);
					}
					if (count($vehicleIds)) {
						$que = $que->whereIn('vehicle_id', $vehicleIds);
					}
					if (!empty($Data['start_date']) && !empty($Data['end_date'])) {
						$start_date = \Carbon\Carbon::parse($Data['start_date'])->format('Y-m-d H:i:s');
						$end_date = \Carbon\Carbon::parse($Data['end_date'])->format('Y-m-d H:i:s');
						$que->where('end_date', '>=', $start_date)->where('end_date', '<=', $end_date);
//						$que->whereBetween('created_at', [$start_date, $end_date]);
					}
					$que = $que->where('litres', '>' , 0)->with([
						'hose' => function ($query) use ($tankIds, $pumpIds, $gradeIds) {

								$query = $query->selectRaw('id,name,tank_id,pump_id')
									->whereHas('pump', function ($query) use ($pumpIds) {
										if (count($pumpIds)) {
											$query = $query->whereIn('id', $pumpIds);
										}
									})
									->with([
										'pump' => function ($query) use ($pumpIds) {
											if (count($pumpIds)) {
												$query = $query->selectRaw('id,name')->whereIn('id', $pumpIds);
											}
											else {
												$query = $query->selectRaw('id,name');
											}
										}
									])
									->whereHas('tank', function ($query) use ($tankIds, $gradeIds) {
										if (count($tankIds)) {
										$query = $query->whereIn('id', $tankIds);
										}
										$query = $query->whereHas('grades', function ($q) use ($gradeIds) {
											if (count($gradeIds)) {
												$q = $q->whereIn('id', $gradeIds);
											}
										});
									});

							$query = $query->orderBy('id');
						},
						'attendant'=>function($q1){
							$q1->select('id', 'name');
						},
						'customer'=>function($q1){
							$q1->select('id', 'name');
						}
					]);
				}
			])->find($Data['site_id']);
			$customers = $site->customer_transaction;
			return Datatables::of($customers)->make(true);

			$returnData = array ( 'status'=> 'success', 'message' => 'Requested transaction listed successfully!', 'data' => $customers, 'code' => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status'  => 'failure', 'message' => 'Requested transaction listed not found', 'code'    => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	public function voids()
	{
		$Data = $this->request->all();
		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric'
		]);
		if ($validator->fails()) {
			$returnData = array ( 'status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
			return response()->json($returnData, 400);
		}
		try {
//			$date_range = explode(' - ', $Data['date_range']);
			$vehicleIds = [];
			$pumpIds = [];
			$attendantIds = [];
			$customerIds = [];
			$gradeIds = [];
			$tankIds = [];
			$hoseIds = [];
			if ($Data['vehicles']) {
				$vehicleIds = explode(',', $Data['vehicles']);
			}
			if ($Data['pumps']) {
				$pumpIds = explode(',', $Data['pumps']);
			}
			if ($Data['attendants']) {
				$attendantIds = explode(',', $Data['attendants']);
			}
			if ($Data['customers']) {
				$customerIds = explode(',', $Data['customers']);
			}
			if ($Data['grades']) {
				$gradeIds = explode(',', $Data['grades']);
			}
			if ($Data['tanks']) {
				$tankIds = explode(',', $Data['tanks']);
			}
			if ($Data['hoses']) {
				$hoseIds = explode(',', $Data['hoses']);
			}
			$site = Sites::with([
				'customer_transaction' => function ($que) use ($hoseIds,$vehicleIds,$pumpIds, $attendantIds, $customerIds,$gradeIds, $tankIds, $Data) {
					if (count($customerIds)) {
						$que = $que->whereIn('customer_id', $customerIds);
					}
					if (count($hoseIds)) {
						$que = $que->whereIn('hose_id', $hoseIds);
					}
					if (count($attendantIds)) {
						$que = $que->whereIn('attendant_id', $attendantIds);
					}
					if (count($vehicleIds)) {
						$que = $que->whereIn('vehicle_id', $vehicleIds);
					}
					if (!empty($Data['start_date']) && !empty($Data['end_date'])) {
						$start_date = \Carbon\Carbon::parse($Data['start_date'])->format('Y-m-d H:i:s');
						$end_date = \Carbon\Carbon::parse($Data['end_date'])->format('Y-m-d H:i:s');
						$que->where('end_date', '>=', $start_date)->where('end_date', '<=', $end_date);
//						$que->whereBetween('created_at', [$start_date, $end_date]);
					}
					$que = $que->where('litres', '>' , 0)->onlyTrashed()->with([
						'hose' => function ($query) use ($tankIds, $pumpIds, $gradeIds) {

								$query = $query->selectRaw('id,name,tank_id,pump_id')
									->whereHas('pump', function ($query) use ($pumpIds) {
										if (count($pumpIds)) {
											$query = $query->whereIn('id', $pumpIds);
										}
									})
									->with([
										'pump' => function ($query) use ($pumpIds) {
											if (count($pumpIds)) {
												$query = $query->selectRaw('id,name')->whereIn('id', $pumpIds);
											}
											else {
												$query = $query->selectRaw('id,name');
											}
										}
									])
									->whereHas('tank', function ($query) use ($tankIds, $gradeIds) {
										if (count($tankIds)) {
										$query = $query->whereIn('id', $tankIds);
										}
										$query = $query->whereHas('grades', function ($q) use ($gradeIds) {
											if (count($gradeIds)) {
												$q = $q->whereIn('id', $gradeIds);
											}
										});
									});

							$query = $query->orderBy('id');
						},
						'attendant'=>function($q1){
							$q1->select('id', 'name');
						},
						'customer'=>function($q1){
							$q1->select('id', 'name');
						}
					]);
				}
			])->find($Data['site_id']);
			$customers = $site->customer_transaction;
			return Datatables::of($customers)->make(true);

//			$returnData = array ( 'status'=> 'success', 'message' => 'Requested transaction listed successfully!', 'data' => $customers, 'code' => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status'  => 'failure', 'message' => 'Requested transaction listed not found', 'code'    => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}

	public function FinancialTransaction()
	{
		$Data = $this->request->all();
		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric'
		]);
		if ($validator->fails()) {
			$returnData = array ( 'status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
			return response()->json($returnData, 400);
		}
		try {
//			$date_range = explode(' - ', $Data['date_range']);
			$vehicleIds = [];
			$pumpIds = [];
			$attendantIds = [];
			$customerIds = [];
			$gradeIds = [];
			$tankIds = [];
			$hoseIds = [];
			if ($Data['vehicles']) {
				$vehicleIds = explode(',', $Data['vehicles']);
			}
			if ($Data['pumps']) {
				$pumpIds = explode(',', $Data['pumps']);
			}
			if ($Data['attendants']) {
				$attendantIds = explode(',', $Data['attendants']);
			}
			if ($Data['customers']) {
				$customerIds = explode(',', $Data['customers']);
			}
			if ($Data['grades']) {
				$gradeIds = explode(',', $Data['grades']);
			}
			if ($Data['tanks']) {
				$tankIds = explode(',', $Data['tanks']);
			}
			if ($Data['hoses']) {
				$hoseIds = explode(',', $Data['hoses']);
			}

			$ctQuery = CustomerTransaction::where('site_id', request()->site_id)->where('litres', '>', 0);

			if (count($customerIds)) {
				$ctQuery = $ctQuery->whereIn('customer_id', $customerIds);
			}
			if (count($hoseIds)) {
				$ctQuery = $ctQuery->whereIn('hose_id', $hoseIds);
			}
			if (count($attendantIds)) {
				$ctQuery = $ctQuery->whereIn('attendant_id', $attendantIds);
			}
			if (count($vehicleIds)) {
				$ctQuery = $ctQuery->whereIn('vehicle_id', $vehicleIds);
			}
			if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//				$strt_date = $date_range[0] . " 00:00:00";
				$ctQuery = $ctQuery->where('end_date', '>=', $Data['start_date'])->where('end_date', '<=', $Data['end_date']);
			}

			$ct= $ctQuery->with(['hose' => function ($query) use ($tankIds, $pumpIds, $gradeIds) {
					$query = $query->selectRaw('id,name,tank_id,pump_id')
						->withAndWhereHas('pump', function ($query) use ($pumpIds) {
							if (count($pumpIds)) {
								$query = $query->whereIn('id', $pumpIds);
							}
						})
						->withAndWhereHas('tank', function ($query) use ($tankIds, $gradeIds) {
							if (count($tankIds)) {
								$query = $query->whereIn('id', $tankIds);
							}
							$query = $query->whereHas('grades', function ($q) use ($gradeIds) {
								if (count($gradeIds)) {
									$q->whereIn('id', $gradeIds);
								}
							});
						});
					$query = $query->orderBy('id');
				}, 'attendant'=>function($q1){
					$q1->select('id','name');
			}, 'customer'=>function($q2){
				$q2->select('id','name');
			}
			])->get();

			$fuelDropQry = FuelDrop::whereHas('tank', function ($q1) use ($tankIds) {
				if (count($tankIds)) {
					$q1->whereIn('id', $tankIds);
				}
			})->whereHas('grade', function ($q1) use ($gradeIds) {
				if (count($gradeIds)) {
					$q1->whereIn('id', $gradeIds);
				}
			})->with(['supplier'=>function($q1){
				$q1->select('id', 'name');
			}, 'tank'=>function($q1){
				$q1->select('id', 'name');
			}]);

			if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
				$fuelDropQry = $fuelDropQry->where('purchase_date', '>=', $Data['start_date'])->where('purchase_date', '<=', $Data['end_date']);
			}
			$fuelDrops = $fuelDropQry->where('site_id', request()->site_id)->get();

			$finalData = [];$i=0;
			foreach ($ct as $transaction){
				if($transaction->hose) {
					$finalData[$i]['transaction_date'] = date('Y-m-d H:i:s', strtotime($transaction->end_date));
					$finalData[$i]['type'] = 'Pump Transaction';
					$finalData[$i]['supplier'] = ($transaction->customer) ? $transaction->customer->name : '';
					$finalData[$i]['tank_name'] = ($transaction->hose) && ($transaction->hose->tank) ? $transaction->hose->tank->name : "";
					$finalData[$i]['attendant_name'] = ($transaction->attendant) ? $transaction->attendant->name : '';
					$finalData[$i]['amount'] = '+'.number_format($transaction->total_cost,2);
					$finalData[$i]['liters'] = '-'.number_format($transaction->litres,2);
					$finalData[$i]['pump_name'] = ($transaction->hose) && ($transaction->hose->pump) ? $transaction->hose->pump->name : '';
					$i++;
				}
			}
			foreach ($fuelDrops as $fuelDrop){
				$finalData[$i]['transaction_date'] = date('Y-m-d H:i:s', strtotime($fuelDrop->purchase_date));
				$finalData[$i]['type'] = 'Fuel Drop';
				$finalData[$i]['supplier'] = ($fuelDrop->supplier) ? $fuelDrop->supplier->name : '';
				$finalData[$i]['tank_name'] = ($fuelDrop->tank) ? $fuelDrop->tank->name : "";
				$finalData[$i]['attendant_name'] = '';
				$finalData[$i]['amount'] = '-'.number_format($fuelDrop->tot_inc_vat,2);
				$finalData[$i]['liters'] = '+'.number_format($fuelDrop->litres,2);
				$finalData[$i]['pump_name'] = '';
				$i++;
			}

			$returnData = array ( 'status'=> 'success', 'message' => 'Requested transaction listed successfully!', 'data' => $finalData, 'code' => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status'  => 'failure', 'message' => 'Requested transaction listed not found', 'code'    => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	function array_orderby()
	{
	    $args = func_get_args();
	    $data = array_shift($args);
	    foreach ($args as $n => $field) {
	        if (is_string($field)) {
	            $tmp = array();
	            foreach ($data as $key => $row)
	                $tmp[$key] = $row[$field];
	            $args[$n] = $tmp;
	            }
	    }
	    $args[] = &$data;
	    call_user_func_array('array_multisort', $args);
	    return array_pop($args);
	}
	public function sarsReport()
	{
		$Data = $this->request->all();
		$finalData = [];
		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric'
		]);
		if ($validator->fails()) {
			$returnData = array ( 'status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
			return response()->json($returnData, 400);
		}
		try {
//			$date_range = explode(' - ', $Data['date_range']);
			$start_date = date('Y-m-d');
//			if (count($date_range) == 2) {
//				$start_date = $date_range[0] . " 00:00:00";
//				$end_date = $date_range[1] . " 00:00:00";
//			}

			$tankQuery = Tanks::select('id', 'name')
				->with(['customer_transaction'=>function($ctq) use($Data){
					$ctq = $ctq->select('customer_transactions.id', 'odo_meter', 'litres', 'start_date', 'end_date', 'job_id', 'vehicle_id',\DB::raw("'transaction' as name"))
						->with(['vehicle'=>function($vq){
//						$vq->select('vehicles.id', 'name', 'sars_type');
					}, 'job'=>function($jq){
							$jq->select('id', 'name');
						}]);
					if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//						$start_date = $date_range[0] . " 00:00:00";
//						$end_date = $date_range[1] . " 00:00:00";
						$ctq = $ctq->where('end_date', '>=', $Data['start_date'])
							->where('end_date', '<=', $Data['end_date'])
							->where('litres','>',0);// Added By HS not to show 0 litres data
					}
					if(!empty($Data['location_ids'])){
						$ctq = $ctq->whereIn('location_id', explode(',', $Data['location_ids']));
					}
				},'fuel_drops'=>function($fdq) use($Data){
					$fdq = $fdq->select('id', 'litres', 'purchase_date as end_date', 'tank_id', 'created_at', \DB::raw("'fuel_drop' as name"));
					if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//						$start_date = $date_range[0] . " 00:00:00";
//						$end_date = $date_range[1] . " 00:00:00";
						$fdq->where('purchase_date', '>=', $Data['start_date'])->where('purchase_date', '<=', $Data['end_date']);
					}
				}]);
			if(!empty($Data['tank_ids'])){
				$tankQuery = $tankQuery->whereIn('id', explode(',', $Data['tank_ids']));
			}
			$tanks = $tankQuery->where('site_id', request()->site_id)->get();

			foreach ($tanks as $tank){
				$opening_balance = 0;
				$cal_opening_balance = 0;
				if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
					$transactionTotal = CustomerTransaction::where('end_date', '<', $Data['start_date'])
						->whereHas('hose', function($q1) use($tank){
							$q1->where('tank_id', $tank->id);
						})->sum('litres');
					$fuelDropsTotal = FuelDrop::where('purchase_date', '<', $Data['start_date'])->where('tank_id', $tank->id)->sum('litres');

					$opening_balance = number_format(($fuelDropsTotal - $transactionTotal), 2);
					$cal_opening_balance = (floatval(str_replace(",","",$opening_balance)));

					$opening_date = date('Y-m-d', strtotime($Data['start_date']));
				}
				$tankTrans = array_merge($tank->customer_transaction->toArray(), $tank->fuel_drops->toArray());
				$orderByArray = $this->array_orderby($tankTrans,'end_date', SORT_ASC);
//13 - 86.88
//14 - 105.96
				$i=0;
				foreach ($orderByArray as $key=>$trans){
					$finalData[$tank->id]['name'] = $tank->name;
					$finalData[$tank->id]['data'][$i]['invoice_number'] = 'N/A';

					if($trans['name'] == 'fuel_drop'){
						$finalData[$tank->id]['data'][$i]['opening_balance'] = str_replace(',','',$opening_balance);
						$finalData[$tank->id]['data'][$i]['purchased_date'] = date('Y-m-d', strtotime($trans['end_date']));
						$finalData[$tank->id]['data'][$i]['purchased_litres'] = number_format($trans['litres'],2);

						$finalData[$tank->id]['data'][$i]['metre_reading_before_disposal'] = number_format(($cal_opening_balance + $trans['litres']),2);

						$finalData[$tank->id]['data'][$i]['transaction_date'] = 'N/A';
						$finalData[$tank->id]['data'][$i]['transaction_litre'] = 'N/A';
						$finalData[$tank->id]['data'][$i]['transaction_vehicle_name'] = 'N/A';
						$finalData[$tank->id]['data'][$i]['vehicle_sars_type'] = 'N/A';
						$finalData[$tank->id]['data'][$i]['job_name'] = 'N/A';
						$finalData[$tank->id]['data'][$i]['final_tank_balance'] = number_format(($cal_opening_balance + $trans['litres']),2);

						$opening_balance = number_format(($cal_opening_balance + $trans['litres']), 2);
						$cal_opening_balance = (floatval(str_replace(",","",$opening_balance)));

					}
					if($trans['name'] == 'transaction'){
						$finalData[$tank->id]['data'][$i]['opening_balance'] = (!$key) ? str_replace(',','',$opening_balance) : 'N/A';
						$finalData[$tank->id]['data'][$i]['purchased_date'] = 'N/A';
						$finalData[$tank->id]['data'][$i]['purchased_litres'] = 'N/A';
						$finalData[$tank->id]['data'][$i]['metre_reading_before_disposal'] = $opening_balance;
						$finalData[$tank->id]['data'][$i]['transaction_date'] = date('Y-m-d', strtotime($trans['end_date']));
						$finalData[$tank->id]['data'][$i]['transaction_litre'] = number_format($trans['litres'],2);
						$finalData[$tank->id]['data'][$i]['transaction_vehicle_name'] = ($trans['vehicle']) ? $trans['vehicle']['name'] : '';
						$finalData[$tank->id]['data'][$i]['vehicle_sars_type'] = ($trans['vehicle']) ? $trans['vehicle']['sars_type'] : '';
						$finalData[$tank->id]['data'][$i]['job_name'] = (isset($trans['job'])) ? $trans['job']['name'] : '';
						$finalData[$tank->id]['data'][$i]['final_tank_balance'] = number_format(($cal_opening_balance - $trans['litres']),2);

						$opening_balance = number_format(($cal_opening_balance - $trans['litres']), 2);
						$cal_opening_balance = (floatval(str_replace(",","",$opening_balance)));
					}
					if($key == 0){
						if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
							$opening_date = date('Y-m-d', strtotime($trans['end_date']));
						}
					}else {
						$opening_date = date('Y-m-d', strtotime($trans['end_date']));
					}
					$finalData[$tank->id]['data'][$i]['opening_date'] = $opening_date;
					$i++;
				}
//dd($tank->id, $start_date, $end_date);
				if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
					$start_date = $Data['start_date'];
					$end_date = $Data['end_date'];
				}else{
					$start_date = "1999-01-01 00:00:00";
					$end_date = "3000-12-01 00:00:00";
				}
				$finalData[$tank->id]['logbook'] = \DB::select('call SARS2('.$tank->id.', "'.$start_date.'", "' . $end_date.'")');
//				/* Diesel Usage Logbook */
//				$dieselArr = [];
//				$outArray = [];
//				$avglpkm = [];
//				$orderByArrayTransaction = $this->array_orderby($tank->customer_transaction->toArray(), 'end_date', SORT_ASC);
////				dd($orderByArrayTransaction);
//				if($orderByArrayTransaction){
//					$finalData[$tank->id]['total_eligible_litres'] = 0;
//				}
//				foreach ($orderByArrayTransaction as $trans) {
//
//					$vehicle_id = $trans['vehicle']['id'];
//
//					if (isset($dieselArr[$vehicle_id])) {
//						if (!isset($avglpkm[$vehicle_id])) {
//							$avglpkm[$vehicle_id] = $this->AvgLpKm($vehicle_id, $end_date);
//						}
//
//						$outArray[] = array (
//							"C1" => date('Y-m-d', strtotime($dieselArr[$vehicle_id]['end_date'])),
//							"C2" => $tank->name,
//							"C3" => $dieselArr[$vehicle_id]['litres'],
//							"D1" => $trans['vehicle']['make'] . " " . $trans['vehicle']['model'],
//							"D2" => $dieselArr['opening_balances'][$vehicle_id],
//							"D3" => $trans['vehicle']['registration_number'],
//							"D4" => $dieselArr[$vehicle_id]['odo'],
//							"D5" => $trans['odo_meter'],
//							"D6" => $trans['odo_meter'] - $dieselArr[$vehicle_id]['odo'],
//							"D7" => $avglpkm[$vehicle_id] * ($trans['odo_meter'] - $dieselArr[$vehicle_id]['odo']),
//							"D8" => $dieselArr[$vehicle_id]['litres'] + $dieselArr['opening_balances'][$vehicle_id]
//								- ($avglpkm[$vehicle_id] * ($trans['odo_meter'] - $dieselArr[$vehicle_id]['odo'])),
//							"E1" => $trans['job']['name'],
//							"E2" => "N/A",
//							"E3" => date('Y-m-d', strtotime($dieselArr[$vehicle_id]['end_date'])),
//							"E4" => 'address line 1 to 4 of customer',
//							"E5" => $avglpkm[$vehicle_id] * ($trans['odo_meter'] - $dieselArr[$vehicle_id]['odo'])
//						);
//						$finalData[$tank->id]['total_eligible_litres'] += $avglpkm[$vehicle_id] * ($trans['odo_meter'] - $dieselArr[$vehicle_id]['odo']);
//
//						$dieselArr['opening_balances'][$vehicle_id] = $dieselArr[$vehicle_id]['litres'] +
//							$dieselArr['opening_balances'][$vehicle_id] - ($avglpkm[$vehicle_id] * ($trans['odo_meter'] - $dieselArr[$vehicle_id]['odo']));
//						unset($dieselArr[$vehicle_id]);
//					}
//					else {
//						$previousTrans = CustomerTransaction::select('vehicle_id', 'litres', 'odo_meter')
//							->where('end_date', '<', $start_date)
//							->where('vehicle_id', $vehicle_id)
//							->get();
//						$before = CustomerTransaction::select('vehicle_id', 'litres', 'odo_meter')
//							->where('end_date', '<', $start_date)
//							->where('vehicle_id', $vehicle_id)
//							->orderBy('id', 'desc')
//							->first();
//
//						$dieselArr['opening_balances'][$vehicle_id] = ($previousTrans->sum('litres')) - ((
//							($before->odo_meter) - ($previousTrans->first()->odo_meter)) * $this->AvgLpKm($vehicle_id, $end_date));
//					}
//					$dieselArr[$vehicle_id]['litres'] = $trans['litres'];
//					$dieselArr[$vehicle_id]['odo'] = $trans['odo_meter'];
//					$dieselArr[$vehicle_id]['job_tag'] = (isset($trans['job'])) ? $trans['job']['name'] : "";
//					$dieselArr[$vehicle_id]['end_date'] = $trans['end_date'];
//				}
//				if(!empty($outArray)) {
//					$finalData[$tank->id]['logbook'] = $outArray;
//				}
			}

			$returnData = array ( 'status'=> 'success', 'message' => 'Requested transaction listed successfully!', 'data' => $finalData, 'code' => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status'  => 'failure', 'message' => 'Requested transaction listed not found', 'code'    => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}

//	public function AvgLpKm($vehicle_ids = '', $end_date = '')
//	{
//		$calcArr = [];
//		$outArr = [];
//		$transactionTotal = CustomerTransaction::select('vehicle_id', 'litres', 'odo_meter')
//			->where('end_date', '<=', $end_date)
//			->where('vehicle_id', $vehicle_ids)
//			->get();
//		foreach ($transactionTotal as $row) {
//			if(!isset($calcArr[$row['vehicle_id']])){
//				$calcArr[$row['vehicle_id']] = [];
//				$calcArr[$row['vehicle_id']]['literSUM'] = 0;
//			}
//			$calcArr[$row['vehicle_id']]['literSUM'] += $row['litres'];
//			$calcArr[$row['vehicle_id']]['literLast'] = $row['litres'];
//			if (!isset($calcArr[$row['vehicle_id']]['odofirst'])) {
//				$calcArr[$row['vehicle_id']]['odofirst'] = $row['odo_meter'];
//			}
//			$calcArr[$row['vehicle_id']]['odolast'] = $row['odo_meter'];
//		}
//
//		foreach ($calcArr as $v_id => $vehicle) {
//			$outArr[$v_id] = ($vehicle['odolast'] == $vehicle['odofirst'] ? 0 : ($vehicle['literSUM'] - $vehicle['literLast']) / ($vehicle['odolast'] - $vehicle['odofirst']));
//		}
//		return $outArr[$vehicle_ids];
//	}
}
