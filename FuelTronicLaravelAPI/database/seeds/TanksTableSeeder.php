<?php

use App\Models\Tanks;
use Illuminate\Database\Seeder;

class TanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tanks = array(
            'name' => '500 KL',
            'optional1' => "On Atg",
            'grade_id' => 1,
            'site_id' => 1
        );

        $tankCreated = Tanks::firstOrCreate($tanks);
    }
}
