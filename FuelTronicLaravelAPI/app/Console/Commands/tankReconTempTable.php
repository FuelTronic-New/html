<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class tankReconTempTable extends Command
{
	/**
	 * The name and signature of the console command.
	 * @var string
	 */
	protected $signature = 'run:tankrecontemptable';

	/**
	 * The console command description.
	 * @var string
	 */
	protected $description = 'run procedure tankReconTempTable at every 10 minutes';

	/**
	 * Create a new command instance.
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 * @return mixed
	 */
	public function handle()
	{
		Log::info('------------------  call TankReconTempTable procedure  --------------------');
		try {
			\DB::select('call TankReconTempTable()');
		}catch (\Exception $e){
			Log::info('error => '.json_encode($e->getMessage()));
		}
	}
}
