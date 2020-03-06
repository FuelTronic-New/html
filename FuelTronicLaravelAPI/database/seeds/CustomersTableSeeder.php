<?php

use App\Models\Customers;
use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customer = array(
            'accountNumber' => '8512121245023',
            'usage' => 'Not Known',
            'status' => 'Active',
            'name' => 'Lancter Macwan',
            'first_name' => 'Lancter',
            'last_name' => 'Macwan',
            'email_address' => 'lanster@gmail.com',
            'phone' => '123456789',
            'fax' => '123456789',
            'mobile' => '123456789',
            'site_id' => 1,
        );

        $customerCreated = Customers::firstOrCreate($customer);
    }
}
