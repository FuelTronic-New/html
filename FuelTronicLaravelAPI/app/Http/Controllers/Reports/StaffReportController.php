<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\JwtAuthController;
use App\Models\Sites;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Datatables;

class StaffReportController extends JwtAuthController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
    }

    /* Staff -> Attendant List
     * Getting Required inputs
     */
    public function attendants()
    {
        try {
            $Data = auth()->user()->sites()->with('attendants')->get();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Attendants not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    /* Staff -> Attendant List
     * Getting Results based on the inputs
     */
    public function attendantsListReport()
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
            if ($Data['attendants']) {
                $attendantIds = explode(',', $Data['attendants']);
            }

            $site = Sites::with(['attendants' => function ($query) use ($attendantIds, $Data) {
                if (count($attendantIds)) {
                    $query = $query->whereIn('id', $attendantIds);
                }
                if (!empty($Data['start_date']) &&  !empty($Data['end_date'])) {
//                    $date_range[0] = $date_range[0] . " 00:00:00";
                    $query = $query->where('attendants.created_at', '>=', $Data['start_date'])->where('attendants.created_at', '<=', $Data['end_date']);
                }
                //$query = $query->where('status', 'Active');
                $query = $query->orderBy('id');
                $query = $query->with('tag');
            }])->find($Data['site_id']);
            $attendants = $site->attendants;
            return Datatables::of($attendants)->make(true);

//            $returnData = array('status' => 'success', 'message' => 'Requested attendants listed successfully!', 'data' => $attendants, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Requested attendants listed not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }
}
