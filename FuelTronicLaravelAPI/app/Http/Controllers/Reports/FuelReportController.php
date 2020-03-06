<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\JwtAuthController;
use App\Models\AtgData;
use App\Models\AtgTransaction;
use App\Models\CustomerTransaction;
use App\Models\Grades;
use App\Models\Sites;
use App\Models\Vehicles;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use JWTAuth, Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Datatables;

class FuelReportController extends JwtAuthController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
    }

    /* Suppliers -> Supplier List
     * Getting Required inputs
     */
    public function getSitesTanksGrades()
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
            $Data = auth()->user()->sites()->with('tanks')->find($Data['site_id']);
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tanks not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function getSitesTanksGradesSuppliers()
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
            $Data = auth()->user()->sites()->with('tanks', 'grades', 'suppliers')->find($Data['site_id']);
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tanks or grades not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function getSitesGrades()
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
            $returnData = array('status' => 'failure', 'message' => 'grades not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function getSitesHosesTanksGrades()
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
            $Data = auth()->user()->sites()->with('hoses', 'tanks', 'grades')->find($Data['site_id']);
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Tanks or hoses not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function getSitesAttendantsGrades()
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
            $Data = auth()->user()->sites()->with('attendants', 'grades')->find($Data['site_id']);
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Attendants or grades not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    // 3) Deliveries Per Period
    public function getDeliveriesPerPeriod()
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
            $tankIds = [];
            $gradeIds = [];
            $supplierIds = [];
            if ($Data['tanks']) {
                $tankIds = explode(',', $Data['tanks']);
            }
            if ($Data['grades']) {
                $gradeIds = explode(',', $Data['grades']);
            }
            if ($Data['suppliers']) {
                $supplierIds = explode(',', $Data['suppliers']);
            }

            $site = Sites::with(['fuel_drops' => function ($que) use ($tankIds, $gradeIds, $supplierIds, $Data) {
	            // Added By HS not to show 0 litres data
            	$que = $que->where('litres', '>', 0);

            	if (count($tankIds)) {
                    $que = $que->whereIn('tank_id', $tankIds);
                }
                if (count($gradeIds)) {
                    $que = $que->whereIn('grade_id', $gradeIds);
                }
                if (count($supplierIds)) {
                    $que = $que->whereIn('supplier_id', $supplierIds);
                }
                if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//                    $date_range[0] = $date_range[0] . " 00:00:00";
                    $que = $que->where('purchase_date', '>=', $Data['start_date'])->where('purchase_date', '<=', $Data['end_date']);
                }
                $que = $que->with(['supplier' => function ($qu) {
                    $qu = $qu->selectRaw('id, name');
                }, 'tank' => function ($qu) {
                    $qu = $qu->selectRaw('id, name');
                }
                ]);
            }])->find($Data['site_id']);
            $fuel_drops = $site->fuel_drops;

	        return Datatables::of($fuel_drops)->make(true);

