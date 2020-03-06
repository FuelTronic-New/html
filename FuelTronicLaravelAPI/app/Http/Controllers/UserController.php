<?php namespace App\Http\Controllers;

use App\Models\Firstdataglobalapi;
use App\Models\Transactions;
use JWTAuth;
use App\Http\Controllers\JwtAuthController;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Hash;
use App\Models\Xeroapi;
use Xero;
use App\Xero\lib\XeroOAuth;

class UserController extends JwtAuthController
{
	public function __construct(Request $request)
	{
		parent::__construct();
		$this->request = $request;
	}

	public function getUser()
	{
		return response()->json($this->user);
	}

	public function getUserTransactions()
	{
		$userTransactions = Transactions::where('UserID', $this->user->ID)->with('user')->get();
		return response()->json($userTransactions);
	}

	public function updateUser()
	{
		$request = $this->request->all();
		$validator = \Validator::make($request, [
			'name' => 'required',
			//            'email' => 'required|unique:users,email,'.$this->user->id
		]);
		if ($validator->fails()) {
			return response()->json(['validation_errors' => $validator->errors()], 400);
		}
		if (isset($request['name'])) {
			$userObj['name'] = $request['name'];
		}
		User::where('id', $this->user->id)->update($userObj);
		$returnData = array ('status'  => 'success',
		                     'message' => 'Profile Updated Successfully.',
		                     'code'    => 200
		);
		return response()->json($returnData, $returnData['code']);
	}

	public function changePassword()
	{
		$request = $this->request->all();
		$validator = \Validator::make($request, [
			'old_password' => 'required',
			'password'     => 'required|confirmed|min:6'
		]);
		if ($validator->fails()) {
			return response()->json(['validation_errors' => $validator->errors()], 400);
		}
		if (Hash::check($this->request->get('old_password'), $this->user->password)) {
			User::where('id', $this->user->id)->update([
					'password' => Hash::make($this->request->get('password'))
				]);
			$returnData = array ('status'  => 'success',
			                     'message' => 'Password Updated Successfully.',
			                     'code'    => 200
			);
		}
		else {
			$returnData = array ('status'  => 'error',
			                     'message' => 'Enter valid old password.',
			                     'code'    => 400
			);
		}
		return response()->json($returnData, $returnData['code']);
	}

}