<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\JwtAuthController;
use App\Interfaces\SiteRepositoryInterface;
use App\Models\CustomerTransaction;
use App\Models\Payment;
use App\Models\Sites;
use App\Models\Tanks;
use App\Models\Vehicles;
use App\Repositories\AtgDataRepository;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;
use App\User;
use Datatables;

class CustomerReportController extends JwtAuthController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
    }

    /* Customers -> Vehicle List
     * Getting Required inputs
     */
    public function customerVehicles()
    {
        try {
            $Data = auth()->user()->sites()->with('customers','vehicles')->get();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'customers not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    /* Customers -> Vehicle List
     * Getting Results based on the inputs
     */
    public function customerVehiclesReport()
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
            $vehicleIds = [];
            $customerIds = [];
            if ($Data['vehicles']) {
                $vehicleIds = explode(',', $Data['vehicles']);
            }
            if ($Data['customers']) {
                $customerIds = explode(',', $Data['customers']);
            }

            $site = Sites::with(['customers' => function ($que) use ($vehicleIds, $customerIds, $Data) {
                if (count($customerIds)) {
                    $que = $que->whereIn('customer_id', $customerIds);
                }
                $que = $que->with(['vehicles' => function ($query) use ($vehicleIds, $customerIds, $Data) {
                    if (count($vehicleIds)) {
                        $query = $query->whereIn('id', $vehicleIds);
                    }

                    if (count($customerIds)) {
                        $query = $query->whereIn('customer_id', $customerIds);
                    }
                    if (!empty($Data['start_date']) &&  !empty($Data['end_date']) && count($vehicleIds)) {
//                        $date_range[0] = $date_range[0] . " 00:00:00";
                        $query = $query->where('vehicles.created_at', '>=', $Data['start_date'])->where('vehicles.created_at', '<=', $Data['end_date']);
                    }
                    //$query = $query->where('status', 'Active');
                    $query = $query->orderBy('id');
                    //$query = $query->with('customer');
                    $query = $query->with('tag');
                }]);
            }])->find($Data['site_id']);
            $customers = $site->customers;
            $returnData = array('status' => 'success', 'message' => 'Requested vehicles listed successfully!', 'data' => $customers, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'vehicles not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    /* Customers -> Vehicle List -> Transactions
     * Getting Results based on the inputs
     */
    public function customerVehicleTransactionsReport()
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
            $vehicleIds = [];
            $customerIds = [];
            if ($Data['vehicles']) {
                $vehicleIds = explode(',', $Data['vehicles']);
            }
            if ($Data['customers']) {
                $customerIds = explode(',', $Data['customers']);
            }
            $site = Sites::with(['customers' => function ($que) use ($vehicleIds, $customerIds,$Data) {
	            if (count($customerIds)) {
		            $que = $que->whereIn('customer_id', $customerIds);
	            }
                $que = $que->with(['vehicles' => function ($query) use ($vehicleIds, $customerIds,$Data) {
                    if (count($vehicleIds)) {
                        $query = $query->whereIn('id', $vehicleIds);
                    }
//	                if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//                        $date_range[0] = $date_range[0] . " 00:00:00";
//                        $query = $query->where('vehicles.created_at', '>=', $Data['start_date'])->where('vehicles.created_at', '<=', $Data['end_date']);
//                    }
                    //$query = $query->where('status', 'Active');
                    $query = $query->orderBy('id');
                    $query = $query->with(['transactions'=>function($q2) use($Data){
	                    if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
	   //                        $date_range[0] = $date_range[0] . " 00:00:00";
	                           $query = $q2->where('end_date', '>=', $Data['start_date'])->where('end_date', '<=',
			                           $Data['end_date'])->where('litres', '>', 0);// Added By HS not to show 0 litres data
	                       }
                    	$q2->with(['attendant'=>function($q1){
                    		$q1->select('id','name');
	                    },'hose'=>function($hoseq){
                            $hoseq->select('id', 'tank_id')->with(['tank'=>function($q1){
                                $q1->select('id','grade_id')->with(['grades'=>function($q2){
                                    $q2->select('id', 'name');
                                }]);
                            }]);
                        }])->where('site_id',$Data['site_id'])->where('litres', '>', 0);
                    }]);
                }]);
            }])->find($Data['site_id']);
            $customers = $site->customers;

//	        CustomerTransaction::withAndWhereHas('vehicle', function ($query) use ($vehicleIds, $date_range, $Data) {
//		        if (count($vehicleIds)) {
//			        $query = $query->whereIn('id', $vehicleIds);
//		        }
//		        if (count($date_range) == 2) {
//			        $date_range[0] = $date_range[0] . " 00:00:00";
//			        $query = $query->where('vehicles.created_at', '>=', $date_range[0]);
//		        }
//		        if (count($date_range) == 2) {
//			        $date_range[1] = $date_range[1] . " 00:00:00";
//			        $query = $query->where('vehicles.created_at', '<=', $date_range[1]);
//		        }
//		        $query->orderBy('id');
//	        })->withAndWhereHas('customer', function ($q2) use ($customerIds) {
//		        if (count($customerIds)) {
//			        $q2->whereIn('customer_id', $customerIds);
//		        }
//	        })->get();

            $finalData = [];
            foreach ($site->customers as $customerKey=>$customer){
	            if(count($customer->vehicles) > 0) {
		            $finalData[$customerKey]['customer_name'] = $customer->name;
		            $finalData[$customerKey]['vehicles'] = [];
		            foreach ($customer->vehicles as $vehicleKey => $vehicle) {
			            if (count($vehicle->transactions) > 0) {
				            $finalData[$customerKey]['vehicles'][$vehicleKey] = [];
				            $finalData[$customerKey]['vehicles'][$vehicleKey]['vehicle_name'] = $vehicle->name;
				            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'] = [];
				            foreach ($vehicle->transactions as $transactionKey => $transaction) {
					            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'][$transactionKey] = [];
					            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'][$transactionKey]['id'] = $transaction->id;
					            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'][$transactionKey]['start_date'] = $transaction->start_date;
					            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'][$transactionKey]['end_date'] = $transaction->end_date;
					            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'][$transactionKey]['odo_meter'] = $transaction->odo_meter;
					            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'][$transactionKey]['litres'] = $transaction->litres;
					            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'][$transactionKey]['total_cost'] = $transaction->total_cost;
					            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'][$transactionKey]['attendant_name'] = (isset($transaction->attendant) && isset($transaction->attendant->name)) ? $transaction->attendant->name : '';
					            $finalData[$customerKey]['vehicles'][$vehicleKey]['transactions'][$transactionKey]['grade_name'] = (isset($transaction->hose) && isset($transaction->hose->tank) && isset($transaction->hose->tank->grades)) ? $transaction->hose->tank->grades->name : '';
				            }
			            }
		            }
	            }
            }
            $returnData = [];
			foreach ($finalData as $data){
				if(count($data['vehicles']) > 0){
					$returnData[] = $data;
				}
			}
            $returnData = array('status' => 'success', 'message' => 'Requested vehicle transactions listed successfully!', 'data' => $returnData, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'vehicle transactions not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    /* Customers -> Customer List
     * Getting Results based on the inputs
     */
    public function customerListReport()
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
	        $customerIds=[];
	        if ($Data['customers']) {
		        $customerIds = explode(',', $Data['customers']);
	        }
	        $site = Sites::with([
		        'customers' => function ($query) use ($customerIds) {
			        if (count($customerIds)) {
				        $query = $query->whereIn('customer_id', $customerIds);
			        }
		        }
	        ])->find($Data['site_id']);
            $customers = $site->customers;
            return Datatables::of($customers)->make(true);

//            $returnData = array('status' => 'success', 'message' => 'Requested customers listed successfully!', 'data' => $customers, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'customers not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    /* Customers -> Customer Statements (Transactions & Payments)
     * Getting Results based on the inputs
     */
    public function customerStatementsReport()
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

            $site = Sites::with(['customers' => function ($query) use ($customerIds, $Data) {
                if (count($customerIds)) {
                    $query = $query->whereIn('customer_id', $customerIds);
                }
                //$query = $query->where('status', 'Active');
                $query = $query->orderBy('id');
	            $query = $query->with([
		            'transactions' => function ($queryTrans) use ($Data) {
			            $queryTrans->where('site_id', (int)request()->site_id)->where('litres', '>', 0);
	            	    $queryTrans->with(['vehicle'=>function($q1){
	            	    	$q1->select('id', 'name');
		                },'hose.tank'=>function($q2){
	            	    	$q2->select('id', 'name', 'grade_id')->with(['grades'=>function($q3){
	            	    		$q3->select('id', 'name');
			                }]);
		                }]);
			            if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//				            $date_range[0] = $date_range[0] . " 00:00:00";
				            $queryTrans = $queryTrans->where('end_date', '>=', $Data['start_date'])->where('end_date', '<=', $Data['end_date']);
			            }
		            },
		            'payments'     => function ($queryPayment) use ($Data) {
	            	    $queryPayment->where('site_id', (int)request()->site_id);
			            if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//				            $date_range[0] = $date_range[0] . " 00:00:00";
				            $queryPayment= $queryPayment->where('created_at', '>=', $Data['start_date'])->where('created_at', '<=', $Data['end_date']);
			            }
		            }
	            ]);
            }])->find($Data['site_id']);

	        if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
		        $date1 = $Data['start_date'];
	        }
            $paymentAndTrans = [];
	        $finalData= [];
            foreach ($site->customers as $key=>$customer){
	            $i=0;
	            $finalData[$key] = [];
	            $finalData[$key]['transactions'] = [];
	            $finalData[$key]['customer_name'] = $customer->name;

	            $ctQry = CustomerTransaction::where('site_id', request()->site_id)->where('customer_id', $customer->id);
	            $paymentQry = Payment::where('site_id', request()->site_id)->where('customer_id', $customer->id);

	            if (isset($date1)) {
		            $transactionTotal = $ctQry->where('end_date', '<', $date1)->sum('total_cost');
		            $paymentTotal = $paymentQry->where('created_at', '<', $date1)->sum('amount');
	            }else{
		            $transactionTotal = 0;
		            $paymentTotal = 0;
	            }
//				dd($transactionTotal, $paymentTotal);
            	$opening_total = $paymentTotal - $transactionTotal;
            	$tempTotal = $opening_total;

	            $paymentAndTrans[$key]['transactions'] = [];
	            /* Transaction */
	            foreach ($customer->transactions as $transaction){
		            $paymentAndTrans[$key]['transactions'][$i]['type'] = 'transaction';
		            $paymentAndTrans[$key]['transactions'][$i]['vehicle'] = ($transaction->vehicle) ? $transaction->vehicle->name : $transaction->name;
		            $paymentAndTrans[$key]['transactions'][$i]['grade'] = ($transaction->hose) &&
		            ($transaction->hose->tank) && ($transaction->hose->tank->grades) ? $transaction->hose->tank->grades->name : '';
		            $paymentAndTrans[$key]['transactions'][$i]['litres'] = $transaction->litres;
		            $paymentAndTrans[$key]['transactions'][$i]['date_time'] = date('Y-m-d H:i', strtotime($transaction->start_date));
		            $paymentAndTrans[$key]['transactions'][$i]['debit'] = '';
		            $paymentAndTrans[$key]['transactions'][$i]['credit'] = $transaction->total_cost;
		            $paymentAndTrans[$key]['transactions'][$i]['amount'] = $transaction->total_cost;
		            $i++;
	            }

	            /* Payments */
	            foreach ($customer->payments as $payment){
//		            $tempFinalData[$i] = [];
		            $paymentAndTrans[$key]['transactions'][$i]['vehicle'] = 'Payment';
		            $paymentAndTrans[$key]['transactions'][$i]['type'] = 'Payment';
		            $paymentAndTrans[$key]['transactions'][$i]['grade'] = '';
		            $paymentAndTrans[$key]['transactions'][$i]['litres'] = '';
		            $paymentAndTrans[$key]['transactions'][$i]['date_time'] = date('Y-m-d H:i', strtotime($payment->created_at));
		            $paymentAndTrans[$key]['transactions'][$i]['debit'] = $payment->amount;
		            $paymentAndTrans[$key]['transactions'][$i]['credit'] = '';
		            $paymentAndTrans[$key]['transactions'][$i]['amount'] = $payment->amount;
		            $i++;
	            }
	            $sortedData = collect($paymentAndTrans[$key]['transactions'])->sortBy('date_time');

	            $f_line['vehicle'] = 'Balance brought Forward';
	            $f_line['grade'] = '';
	            $f_line['litres'] = '';
	            if (isset($date1)) {
		            $f_line['date_time'] = date('Y-m-d', strtotime($date1));
	            }else{
		            $f_line['date_time'] = date('Y-m-d', strtotime($customer->created_at));
	            }
	            $f_line['debit'] = '';
	            $f_line['credit'] = '';
	            $f_line['total'] = $opening_total;

	            $finalData[$key]['transactions'][] = $f_line;

	            foreach ($sortedData as $val){
		            $val['tempTotal'] = $tempTotal;
		            if($val['type'] == 'transaction'){
						$tempTotal = $tempTotal - $val['amount'];
					}else{
						$tempTotal = $tempTotal + $val['amount'];
					}
		            $val['total'] = $tempTotal;
					$finalData[$key]['transactions'][] = $val;
	            }
	            $finalData[$key]['final_total'] = $tempTotal;
            }