//	        $finalData = $this->paginate($fuel_drops, 1000);
//            return response($finalData);
//            $returnData = array('status' => 'success', 'message' => 'Requested deliveries listed successfully!', 'data' => $fuel_drops, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Requested deliveries listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    // 4) Pump Transactions
    public function getPumpTransactions()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }

        $siteId = $Data['site_id'];

        try {
//            $date_range = explode(' - ', $Data['date_range']);

            $siteData = Sites::with(['pumps' => function ($query) use ($Data, $siteId) {
                $query = $query->with(['customer_transaction' => function ($q) use ($Data, $siteId) {
                    $q = $q->where('customer_transactions.site_id', $siteId)->where('customer_transactions.litres', '>', 0);
                    if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
                        $start_date = \Carbon\Carbon::parse($Data['start_date'])->format('Y-m-d H:i:s');
                        $end_date = \Carbon\Carbon::parse($Data['end_date'])->format('Y-m-d H:i:s');
	                    $q->where('end_date', '>=', $start_date)->where('end_date', '<=', $end_date);
//                        $q->whereBetween('customer_transactions.created_at', [$start_date, $end_date]);
                    }
                    $q = $q->with(['hose' => function($qh){
                        $qh->selectRaw('id, name');
                    }, 'attendant' => function($qh){
                        $qh->selectRaw('id, name');
                    }, 'vehicle' => function($qh){
                        $qh->selectRaw('id, name');
                    }
                    ]);
                }]);
            }])->find($Data['site_id']);

            $returnData = array('status' => 'success', 'data' => $siteData, 'message' => 'Requested pump transaction listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Requested pump transaction listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    // 5) Dispensed Grade
    public function getDispensedGrades()
    {
        $Data = $this->request->all();

        $validator = \Validator::make($Data, [
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }

        $siteId = $Data['site_id'];

        try {
//            $date_range = explode(' - ', $Data['date_range']);
            $gradeIds = [];
            if ($Data['grades']) {
                $gradeIds = explode(',', $Data['grades']);
            }
            $siteData = Sites::with(['grades' => function ($que) use ($gradeIds, $Data,$siteId) {
	                $que = $que->with(['tanks', 'tanks.customer_transaction' => function ($q) use ($Data,$siteId) {
                    $q = $q->where('customer_transactions.site_id', $siteId);
		            $q = $q->where('customer_transactions.litres', '>', 0);
                    if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
                        $start_date = \Carbon\Carbon::parse($Data['start_date'])->format('Y-m-d H:i:s');
                        $end_date = \Carbon\Carbon::parse($Data['end_date'])->format('Y-m-d H:i:s');
	                    $q->where('end_date', '>=', $start_date)->where('end_date', '<=', $end_date);
//                        $q->whereBetween('customer_transactions.created_at', [$start_date, $end_date]);
                    }
                }]);
                if (count($gradeIds)) {
                    $que = $que->whereIn('id', $gradeIds);
                }
            }])->find($Data['site_id']);

            $transactions = collect($siteData->grades)->transform(function ($grade) {
                $gradeArray = ['grade_id' => $grade->id, 'grade_name' => $grade->name, 'trans_count' => (int)0, 'total_cost' => (float)0, 'total_litres' => (int)0];
                foreach ($grade->tanks as $tank) {
                    $gradeArray['total_litres'] += collect($tank->customer_transaction)->sum('litres');
                    $gradeArray['total_cost'] += collect($tank->customer_transaction)->sum('total_cost');
                    $gradeArray['trans_count'] += count($tank->customer_transaction);
                }
                return collect($gradeArray);
            });

	        // Added By HS not to show 0 litres data
	        $transactions = collect($transactions)->filter(function ($item){
            	return $item['total_litres'] > 0;
            });

	        return Datatables::of($transactions)->make(true);

	        $finalData = $this->paginate($transactions, 1000);
            return response($finalData);
            $returnData = array('status' => 'success', 'message' => 'Requested Dispensed grades listed successfully!', 'data' => $finalData, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Requested dispensed grades listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }
	public function array_flatten($array) {
	  if (!is_array($array)) {
	    return false;
	  }
	  $result = array();
	  foreach ($array as $key => $value) {
	    if (is_array($value)) {
	      $result = array_merge($result, array_flatten($value));
	    } else {
	      $result[$key] = $value;
	    }
	  }
	  return $result;
	}
    // 6) Dispensed Time
    public function getDispensedTime()
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

            $gradeIds = [];
            if ($Data['grades']) {
                $gradeIds = explode(',', $Data['grades']);
            }

            $yearMonthSet = [];
            $from=$temp=\Carbon\Carbon::parse($Data['start_date'])->format('Y-m-d');
            $to=\Carbon\Carbon::parse($Data['end_date'])->format('Y-m-d');

            $start    = new \DateTime($from);
            $end      = new \DateTime($to);
            $interval = \DateInterval::createFromDateString('1 month');
            $period   = new \DatePeriod($start, $interval, $end);
            foreach ($period as $dt) {
                $yearMonthSet[]= $dt->format("Y-m");
            }

            $fullData=[];

            $siteId = $Data['site_id'];

            foreach($yearMonthSet as $date)
            {
                $yearMonth=explode('-',$date);
                $month_name = \Carbon\Carbon::parse($date.'-01')->format('F');
                $fullData[$yearMonth[0]][$month_name]=[];

                $siteData = Sites::with(['grades' => function ($que) use ($gradeIds, $Data,$yearMonth, $siteId) {
                    $que = $que->with(['tanks', 'tanks.customer_transaction' => function ($q) use ($Data,$yearMonth, $siteId) {
                        $q = $q->where('customer_transactions.site_id', $siteId);
	                    $q = $q->where('customer_transactions.litres', '>', 0);
                        if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
                            $date_range[0] = \Carbon\Carbon::parse($Data['start_date'])->format('Y-m-d H:i:s');
                            $date_range[1] = \Carbon\Carbon::parse($Data['end_date'])->format('Y-m-d H:i:s');
                            $q->whereYear('customer_transactions.end_date','=',(int)$yearMonth[0])
                                ->whereMonth('customer_transactions.end_date','=',(int)$yearMonth[1]);
                        }
                    }]);
                    if (count($gradeIds)) {
                        $que = $que->whereIn('id', $gradeIds);
                    }
                }])->find($Data['site_id']);

                $transactions = collect($siteData->grades)->transform(function ($grade) {
                    $gradeArray = ['grade_id' => $grade->id, 'grade_name' => $grade->name, 'trans_count' => (int)0, 'total_cost' => (float)0, 'total_litres' => (int)0];
                    foreach ($grade->tanks as $tank) {
                        $gradeArray['total_litres'] += collect($tank->customer_transaction)->sum('litres');
                        $gradeArray['total_cost'] += collect($tank->customer_transaction)->sum('total_cost');
                        $gradeArray['trans_count'] += count($tank->customer_transaction);
                    }
                    return $gradeArray;
                });

                // Added By HS not to show 0 litres data
	            $transactions = collect($transactions)->filter(function ($item){
                    return $item['total_litres'] > 0;
                });

                $fullData[$yearMonth[0]][$month_name]=$transactions;
            }
            $returnData = array('status' => 'success', 'message' => 'Requested Dispensed Time listed successfully!', 'data' => $fullData, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Requested Dispensed Time listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    // 7) Dispensed Hose
    public function getDispensedHoses()
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

            $gradeIds = [];
            $hoseIds = [];
            $tankIds = [];
            if ($Data['grades']) {
                $gradeIds = explode(',', $Data['grades']);
            }
            if ($Data['hoses']) {
                $hoseIds = explode(',', $Data['hoses']);
            }
            $defaultSiteHoses = Sites::with(['hoses' => function ($qHoses) use ($hoseIds) {
                if (count($hoseIds)) {
                    $qHoses = $qHoses->whereIn('id', $hoseIds);
                }
            }, 'hoses.tank'])->find($Data['site_id']);

            $hoseArray = [];
            foreach ($defaultSiteHoses->hoses as $hose) {
                $hoseArray[$hose->id] = ['id' => $hose->id, 'hose_name' => $hose->name, 'tank_name' => !empty($hose->tank) ? $hose->tank->name : 'N/A', 'trans_count' => (int)0, 'total_cost' => (float)0, 'total_litres' => (int)0];
            }

            if ($Data['tanks']) {
                $tankIds = explode(',', $Data['tanks']);
            }

            $siteId = $Data['site_id'];

            // Grades
            $siteData = Sites::with(['grades' => function ($query) use ($tankIds, $hoseIds, $gradeIds, $Data, $siteId) {
                if (count($gradeIds)) {
                    $query = $query->whereIn('id', $gradeIds);
                }
                // Tanks
                $query = $query->with(['tanks' => function ($quer) use ($tankIds, $hoseIds, $Data, $siteId) {
                    if (count($tankIds)) {
                        $quer = $quer->whereIn('id', $tankIds);
                    }
                    $quer = $quer->with(['grade_hoses' => function ($que) use ($hoseIds, $Data, $siteId) {
                        if (count($hoseIds)) {
                            $que = $que->whereIn('id', $hoseIds);
                        }
                        $que = $que->with(['customer_transactions' => function ($qu) use ($Data, $hoseIds, $siteId) {
                            $qu = $qu->where('customer_transactions.site_id', $siteId);
                            if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
                                $start_date = \Carbon\Carbon::parse($Data['start_date'])->format('Y-m-d H:i:s');
                                $end_date = \Carbon\Carbon::parse($Data['end_date'])->format('Y-m-d H:i:s');
	                            $qu->where('end_date', '>=', $start_date)->where('end_date', '<=', $end_date);
//                                $qu->whereBetween('customer_transactions.created_at', [$start_date, $end_date]);
                            }
                        }]);
                    }]);
                }]);
            }])->find($Data['site_id']);

            $finalArray = [];

            foreach ($siteData->grades as $grade) {
                foreach ($grade->tanks as $tank) {
                    foreach ($tank->grade_hoses as $hose) {
                        if (isset($hoseArray[$hose->id])) {
                            $hoseArray[$hose->id]['total_litres'] += collect($hose->customer_transactions)->sum('litres');
                            $hoseArray[$hose->id]['total_cost'] += collect($hose->customer_transactions)->sum('total_cost');
                            $hoseArray[$hose->id]['trans_count'] += count($hose->customer_transactions);
	                        // Added By HS not to show 0 litres data
                            if($hoseArray[$hose->id]['total_litres'] > 0){
	                            $finalArray[] = $hoseArray[$hose->id];
                            }
                        }
                    }
                }
            }
	        return Datatables::of(collect($finalArray))->make(true);

//            $returnData = array('status' => 'success', 'data' => $finalArray, 'message' => 'Requested Dispensed hoses listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Requested Dispensed hoses listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    // 8) Dispensed Tank
    public function getDispensedTanks()
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

            $gradeIds = [];
            $hoseIds = [];
            $tankIds = [];
            if ($Data['grades']) {
                $gradeIds = explode(',', $Data['grades']);
            }
            if ($Data['hoses']) {
                $hoseIds = explode(',', $Data['hoses']);
            }
            if ($Data['tanks']) {
                $tankIds = explode(',', $Data['tanks']);
            }

            $defaultSiteTanks = Sites::with(['tanks' => function ($qTanks) use ($tankIds) {
                if (count($tankIds)) {
                    $qTanks = $qTanks->whereIn('id', $tankIds);
                }
            }, 'tanks.grades'])->find($Data['site_id']);

            $tankArray = [];
            foreach ($defaultSiteTanks->tanks as $tank) {
                $tankArray[$tank->id] = ['id' => $tank->id, 'tank_name' => $tank->name, 'grade_name' => !empty($tank->grades) ? $tank->grades->name : 'N/A', 'trans_count' => (int)0, 'total_cost' => (float)0, 'total_litres' => (int)0];
            }

            $siteId = $Data['site_id'];

            // Grades
            $siteData = Sites::with(['grades' => function ($query) use ($tankIds, $hoseIds, $gradeIds, $Data, $siteId) {
                if (count($gradeIds)) {
                    $query = $query->whereIn('id', $gradeIds);
                }
                // Tanks
                $query = $query->with(['tanks' => function ($quer) use ($tankIds, $hoseIds, $Data, $siteId) {
                    if (count($tankIds)) {
                        $quer = $quer->whereIn('id', $tankIds);
                    }
                    $quer = $quer->with(['grade_hoses' => function ($que) use ($hoseIds, $Data, $siteId) {
                        if (count($hoseIds)) {
                            $que = $que->whereIn('id', $hoseIds);
                        }
                        $que = $que->with(['customer_transactions' => function ($qu) use ($Data, $hoseIds, $siteId) {
                            $qu = $qu->where('customer_transactions.site_id', $siteId);
	                        $qu = $qu->where('customer_transactions.litres', '>', 0);
                            if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
                                $start_date = \Carbon\Carbon::parse($Data['start_date'])->format('Y-m-d H:i:s');
                                $end_date = \Carbon\Carbon::parse($Data['end_date'])->format('Y-m-d H:i:s');
	                            $qu->where('end_date', '>=', $start_date)->where('end_date', '<=', $end_date);
//                                $qu->whereBetween('customer_transactions.created_at', [$start_date, $end_date]);
                            }
                        }]);
                    }]);
                }]);
            }])->find($Data['site_id']);

            $finalArray = [];

            foreach ($siteData->grades as $grade) {
                foreach ($grade->tanks as $tank) {
                    foreach ($tank->grade_hoses as $hose) {
                        if (isset($tankArray[$tank->id])) {
                            $tankArray[$tank->id]['total_litres'] += collect($hose->customer_transactions)->sum('litres');
                            $tankArray[$tank->id]['total_cost'] += collect($hose->customer_transactions)->sum('total_cost');
                            $tankArray[$tank->id]['trans_count'] += count($hose->customer_transactions);
                            //$finalArray[] = $tankArray[$tank->id];
	                        // Added By HS not to show 0 litres data
                            if($tankArray[$tank->id]['total_litres'] > 0){
                                $finalArray[] = $tankArray[$tank->id];
                            }
                        }
                    }
                }
            }
	        return Datatables::of(collect($finalArray))->make(true);

//            $returnData = array('status' => 'success', 'data' => $finalArray, 'message' => 'Requested Dispensed tanks listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
        	$returnData = array('status' => 'failure', 'message' => 'Requested Dispensed tanks listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    // 9) Dispensed Attendant
    public function getDispensedAttendants()
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
            $attendantIds = [];
            $gradeIds = [];
            if ($Data['attendants']) {
                $attendantIds = explode(',', $Data['attendants']);
            }
            if ($Data['grades']) {
                $gradeIds = explode(',', $Data['grades']);
            }

            $siteId = $Data['site_id'];

            $site = Sites::with(['attendants' => function ($quer) use ($attendantIds, $gradeIds, $Data, $siteId) {
                if (count($attendantIds)) {
                    $quer = $quer->whereIn('attendant_id', $attendantIds);
                }
                $quer = $quer->with(['transactions' => function ($que) use ($attendantIds, $gradeIds, $Data, $siteId) {
                    $que = $que->where('customer_transactions.site_id', $siteId);
	                $que = $que->where('customer_transactions.litres', '>', 0);
                    $que = $que->with(['hose' => function ($qu) use ($gradeIds) {
                        $qu = $qu->with(['tank' => function ($q) use ($gradeIds) {
                            $q = $q->with(['grades' => function ($qr) use ($gradeIds) {
                                $qr = $qr->whereIn('id', $gradeIds);
                            }]);
                        }]);
                    }]);
                    if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//                        $date_range[0] = $date_range[0] . " 00:00:00";
                        $que = $que->where('customer_transactions.end_date', '>=', $Data['start_date'])
	                        ->where('customer_transactions.end_date', '<=', $Data['end_date']);
                    }
                }]);
            }])->find($Data['site_id']);

            $attendants = $site->attendants;

            $attendantData = [];
            foreach ($attendants as $key => $attendant) {
                $attendantData[$key] = ['attendant_id' => $attendant->id, 'attendant_name' => $attendant->name . $attendant->surname, 'trans_count' => (int)0, 'total_cost' => (float)0, 'total_litres' => (int)0];
                $attendantData[$key]['total_litres'] += collect($attendant->transactions)->sum('litres');
                $attendantData[$key]['total_cost'] += collect($attendant->transactions)->sum('total_cost');
                $attendantData[$key]['trans_count'] += count($attendant->transactions);
            }

	        // Added By HS not to show 0 litres data
	        $attendantData = collect($attendantData)->filter(function ($item){
                return $item['total_litres'] > 0;
            });

	        return Datatables::of(collect($attendantData))->make(true);

//            $returnData = array('status' => 'success', 'data' => $attendantData, 'message' => 'Requested Dispensed attendants listed successfully!','code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Requested Dispensed attendants listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

	public function getAtgData()
	{

		$Data = $this->request->all();
		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric'
		]);
		if ($validator->fails()) {
			$returnData = array ('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
			return response()->json($returnData, 400);
		}
		try {
//			$date_range = explode(' - ', $Data['date_range']);

			/* New */
//			$tankIds = Tanks::where('site_id', $Data['site_id'])->get()->pluck('atg_id')->toArray();
//			$query = AtgTransaction::select('*');
//			if (count($date_range) == 2) {
//				$date_range[0] = $date_range[0] . " 00:00:00";
//				$query = $query->where('date', '>=', $date_range[0]);
//			}
//			if (count($date_range) == 2 ) {
//				$date_range[1] = $date_range[1] . " 00:00:00";
//				$query = $query->where('date', '<=', $date_range[1]);
//			}
//			$transactions = $query->whereHas('AtgData',function($q1) use($tankIds){
//				$q1->whereIn('id', $tankIds);
//			})->with([
//				'AtgData' => function ($q1) use ($tankIds) {
//					$q1->whereIn('id', $tankIds)->with([
//						'tank' => function ($q1) {
//							$q1->select('id', 'name');
//						}
//					]);
//				}
//			])->get();
//
//			$finalData=[];
//			foreach ($transactions as $key=>$tran) {
////				dd($tran->AtgData);
//				$finalData[$key] = [];
//				$finalData[$key]['atg_name'] = ($tran->AtgData) ? $tran->AtgData->name : '';
//				$finalData[$key]['tank_name'] = ($tran->AtgData && $tran->AtgData->tank) ? $tran->AtgData->tank->name : '';
//				$finalData[$key]['date'] = $tran->date;
//				$finalData[$key]['time'] = $tran->time;
//				$finalData[$key]['cm'] = $tran->cm;
//				$finalData[$key]['liters'] = $tran->liters;
//			}
			/* Old */
			ini_set('memory_limit', '-1');
			$site = Sites::with([
				'tanks' => function ($q) use ($Data) {
					if($Data['tank_ids']){
						$q = $q->whereIn('id', explode(',', $Data['tank_ids']));
					}
					$q->has('atgData')->with(['atgData.atgTransaction'=>function($q2) use ($Data){
						$q2 = $q2->select('id', 'atg_id', 'date', 'time', 'cm', 'liters')->where('liters', '>', 0);
						if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
							$start_date = date('Y-m-d', strtotime($Data['start_date']));
							$end_date = date('Y-m-d', strtotime($Data['end_date']));
							$start_time = date('H:i:s', strtotime($Data['start_date']));
							$end_time = date('H:i:s', strtotime($Data['end_date']));

							$start_date_time = date('Y-m-d H:i:s', strtotime($Data['start_date']));
							$end_date_time = date('Y-m-d H:i:s', strtotime($Data['end_date']));
							//New Query
							$q2 = $q2->where(\DB::raw('concat(date," ",time)'), '>=', $start_date_time)
								->where(\DB::raw('concat(date," ",time)'), '<=', $end_date_time);
						// Old Query
//							$q2 = $q2->where('date', '>=', $start_date)->where('date', '<=', $end_date)
//								->where('time', '>=', $start_time)->where('time', '<=', $end_time);
						}
					}]);
				}
			])->find($Data['site_id']);
			$finalData=[];
			foreach ($site->tanks as $tank) {
				foreach ($tank->atgData->atgTransaction as $t) {
					$t->atg_name = $tank->atgData->name;
					$t->tank_name = $tank->name;
					$finalData[] = $t;
				}
			}
			return Datatables::of(collect($finalData))->make(true);
//			$finalData = $this->paginate($finalData, 1000);
//			return response($finalData);
			$returnData = array('status' => 'success', 'data' => $finalData, 'message' => 'Requested ATG listed successfully!','code' => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status' => 'failure', 'message' => 'Requested ATG listed not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	public function paginate($items, $perPage = 15, $page = null, $options = [])
	{
		$page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
		$items = $items instanceof Collection ? $items : Collection::make($items);
		return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
	}
	public function getAtgDataHourly()
	{
			$Data = $this->request->all();
			$validator = \Validator::make($Data, [
				'site_id' => 'required|numeric'
			]);
			if ($validator->fails()) {
				$returnData = array ('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
				return response()->json($returnData, 400);
			}
		try {
//			$date_range = explode(' - ', $Data['date_range']);
			$whereCondition  = 'atg_transactions.atg_id = atg_data.id and atg_data.id = tanks.atg_id ';

			if($Data['tank_ids']){
				$whereCondition  .= 'and tanks.id in ('.$Data["tank_ids"].')';
			}
			if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
				$start_date = date('Y-m-d', strtotime($Data['start_date']));
				$end_date = date('Y-m-d', strtotime($Data['end_date']));
				$start_time = date('H:i:s', strtotime($Data['start_date']));
				$end_time = date('H:i:s', strtotime($Data['end_date']));

				$whereCondition  .= ' and concat(atg_transactions.date," ",atg_transactions.time) between "'.$Data['start_date'].'" AND "' .$Data['end_date'].'"';
			}

			$Data = \DB::select('Select atg_data.name, atg_transactions.date,tanks.name AS tank_name, 
					concat(TIME_FORMAT(atg_transactions.time, \'%H\') ,\':00\') time, round(avg(atg_transactions.cm),2) cm, 
					round(avg(atg_transactions.liters),2) liters

					from atg_transactions,tanks,atg_data 
					where '.$whereCondition.' and tanks.site_id = '.$Data["site_id"].' and atg_transactions.liters > 0 
					group by atg_data.name, atg_transactions.date,hour( time )');

			return Datatables::of(collect($Data))->make(true);

//			$finalData = $this->paginate($Data, 1000);
//			return response($finalData);
//			$returnData = array ( 'status' => 'success', 'data'   => $Data,'message' => 'Requested ATG Hourly listed successfully!', 'code'   => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status' => 'failure', 'message' => 'Requested ATG Hourly listed not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}

	public function dailyTankRecon()
	{
		
		$Data = $this->request->all();
		$validator = \Validator::make($Data, [
			'site_id' => 'required|numeric'
		]);
		if ($validator->fails()) {
			$returnData = ['status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400];
			return response()->json($returnData, 400);
		}
		try {
			$Data = \DB::select('call TankRecon('.$Data['tanks'].', "'.$Data['date'].'")');
			$returnData = array ( 'status' => 'success', 'data'   => $Data, 'code'   => 200);
		}
		catch (\Exception $e) {
			$returnData = ['status'  => 'failure', 'message' => 'Transactions not found', 'code' => 400];
		}
		return response()->json($returnData, $returnData['code']);
	}
}
