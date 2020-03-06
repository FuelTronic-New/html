<?php

namespace App\Http\Controllers;

use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Payment;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class PaymentsController extends JwtAuthController
{
    public function __construct(Request $request, PaymentRepositoryInterface $paymentRepo)
    {
        parent::__construct();
        $this->payment = $paymentRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->payment->getAll();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Payments not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $Data['created_by'] = auth()->user()->id;
        if ($Data['customer_id'] == '' || $Data['customer_id'] == '0') {
            $validator = \Validator::make($Data, [
                'supplier_id' => 'required|numeric',
                'site_id' => 'required|numeric',
                'amount' => 'required|max:255'
            ]);
        }
        if ($Data['supplier_id'] == '' || $Data['supplier_id'] == '0') {
            $validator = \Validator::make($Data, [
                'customer_id' => 'required|numeric',
                'site_id' => 'required|numeric',
                'amount' => 'required|max:255'
            ]);
        }

        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->payment->store($Data);
            $returnData = array('status' => 'success', 'message' => 'Payment saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Payment not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $tank = $this->payment->show($id);
            $returnData = array('status' => 'success', 'data' => $tank, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Payment not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $Data['created_by'] = auth()->user()->id;
        if ($Data['customer_id'] == '' || $Data['customer_id'] == '0') {
            $validator = \Validator::make($Data, [
                'supplier_id' => 'required|numeric',
                'site_id' => 'required|numeric',
                'amount' => 'required|max:255'
            ]);
        }
        if ($Data['supplier_id'] == '' || $Data['supplier_id'] == '0') {
            $validator = \Validator::make($Data, [
                'customer_id' => 'required|numeric',
                'site_id' => 'required|numeric',
                'amount' => 'required|max:255'
            ]);
        }
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->payment->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Payment updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Payment not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->payment->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Payment deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Payment not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function create($siteId)
    {
        $sites = auth()->user()->load(['sites' => function ($query) use ($siteId) {
            return $query->whereId($siteId);
        }, 'sites.customers', 'sites.suppliers']);
        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

    public function edit($id)
    {
	    $payment = Payment::find($id);
	    if(!$payment) {
		    return response()->json(['status'  => 'failure', 'message' => 'Payment not found', 'code' => 400],400);
	    }
        $saved_site = $payment->site_id;
        $sites = auth()->user()->load(['sites' => function ($query) use ($saved_site) {
            return $query->whereId($saved_site);
        }, 'sites.customers', 'sites.suppliers']);
        $returnData = array('status' => 'success', 'data' => $sites, 'code' => 200);
        return response()->json($returnData, $returnData['code']);
    }

}
