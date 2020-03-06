<?php

use App\Models\Vehicles;
use Illuminate\Database\Seeder;

class VehiclesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicle = array(
            'name' => 'Lancter Macwan',
            'make' => 'Ford',
            'model' => 'Truck',
            'registration_number' => 'FGG 231 FS',
            'customer_id' => 1,
            'site_id' => 1
        );

        $vehicleCreated = Vehicles::firstOrCreate($vehicle);
    }
}
