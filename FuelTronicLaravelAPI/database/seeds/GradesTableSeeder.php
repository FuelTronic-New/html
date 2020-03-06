<?php

use App\Models\Grades;
use Illuminate\Database\Seeder;

class GradesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $grades = array(
            'name' => 'Diesel 50PPM',
            'price' => 11.30,
            'optional1' => '1',
            'site_id' => 1
        );

        $gradeCreated = Grades::firstOrCreate($grades);
    }
}
