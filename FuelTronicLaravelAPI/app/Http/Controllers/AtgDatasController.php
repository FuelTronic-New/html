<?php

namespace App\Http\Controllers;

use App\Interfaces\SiteRepositoryInterface;
use App\Models\AtgData;
use App\Models\Tanks;
use App\Repositories\AtgDataRepository;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;
use App\User;

class AtgDatasController extends JwtAuthController
{
    public function __construct(Request $request, AtgDataRepository $atgDataRepo)
    {
        parent::__construct();
        $this->atg_data = $atgDataRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
	        $Data['sites'] = auth()->user()->sites()->select('id', 'name')->get();
            $Data['atg'] = AtgData::where('site_id', $this->request->site_id)->get();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'ip_address' => 'required|max:255',
            'port_num' => 'required|numeric',
            'tank_type' => 'required',
            'guid' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->atg_data->store($Data);
            $returnData = array('status' => 'success', 'message' => 'ATG Data saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $site = $this->atg_data->show($id);
            $returnData = array('status' => 'success', 'data' => $site, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'ip_address' => 'required|max:255',
            'port_num' => 'required|numeric',
            'tank_type' => 'required',
            'guid' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $site = $this->atg_data->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'ATG Data updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->atg_data->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Site deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'ATG not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

	public function create()
	{
        $users = Tanks::select('id', 'name')->get();
		$returnData = array('status' => 'success', 'data' => $users, 'code' => 200);
		return response()->json($returnData, $returnData['code']);
	}


	public function edit($id)
	{
        $users = Tanks::select('id', 'name')->get();
		$returnData = array('status' => 'success', 'data' => $users, 'code' => 200);
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
            $returnData = array('status' => 'failure', 'message' => 'guid not generated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }
}