//dd($finalData);
            $returnData = array('status' => 'success', 'message' => 'Requested customer statements listed successfully!', 'data' => $finalData, 'code' => 200);
        }
        catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'customer statements not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function vehicleTransactionsReport()
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

            // Grades
            $siteData = Sites::with(['customers' => function ($query) use ($customerIds, $Data) {
                if (count($customerIds)) {
                    $query = $query->whereIn('id', $customerIds);
                }
                // Tanks
                $query = $query->with(['vehicles' => function ($quer) use ($Data) {
                    $quer = $quer->with(['transactions' => function ($que) use ($Data) {
                        if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
	                        $start_date = \Carbon\Carbon::parse($Data['start_date'])->format('Y-m-d H:i:s');
	                        $end_date = \Carbon\Carbon::parse($Data['end_date'])->format('Y-m-d H:i:s');
	                        $que->where('end_date', '>=', $start_date)
		                        ->where('end_date', '<=', $end_date)
	                            ->where('litres','>',0);// Added By HS not to show 0 litres data
//	                        $que->whereBetween('customer_transactions.created_at', [$start_date, $end_date]);
                        }
                    }]);
                }]);
            }])->find($Data['site_id']);

            $finalArray = [];
            $customerArray = [];

            foreach ($siteData->customers as $customer) {
                $customerArray = ['customer_id' => $customer->id, 'customer_name' => $customer->name, 'vehicle_id' => (int)0, 'vehicle_name' => '', 'trans_count' => (int)0, 'total_cost' => (float)0, 'total_litres' => (int)0];
                foreach ($customer->vehicles as $vehicle) {
                    $customerArray['vehicle_id'] = $vehicle->id;
                    $customerArray['vehicle_name'] = $vehicle->name;
                    $customerArray['total_litres'] = (int)0;
                    $customerArray['total_cost'] = (float)0;
                    $customerArray['trans_count'] = (int)0;
                    if($vehicle->transactions){
                        $customerArray['total_litres'] += collect($vehicle->transactions)->sum('litres');
                        $customerArray['total_cost'] += collect($vehicle->transactions)->sum('total_cost');
                        $customerArray['trans_count'] += count($vehicle->transactions);
                    }
                    if($customerArray['total_litres'] > 0){
	                    $finalArray[] = $customerArray;
                    }
                }
            }
            return Datatables::of(collect($finalArray))->make(true);
            $returnData = array('status' => 'success', 'data' => $finalArray, 'message' => 'Requested vehicle transaction summary listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'vehicles not found', 'code' => 400);
        }

        return response()->json($returnData, $returnData['code']);
    }
    
}
