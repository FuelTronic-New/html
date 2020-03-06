<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('hose_id');
            $table->integer('attendant_id');
            $table->integer('vehicle_id');
            $table->integer('litres');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('customer_transactions');
    }
}
