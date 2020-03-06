<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //User::truncate();

            $users = array(
                ['name' => 'Henzard Kruger', 'email' => 'henzard@picahoo.co.za', 'password' => Hash::make('Alicia07')],
                ['name' => 'Chris Sevilleja', 'email' => 'chris@scotch.io', 'password' => Hash::make('secret')],
                ['name' => 'Holly Lloyd', 'email' => 'holly@scotch.io', 'password' => Hash::make('secret')],
                ['name' => 'Adnan Kukic', 'email' => 'adnan@scotch.io', 'password' => Hash::make('secret')],
            );

        // Uncomment the below to run the seeder
        $userCreated = User::insert($users);
    }
}
