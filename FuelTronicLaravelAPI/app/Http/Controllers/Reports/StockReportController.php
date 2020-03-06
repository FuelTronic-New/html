<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\JwtAuthController;
use App\Models\Sites;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Datatables;

class StockReportController extends JwtAuthController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
    }

    /* Suppliers -> Supplier List
     * Getting Required inputs
     */
	public function sites()
	{
		try {
			$Data = auth()->user()->sites;
			$returnData = array ( 'status' => 'success', 'data'   => $Data, 'code'   => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status' => 'failure', 'message' => 'Sites not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	public function sitePumps()
	{
		try {
			$Data = auth()->user()->sites()->with(['pumps'=>function($q1){
				$q1->select('id', 'name', 'site_id');
			},'grades'=>function($q1){
				$q1->select('id', 'name', 'site_id');
			}])->find($this->request->site_id);
			$returnData = array ( 'status' => 'success', 'data'   => $Data, 'code'   => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status' => 'failure', 'message' => 'Pumps not found', 'code' => 400);
		}
		return response()->json($returnData, $returnData['code']);
	}

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

	public function stockAdjustmentReport()
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
			$Data = auth()->user()->sites()->with(['fuel_adjustments' => function($q1) {
				$q1->with('tank')->where('litres', '>', 0);// Added By HS not to show 0 litres data
			}])->find($Data['site_id']);
			$retData = !empty($Data['fuel_adjustments']) ? $Data['fuel_adjustments'] : array();
			return Datatables::of(collect($retData))->make(true);

			$returnData = array ( 'status' => 'success', 'message' => 'Requested fuel adjustment listed successfully!', 'data'   => $Data,
			                      'code'   => 200);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status'  => 'failure', 'message' => 'Requested fuel adjustment listed not found', 'code'=> 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
	public function stockLevelReport()
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
			$finalData = [];$pumpIds = [];$gradeIds = [];
//			if ($Data['pump_ids']) {
//				$pumpIds = explode(',', $Data['pump_ids']);
//			}
			if ($Data['grade_ids']) {
				$gradeIds = explode(',', $Data['grade_ids']);
			}
			$site = auth()->user()->sites()->with(['tanks'=>function($q1) use($gradeIds){
				$q1->with(['grades' => function($gq) use($gradeIds){
					$gq->select('id', 'name', 'price');
					if($gradeIds){
						$gq->whereIn('id', $gradeIds);
					}
				}]);
				if($gradeIds){
					$q1->whereHas('grades', function ($q2) use($gradeIds){
						$q2->whereIn('id', $gradeIds);
					});
				}
//					->whereHas('grade_hoses', function ($q1) use($pumpIds){
//					if($pumpIds) {
//						$q1->whereIn('pump_id', $pumpIds);
//					}
//				});
			}])->find($Data['site_id']);

			foreach ($site->tanks as $tank) {
				if(!isset($finalData[$tank->grades->id])){
					$finalData[$tank->grades->id] = [];
					$finalData[$tank->grades->id]['current_level_stock'] = 0;
					$finalData[$tank->grades->id]['last_dip_reading'] = 0;
					$finalData[$tank->grades->id]['cur_atg_level'] = 0;
				}
				$finalData[$tank->grades->id]['grade_name'] = $tank->grades->name;
				$finalData[$tank->grades->id]['current_level_stock'] += (float)$tank->litre;
				$finalData[$tank->grades->id]['last_dip_reading'] += (float)$tank->last_dip_reading;
				$finalData[$tank->grades->id]['cur_atg_level'] += (float)$tank->cur_atg_level;
				$finalData[$tank->grades->id]['price'] = $tank->grades->price;
			}

			return Datatables::of(collect($finalData))->make(true);

//			$returnData = array ( 'status' => 'success', 'message' => 'Requested fuel level listed successfully!', 'data' => array_values($finalData),
//			                      'code'   => 200);

		}
		catch (\Exception $e) {
			$returnData = array ( 'status'  => 'failure', 'message' => 'Requested fuel level listed not found', 'code'=> 400);
		}
		return response()->json($returnData, $returnData['code']);
	}
}
