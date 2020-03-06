<?php

namespace App\Http\Controllers;

use App\Interfaces\GradeRepositoryInterface;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class GradesController extends JwtAuthController
{
    public function __construct(Request $request, GradeRepositoryInterface $gradeRepo)
    {
        parent::__construct();
        $this->grade = $gradeRepo;
        $this->request = $request;
    }

    public function index()
    {
        try {
            $Data = $this->grade->getAll();
            $returnData = array('status' => 'success', 'data' => $Data, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Grades not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function store()
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'price' => 'required|numeric',
            'site_id' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $Data['rate_increased_at'] = \Carbon\Carbon::parse($this->request->rate_increased_at)->format('Y-m-d H:i:s');
            $this->grade->store($Data);
            $returnData = array('status' => 'success', 'message' => 'Grade saved successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Grade not stored', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function show($id)
    {
        try {
            $grade = $this->grade->show($id);
            $returnData = array('status' => 'success', 'data' => $grade, 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Grade not found', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function update($id)
    {
        $Data = $this->request->all();
        $validator = \Validator::make($Data, [
            'name' => 'required|max:255',
            'price' => 'required|numeric',
            'site_id' => 'required'
        ]);
        if ($validator->fails()) {
            $returnData = array('status' => 'failure', 'validation_errors' => $validator->errors(), 'code' => 400);
            return response()->json($returnData, 400);
        }
        try {
            $Data['rate_increased_at'] = \Carbon\Carbon::parse($this->request->rate_increased_at)->format('Y-m-d H:i:s');
            $this->grade->update($id, $Data);
            $returnData = array('status' => 'success', 'data' => $Data, 'message' => 'Grade updated successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Grade not updated', 'code' => 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

    public function delete($id)
    {
        try {
            $this->grade->delete($id);
            $returnData = array('status' => 'success', 'message' => 'Grade deleted successfully!', 'code' => 200);
        } catch (\Exception $e) {
            $returnData = array('status' => 'failure', 'message' => 'Grade not found', 'code' => 400);
            return response()->json($returnData, 400);
        }
        return response()->json($returnData, $returnData['code']);
    }

}
