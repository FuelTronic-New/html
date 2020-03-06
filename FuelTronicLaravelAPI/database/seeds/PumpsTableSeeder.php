<?php

use App\Models\Pumps;
use Illuminate\Database\Seeder;

class PumpsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pumps = array(
            'name' => 'Big Red',
            'ip' => '41.12.11.45',
            'code' => 'SN-234902',
            'optional1' =>'1',
            'optional2' => '1',
            'optional3' => '1',
            'site_id' => 1
        );

        $pumpCreated = Pumps::firstOrCreate($pumps);
    }
}
