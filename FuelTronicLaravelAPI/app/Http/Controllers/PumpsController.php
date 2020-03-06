<?php

namespace App\Http\Controllers;

use App\Interfaces\PumpRepositoryInterface;
use App\Models\Pumps;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class PumpsController extends JwtAuthController
{
    public function __construct(Request $request, PumpRepositoryInterface $pumpRepo)
    {
        parent::__construct();
        $this->pump = $pumpRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->pump->getAll();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pumps not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'code' => 'required|max:255',
            'guid' => 'required|max:255',
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $pump = $this->pump->store($Data);
            $returnData = array('status' => 'success', 'message' => 'Pump saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $pump = $this->pump->show($id);
            $returnData = array('status' => 'success', 'data' => $pump, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'code' => 'required|max:255',
            'guid' => 'required|max:255',
            'site_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->pump->update($id, $Data);
            $returnData = array('status' => 'success', 'message' => 'Pump updated successfully!', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $pump = $this->pump->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Pump deleted successfully!', 'data' => $pump, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function generateGUID()
    {
        try {
            if (function_exists('com_create_guid')) {
                $guid = trim(com_create_guid(), '{}');
            } else {
                mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
                $charid = strtoupper(md5(uniqid(rand(), true)));
                $hyphen = chr(45);// "-"
                $guid = chr(123)// "{"
                    . substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12)
                    . chr(125);// "}"
                $guid = trim($guid, '{}');
            }
            $returnData = array('status' => 'success', 'message' => 'Pump guid generated successfully!', 'guid' => $guid, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Pump guid not generated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

}
