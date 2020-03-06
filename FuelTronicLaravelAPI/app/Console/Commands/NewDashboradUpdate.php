<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NewDashboradUpdate extends Command
{
	/**
	 * The name and signature of the console command.
	 * @var string
	 */
	protected $signature = 'run:NewDashboradUpdate';

	/**
	 * The console command description.
	 * @var string
	 */
	protected $description = 'run procedure NewDashboradUpdate at every 5 minutes';

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
		//Log::info('------------------  call NewDashboradUpdate procedure  --------------------');
		try {
			\DB::select('call NewDashboradUpdate()');
		}catch (\Exception $e){
	//		Log::info('error => '.json_encode($e->getMessage()));
		}
	}
}
