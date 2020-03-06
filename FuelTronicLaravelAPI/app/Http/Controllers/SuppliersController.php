<?php

namespace App\Http\Controllers;

use App\Interfaces\SupplierRepositoryInterface;
use App\Models\Suppliers;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class SuppliersController extends JwtAuthController
{
    public function __construct(Request $request, SupplierRepositoryInterface $supplierRepo)
    {
        parent::__construct();
        $this->supplier = $supplierRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data['sites'] = auth()->user()->sites()->select('id', 'name')->get();
            $Data['suppliers'] = $this->supplier->getAll();
            foreach ($Data['suppliers'] as $key => $value) {
                if (count($value->sites) > 0) {
                    $Data['suppliers'][$key]->supplierSites = $value->sites->lists('id');
                } else {
                    if ($this->user->role == '2')
                        unset($Data['suppliers'][$key]);
                    else
                        $Data['suppliers'][$key]->supplierSites = [];
                }
            }
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Suppliers not found', 'code' => 400);
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
            'email_address' => 'required|email|unique:suppliers',
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $site_id = $Data['site_id'];unset($Data['site_id']);
            $Data['name'] = $Data['first_name'].' '.$Data['last_name'];
            $supplier = $this->supplier->store($Data);
            $supplierData = Suppliers::find($supplier->id);
            $supplierData->sites()->attach($site_id);
            $returnData = array('status' => 'success', 'message' => 'Supplier saved successfully!', 'code' => 200);
        } catch
        (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Supplier not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $supplier = $this->supplier->show($id);
            $returnData = array('status' => 'success', 'data' => $supplier, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Supplier not found', 'code' => 400);
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
            'email_address' => 'required|email|unique:suppliers,email_address,'.$id
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $Data['name'] = $Data['first_name'].' '.$Data['last_name'];
            $this->supplier->update($id, $Data);
            $returnData = array('status' => 'success', 'message' => 'Supplier updated successfully!', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Supplier not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->supplier->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Supplier deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Supplier not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function getSitesOfSupplier($supplierId)
    {
        try {
            $sites = auth()->user()->sites()->get();
            $supplier = Suppliers::find($supplierId);
	        if(!$supplier){
	            return response()->json(['status' => 'failure', 'message' => 'Supplier not found', 'code' => 400],400);
	         }
            $supplierSites = $supplier->sites()->lists('id');
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'supplierSites' => $supplierSites), 'message' => 'Selected sites of supplier listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Supplier sites not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function updateSitesOfSupplier()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'supplier_id' => 'required',
            //'site_ids' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $updateData = explode(',', $Data['site_ids']);
            $sites = auth()->user()->sites()->get();
            $supplier = Suppliers::find($Data['supplier_id']);
	        if(!$supplier){
	            return response()->json(['status' => 'failure', 'message' => 'Supplier not found', 'code' => 400],400);
	         }
            $supplierSites = $supplier->sites()->sync($updateData);
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'supplierSites' => $supplierSites), 'message' => 'Supplier sites updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Supplier sites not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }
}
