<?php

use App\Models\Hoses;
use Illuminate\Database\Seeder;

class HosesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hoses = array(
            'name' => 'Green Hose Pump 1',
            'optional1' => 'Extra Green',
            'pump_id' => 1,
            'tank_id' => 1,
            'site_id' => 1
        );

        $hoseCreated = Hoses::firstOrCreate($hoses);
    }
}
