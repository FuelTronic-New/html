<?php

namespace App\Http\Controllers;

use App\Interfaces\SiteRepositoryInterface;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;
use App\User;

class SitesController extends JwtAuthController
{
    public function __construct(Request $request, SiteRepositoryInterface $siteRepo)
    {
        parent::__construct();
        $this->site = $siteRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->site->getAll();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Sites not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $this->site->store($Data);
            $returnData = array('status' => 'success', 'message' => 'Site saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Site not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $site = $this->site->show($id);
            $returnData = array('status' => 'success', 'data' => $site, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Site not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $site = $this->site->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Site updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Site not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->site->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Site deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Site not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

	public function create()
	{
		$users = User::select('id','name')->get();
		$returnData = array('status' => 'success', 'data' => $users, 'code' => 200);
		return response()->json($returnData, $returnData['code']);
	}


	public function edit($id)
	{
		$users = User::select('id','name')->get();
		$returnData = array('status' => 'success', 'data' => $users, 'code' => 200);
		return response()->json($returnData, $returnData['code']);
	}

}
