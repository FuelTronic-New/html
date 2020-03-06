<?php

namespace App\Http\Controllers;

use App\Interfaces\SiteusersRepositoryInterface;
use App\Models\Sites;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;
use App\User;

class SiteusersController extends JwtAuthController
{
    public function __construct(Request $request, SiteusersRepositoryInterface $siteusersRepo)
    {
        parent::__construct();
        $this->siteusers = $siteusersRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->siteusers->getAll($this->user->id);
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Users not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'role' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $Data['password'] = Hash::make($Data['password']);
            $this->siteusers->store($Data);
            $returnData = array('status' => 'success', 'message' => 'User saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'User not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $user = $this->siteusers->show($id);
            $returnData = array('status' => 'success', 'data' => $user, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'User not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required',
            'role' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            if($Data['password'] != '') {
                $Data['password'] = Hash::make($Data['password']);
            }
            else {
                unset($Data['password']);
            }
            $user = $this->siteusers->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'User updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'User not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->siteusers->delete($id);
            $returnData = array('status' => 'success', 'message' => 'User deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'User not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

	public function create()
	{

	}


	public function edit($id)
	{

	}

    public function getSitesOfUser($userID)
    {
        try {
            $sites = Sites::all();
            $user = User::find($userID);
	        if(!$user){
	            return response()->json(['status' => 'failure', 'message' => 'User not found', 'code' => 400],400);
	         }
            $userSites = $user->sites()->lists('id');
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'userSites' => $userSites), 'message' => 'Seleceted sites of user listed successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'User sites not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function updateSitesOfUser()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'user_id' => 'required',
            'site_ids' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $updateData = explode(',', $Data['site_ids']);
            $sites = Sites::all();
            $user = User::find($Data['user_id']);
	        if(!$user){
	            return response()->json(['status' => 'failure', 'message' => 'User not found', 'code' => 400],400);
	         }
            $userSites = $user->sites()->sync($updateData);
            $returnData = array('status' => 'success', 'data' => array('sites' => $sites, 'userSites' => $userSites), 'message' => 'User sites updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'User sites not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

}
