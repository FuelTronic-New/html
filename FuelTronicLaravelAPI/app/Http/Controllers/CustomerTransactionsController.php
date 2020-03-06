<?php
namespace App\Http\Controllers;
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

use App\Interfaces\CustomerTransactionRepositoryInterface;
use App\Interfaces\TankRepositoryInterface;
use App\Models\CustomerTransaction;
use App\Models\Grades;
use App\Models\Hoses;
use App\Models\Pumps;
use App\Models\Tanks;
use App\Models\Vehicles;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;
use Datatables;
use App\Models\Sites;
use App\Models\DashboardAtg;
use App\Models\DashboardTankLevel;
use App\Models\DashboardVehicleEco;
use App\Models\DashboardVehicleFueling;
class CustomerTransactionsController extends JwtAuthController
{
    public function __construct(Request $request, CustomerTransactionRepositoryInterface $customerTransactionRepo, TankRepositoryInterface $tankRepo)
    {
        parent::__construct();
        $this->customer_transaction = $customerTransactionRepo;
        $this->tank = $tankRepo;
        $this->request = $request;
    }

    public function Fuel(){

         if ((auth()->user()->role != 1) && (auth()->user()->role != 2)) {
			 $returnData = array ( 'status' => 'failure', 'message' => 'This page only access admin ', 'code' => 400);
        return response()->json($returnData, $returnData['code']);
}
        $Data = $this->request->all();
try {
            $query = DashboardVehicleEco::with('vehicle');
                        if($Data['vehicles']!=''){
                $ids = explode(',', $Data['vehicles']);
                $query = $query->whereIn('vehicle_id', $ids);
                }
                if($Data['customers']!=''){
                $ids = explode(',', $Data['customers']);
                $query = $query->whereIn('customer_id', $ids);
            }
            if(!empty($Data['start_date'])){
        $query->whereBetween('start_date',[$Data['start_date'],$Data['end_date']]);
            }
$vehicleEco = $query->where('site_id',$Data['site_id'])->get();
if(count($vehicleEco)==0){
    // $returnData = array ( 'status' => 'failure', 'message' => 'data not found ', 'code' => 200);
      //  return response()->json($returnData, $returnData['code']);
    return Datatables::of(collect())->make(true);
    
}
           foreach ($vehicleEco as $value){
                //$value->date = date('Y-m-d', strtotime($value->start_date));
            }
            $tankReconDataGroupByDate = collect($vehicleEco)->groupBy('date');
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

         return Datatables::of(collect($graphData2[0]['line']))->make(true);
    
       //     $returnData = ['status' => 'success', 'data'=> ['graphData2' => $graphData2,'data' => $data], 'code' => 200];
        } catch (\Exception $e) {dd($e->getMessage(), $e->getLine());
            $returnData = array ( 'status' => 'failure', 'message' => 'Transactions not found', 'code' => 400);
        }
        
        //return response()->json($returnData, $returnData['code']);

/*         $Data = $this->request->all();

$site = auth()->user()->sites()->with(['customer_transaction' => function($q) use ($Data){
            $q->whereIn('customer_id',[$Data['customers']]);// Added By HS not to show 0 litres data
        }])->where('site_id','=',$Data['site_id'])->first();
        foreach ($site->customer_transaction as $customer_transaction) {
            $customer_transaction->vehicle_name = $customer_transaction->vehicle['name'];
        }
$d_code=json_encode($site,true);
$e_code=json_decode($d_code,true);
//echo '<pre>';
    return Datatables::of(collect($e_code['customer_transaction']))->make(true);
*/
//return $e_code['customer_transaction'];
//echo '</pre>';
//        return $site;
    }
    public function index()
    {
        try {
            $Data = $this->customer_transaction->getAll();
	        $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer Transactions not found' . $e->getMessage(), 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

	public function getTransactionsById($id)
	{
    	try {
			$Data = $this->customer_transaction->getTransactionsById($id);
			return Datatables::of(collect($Data->customer_transaction))->make(true);
		}
		catch (\Exception $e) {
			$returnData = array ( 'status' => 'failure', 'message' => 'Customer Transaction not found' . $e->getMessage(), 'code' => 400);
		
		}
		return response()->json($returnData, $returnData['code']);
	}
    public function getTransactionsByIdWithDate(Request $request,$id)
    {


 $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        $start_date=$request->input('start_date');
        $end_date=$request->input('end_date');
      
        try {
            $Data = $this->customer_transaction->getTransactionsByIdWithDate($id,$start_date,$end_date);
            return Datatables::of(collect($Data->customer_transaction))->make(true);
        }
        catch (\Exception $e) {
            $returnData = array ( 'status' => 'failure', 'message' => 'Customer Transaction not found' . $e->getMessage(), 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'hose_id' => 'required|numeric',
            'litres' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $hoseDetail = Hoses::select('tank_id', 'site_id')->where('id', $Data['hose_id'])->first();
            if(!$hoseDetail){
	            return response()->json(['status' => 'failure', 'message' => 'Hose not found', 'code' => 400], 400);
            }
            $Data['site_id'] = $hoseDetail->site_id;
            $tank_id = $hoseDetail->tank_id;// Tank id to update litres
            $tankDetail = $this->tank->show($tank_id);
	        if(!$tankDetail){
	          return response()->json(['status' => 'failure', 'message' => 'Tank not found', 'code' => 400], 400);
	         }
            $litresDiff = $tankDetail->litres - $Data['litres'];
            $Data['customer_id'] = Vehicles::where('id', $Data['vehicle_id'])->pluck('customer_id');
            $this->customer_transaction->store($Data);
            // Update amount to tanks
            $updateTankLitres = $this->tank->updateFuel($tank_id, $litresDiff);
            $returnData = array('status' => 'success', 'message' => 'Customer Transaction saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer Transaction not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $fuelDrop = $this->customer_transaction->show($id);
	        if(!$fuelDrop){
	          return response()->json(['status' => 'failure', 'message' => 'Customer Transaction not found', 'code' => 400], 400);
	         }
           $fuelDrop->attendant_name = !empty($fuelDrop->attendant->name) ? $fuelDrop->attendant->name : '';;
           $fuelDrop->pump_name = !empty($fuelDrop->pump->name) ? $fuelDrop->pump->name : '';
           $fuelDrop->grade_name = !empty($fuelDrop->hose->tank->grades->name) ? $fuelDrop->hose->tank->grades->name : '';
           unset($fuelDrop->{'attendant'},$fuelDrop->{'pump'},$fuelDrop->{'hose'}); // remove unnecessary data to send
           $returnData = array('status' => 'success', 'data' => $fuelDrop, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer Transaction not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'hose_id' => 'required|numeric',
            'litres' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $hoseDetail = Hoses::select('tank_id', 'site_id')->where('id', $Data['hose_id'])->first();
	        if(!$hoseDetail){
	          return response()->json(['status' => 'failure', 'message' => 'Hose not found', 'code' => 400], 400);
	         }
            $Data['site_id'] = $hoseDetail->site_id;
            $tank_id = $hoseDetail->tank_id;// Tank id to update litres
            $oldTransaction = $this->customer_transaction->show($id);
            $tankDetail = $this->tank->show($tank_id);
	        if(!$tankDetail){
	          return response()->json(['status' => 'failure', 'message' => 'Tank not found', 'code' => 400], 400);
	         }
            $litresDiff = $tankDetail->litres - ($Data['litres'] - $oldTransaction->litres);
            $Data['customer_id'] = Vehicles::where('id', $Data['vehicle_id'])->pluck('customer_id');
            $this->customer_transaction->update($id, $Data);
            // Update amount to tanks
            $updateTankLitres = $this->tank->updateFuel($tank_id, $litresDiff);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Customer Transaction updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer Transaction not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
        	$ct = CustomerTransaction::with('hose.tank')->findOrFail($id);
        	if(isset($ct->hose) && isset($ct->hose->tank)) {
		        $ct->hose->tank->update([
			        'litre' => ($ct->hose->tank->litre + $ct->litres)
		        ]);
	        }
            $this->customer_transaction->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Customer Transaction deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer Transaction not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create($siteID)
    {
        $sites = auth()->user()->load(['sites' => function($query) use ($siteID){
            return $query->whereId($siteID);
        }, 'sites.pumps','sites.pumps.hoses','sites.pumps.hoses.tank', 'sites.attendants', 'sites.vehicles' => function ($q) {
        			    return $q->whereHas('customer',function ($q1){
        			    	$q1->where('status','active');
			            })->where('status', 'Active');
        		    }, 'sites.jobs', 'sites.locations']);
        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function edit($id)
    {
        $customerTransaction = CustomerTransaction::find($id);
	    if(!$customerTransaction){
		  return response()->json(['status' => 'failure', 'message' => 'Customer Transaction not found', 'code' => 400], 400);
		 }
        $siteID = $customerTransaction->site_id;
        $sites = auth()->user()->load(['sites' => function($query) use ($siteID){
            return $query->whereId($siteID);
        }, 'sites.pumps','sites.pumps.hoses','sites.pumps.hoses.tank', 'sites.attendants', 'sites.vehicles' => function ($q) {
        			    return $q->whereHas('customer',function ($q1){
                            $q1->where('status','active');
                        })->where('status', 'Active');
        		    }, 'sites.jobs', 'sites.locations']);
        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function getDetailsFromHoseID($hoseId)
    {
        $hoseDetail = Hoses::find($hoseId);
	    if(!$hoseDetail){
	      return response()->json(['status' => 'failure', 'message' => 'Hose not found', 'code' => 400], 400);
	     }
        $pumpDetail = Pumps::find($hoseDetail->pump_id);
	    if(!$pumpDetail){
	      return response()->json(['status' => 'failure', 'message' => 'Pump not found', 'code' => 400], 400);
	     }
        $tankDetail = Tanks::find($hoseDetail->tank_id);
	    if(!$tankDetail){
	      return response()->json(['status' => 'failure', 'message' => 'Tank not found', 'code' => 400], 400);
	     }
        $gradeDetail = Grades::find($tankDetail->grade_id);
        $finalDetails = array(
            'location_show' => $pumpDetail->location,
            'attendant_show' => $pumpDetail->attendent_tag,
            'vehicle_show' => $pumpDetail->vehicle_tag,
            'job_show' => $pumpDetail->job_tag,
            'pin' => $pumpDetail->pin,
            'odo_meter' => $pumpDetail->odo_meter,
            'grade_price' => $gradeDetail->price,
            'grade_vat' => $gradeDetail->vat_rate
        );
        $returnData = array('status' => 'success', 'data' => $finalDetails, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

}
