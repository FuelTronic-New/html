<?php

namespace App\Http\Controllers;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customers;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class CustomersController extends JwtAuthController
{
    public function __construct(Request $request, CustomerRepositoryInterface $customerRepo)
    {
        parent::__construct();
        $this->customer = $customerRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data['sites'] = auth()->user()->sites()->select('id', 'name')->get();
            $Data['customers'] = $this->customer->getAll();
            foreach ($Data['customers'] as $key => $value) {
                if (count($value->sites->lists('id')) > 0) {
                    $Data['customers'][$key]->customerSites = $value->sites->lists('id');
                } else {
                    if ($this->user->role == '2')
                        unset($Data['customers'][$key]);
                    else
                        $Data['customers'][$key]->customerSites = [];
                }
            }
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customers not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'accountNumber' => 'required|max:255',
            'status' => 'required',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email_address' => 'required|email|unique:customers',
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $site_id = $Data['site_id'];unset($Data['site_id']);
            $Data['name'] = $Data['first_name'].' '.$Data['last_name'];
            $customer = $this->customer->store($Data);
            $customerData = Customers::find($customer->id);
            $customerData->sites()->attach($site_id);
            $returnData = array('status' => 'success', 'message' => 'Customer saved successfully!', 'code' => 200);
        } catch
        (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $customer = $this->customer->show($id);
            $returnData = array('status' => 'success', 'data' => $customer, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'accountNumber' => 'required|max:255',
            'status' => 'required',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email_address' => 'required|email|unique:customers,email_address,'.$id,
//            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $Data['name'] = $Data['first_name'].' '.$Data['last_name'];
            $this->customer->update($id, $Data);
            $returnData = array('status' => 'success', 'message' => 'Customer updated successfully!', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->customer->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Customer deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function getSitesOfCustomer($customerId)
    {
        try {
            $sites = auth()->user()->sites()->get();
            $customer = Customers::find($customerId);
            $customerSites = $customer->sites()->lists('id');
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'customerSites' => $customerSites), 'message' => 'Selected sites of customer listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function updateSitesOfCustomer()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'customer_id' => 'required',
            //'site_ids' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $updateData = explode(',', $Data['site_ids']);
            $sites = auth()->user()->sites()->get();
            $customer = Customers::find($Data['customer_id']);
            $customerSites = $customer->sites()->sync($updateData);
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'customerSites' => $customerSites), 'message' => 'Customer sites updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Customer site not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

}
