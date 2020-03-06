<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\JwtAuthController;
use App\Models\Sites;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Datatables;

class SupplierReportController extends JwtAuthController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
    }

    /* Suppliers -> Supplier List
     * Getting Required inputs
     */
    public function suppliers()
    {
        try {
            $Data = auth()->user()->sites()->with('suppliers')->get();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Suppliers not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

	public function suppliersPurchases()
	{
		try {
			$Data = auth()->user()->sites()->with('suppliers', 'grades')->get();
			$returnData = array ( 'status' => 'success', 'data'   => $Data,'message' => 'Requested suppliers listed successfully!', 'code'   => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status'  => 'failure', 'message' => 'Requested suppliers listed not found', 'code'    => 400);
		}
		return response()->json($returnData, $returnData['code']);
    }

    /* Suppliers -> Supplier List
     * Getting Results based on the inputs
     */
    public function suppliersListReport()
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
            $supplierIds = [];
	        $gradeIds = [];
            if ($Data['suppliers']) {
                $supplierIds = explode(',', $Data['suppliers']);
            }
	        if ($Data['grades']) {
		        $gradeIds = explode(',', $Data['grades']);
	        }
	        $site = Sites::with([
		        'suppliers' => function ($query) use ($supplierIds, $gradeIds, $Data) {
                if (count($supplierIds)) {
                    $query = $query->whereIn('id', $supplierIds);
                }
                //$query = $query->where('status', 'Active');
                $query = $query->orderBy('id');
			        $query = $query->with([
				        'fueldrops' => function ($que) use ($Data, $gradeIds) {
                    if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//                        $date_range[0] = $date_range[0] . " 00:00:00";
                        $que = $que->where('purchase_date', '>=', $Data['start_date'])
	                        ->where('purchase_date', '<=', $Data['end_date'])
	                        ->where('litres','>',0);
                    }
			        if (count($gradeIds)) {
				        $que = $que->whereIn('fuel_drops.grade_id', $gradeIds);
			        }
                    $que = $que->with(['tank' => function ($qu) {
                        $qu = $qu->selectRaw("id, name");
                    }, 'grade' => function ($qu) {
                        $qu = $qu->selectRaw("id, name");
                    }
                    ]);
                }]);
            }])->find($Data['site_id']);
            $suppliers = $site->suppliers;
            return Datatables::of($suppliers)->make(true);

//          $returnData = array('status' => 'success', 'message' => 'Requested suppliers listed successfully!', 'data' => $suppliers, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Requested suppliers listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }
}