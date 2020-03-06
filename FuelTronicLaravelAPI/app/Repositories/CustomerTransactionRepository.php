<?php
namespace App\Repositories;

use App\Interfaces\CustomerTransactionRepositoryInterface;
use App\Models\CustomerTransaction;

class CustomerTransactionRepository implements CustomerTransactionRepositoryInterface
{
	protected $model;

	public function __construct(CustomerTransaction $customerTransaction)
	{
		$this->model = $customerTransaction;
	}

	public function getAll()
	{
		$sites = auth()->user()->sites()->get();//->each(function ($item) {
		/* commented due to reduce the serverside processing load */
//		foreach ($sites as $site) {
//			foreach ($site->customer_transaction as $customer_transaction) {
//				$customer_transaction->pump_name = $customer_transaction->hose()->first()->pump->name;
//				$customer_transaction->grade_name = $customer_transaction->hose()->first()->tank->grades->name;
//				$customer_transaction->attendant_name = $customer_transaction->attendant['name'];
//			}
//		} //
		return $sites;
	}


	public function getTransactionsById($id)
	{
		//$start="2018-12-5 11:20:11";
	// $end="2019-03-5 11:20:11";
			      $start = date('Y-m-1 00:00:00');
      //$start="2019-02-01 00:00:00";
		$site = auth()->user()->sites()->with(['customer_transaction' => function($q) use ($start){
			$q->where('litres','>',0)->where('start_date','>=',$start);// Added By HS not to show 0 litres data
		}])->where('site_id','=',$id)->first();
		foreach ($site->customer_transaction as $customer_transaction) {
			$customer_transaction->pump_name = $customer_transaction->hose()->first()->pump->name;
			//$customer_transaction->order_number = $customer_transaction->hose()->first()->pump->order_number;
			$customer_transaction->driver = $customer_transaction->hose()->first()->pump->driver_fingerprint;
			$customer_transaction->grade_name = $customer_transaction->hose()->first()->tank->grades->name;
			$customer_transaction->attendant_name = $customer_transaction->attendant['name'];
			$customer_transaction->vehicle_name = $customer_transaction->vehicle['name'];
		}
		return $site;
	}
	public function getTransactionsByIdWithDate($id,$start_date,$end_date)
	{
	//$start="2018-12-5 11:20:11";
		 //$end="2019-03-5 11:20:11";
		     // $start = date('Y-m-1 00:00:00');
      // $start="2019-02-01 12:00:00";
		$site = auth()->user()->sites()->with(['customer_transaction' => function($q) use ($start_date,$end_date){
			$q->where('litres','>',0)
//			->whereBetween('start_date',[$start_date,$end_date]);

			->where('start_date','>=',$start_date)->where('start_date','<=',$end_date);// Added By HS not to show 0 litres data
		}])->where('site_id','=',$id)->first();
		foreach ($site->customer_transaction as $customer_transaction) {
			$customer_transaction->pump_name = $customer_transaction->hose()->first()->pump->name;
			//$customer_transaction->order_number = $customer_transaction->hose()->first()->pump->order_number;
			$customer_transaction->driver = $customer_transaction->hose()->first()->pump->driver_fingerprint;
			$customer_transaction->grade_name = $customer_transaction->hose()->first()->tank->grades->name;
			$customer_transaction->attendant_name = $customer_transaction->attendant['name'];
			$customer_transaction->vehicle_name = $customer_transaction->vehicle['name'];
		}
		return $site;
	}

	public function store($Data)
	{
		return $this->model->create($Data);
	}

	public function show($id)
	{
		return $this->model->with(['pump','attendant','hose.tank.grades'])->findOrFail($id);
	}

	public function update($id, $Data)
	{
		return $this->model->findOrFail($id)->update($Data);
	}

	public function delete($id)
	{
		return $this->model->destroy($id);
	}

	public function create($siteId)
	{
		return $this->tags->has('jobs', '=', 0)->where([
			'site_id' => $siteId,
			'usage'   => 'Jobs'
		])->get();
	}

	public function edit($siteId)
	{
		return $this->tags->has('jobs', '=', 0)->where([
			'site_id' => $siteId,
			'usage'   => 'Jobs'
		])->get();
	}

}

