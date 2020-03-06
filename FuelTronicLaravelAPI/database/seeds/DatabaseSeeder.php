<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(UsersTableSeeder::class);
        $this->call(SitesTableSeeder::class);
        $this->call(GradesTableSeeder::class);
        $this->call(TanksTableSeeder::class);
        $this->call(PumpsTableSeeder::class);
        $this->call(HosesTableSeeder::class);
        $this->call(AttendantsTableSeeder::class);
        $this->call(CustomersTableSeeder::class);
        $this->call(VehiclesTableSeeder::class);

        Model::reguard();
    }
}
