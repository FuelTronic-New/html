<?php

use App\Models\Attendants;
use Illuminate\Database\Seeder;

class AttendantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendant = array(
            'name' => 'Pieter',
            'surname' => 'du Toit',
            'cell' => '0864651324',
            'said' => '8512121245023',
            'site_id' => 1
        );

        $attendantCreated = Attendants::firstOrCreate($attendant);
    }
}
