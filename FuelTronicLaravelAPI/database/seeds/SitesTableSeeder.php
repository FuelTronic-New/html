<?php

use App\Models\Sites;
use Illuminate\Database\Seeder;

class SitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sites = array(
            'name' => 'Site 1',
            'owner_id' => '1'
        );

        $siteCreated = Sites::firstOrCreate($sites);
    }
}
